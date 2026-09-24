<?php

namespace App\Services\Business;

use App\Models\BusinessOrder;
use App\Models\Conversation;
use App\Models\CustomerProfile;
use App\Models\RepairRequest;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class BusinessIntakeService
{
    public function repairRequest(RepairRequest $request): BusinessOrder
    {
        return DB::transaction(function () use ($request) {
            $customer = $this->customer($request->name, $request->email, $request->phone, $request->preferred_contact ?: 'phone');

            return BusinessOrder::firstOrCreate(['intake_reference' => 'repair:'.$request->id], [
                'repair_request_id' => $request->id, 'customer_profile_id' => $customer?->id,
                'order_number' => 'WEB-'.strtoupper($request->id), 'customer_name' => $request->name,
                'customer_email' => $request->email, 'customer_phone' => $request->phone,
                'device_or_product' => $request->model ?: 'Устройство за диагностика', 'service_type' => 'repair',
                'status' => 'received', 'priority' => 'normal', 'notes' => $request->issue,
                'contact_next_step' => 'Потвърдете заявката и диагностиката с клиента.',
            ]);
        });
    }

    public function ticket(Ticket $ticket): BusinessOrder
    {
        return DB::transaction(function () use ($ticket) {
            $user = $ticket->user;
            $customer = $this->customer($user->name, $user->email, $user->phone, $user->preferred_contact_channel ?: 'email');
            $order = BusinessOrder::where('ticket_id', $ticket->id)->first()
                ?? new BusinessOrder(['intake_reference' => 'ticket:'.$ticket->id, 'ticket_id' => $ticket->id, 'order_number' => 'TKT-'.$ticket->id]);
            $status = match ($ticket->status) {
                'in_progress' => 'in_progress', 'ready_for_pickup', 'resolved' => 'ready',
                'closed', 'waiting_customer' => $order->status ?: 'received', default => 'received',
            };
            $order->fill([
                'user_id' => $user->id, 'customer_profile_id' => $customer?->id,
                'customer_name' => $user->name, 'customer_email' => $user->email, 'customer_phone' => $user->phone,
                'device_or_product' => $ticket->device_model ?: $ticket->subject,
                'service_type' => $ticket->category === 'warranty' ? 'warranty' : ($ticket->category === 'repair' ? 'repair' : 'consultation'),
                'priority' => $ticket->priority, 'notes' => $ticket->description,
            ]);
            if (! $order->exists || $ticket->wasChanged('status')) {
                $order->status = $status;
                if ($status === 'ready' && ! $order->ready_at) {
                    $order->ready_at = now();
                }
            }
            $order->save();

            return $order;
        });
    }

    public function conversation(Conversation $conversation): ?CustomerProfile
    {
        $channel = $conversation->channel === 'facebook-messenger' ? 'facebook' : $conversation->channel;

        return $this->customer($conversation->customer_name ?: 'Клиент', $conversation->customer_email, $conversation->customer_phone,
            in_array($channel, ['phone', 'email', 'viber', 'whatsapp', 'facebook'], true) ? $channel : 'phone');
    }

    private function customer(string $name, ?string $email, ?string $phone, string $channel): ?CustomerProfile
    {
        $email = filled($email) ? mb_strtolower(trim($email)) : null;
        $phone = filled($phone) ? $this->normalizePhone($phone) : null;
        if (! $email && ! $phone) {
            return null;
        }
        $customer = $email ? CustomerProfile::where('email', $email)->first() : null;
        if (! $customer && $phone) {
            $customer = CustomerProfile::where('phone', $phone)->when($email, fn ($query) => $query->whereNull('email'))->first();
        }
        // Incoming unverified contact details must not replace an established customer identity.
        $attributes = [
            'name' => mb_substr($name, 0, 160), 'email' => $email, 'phone' => $phone,
            'preferred_channel' => $channel, 'status' => 'lead',
        ];

        return $customer ?? ($email ? CustomerProfile::firstOrCreate(['email' => $email], $attributes) : CustomerProfile::create($attributes));
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '359'.substr($digits, 1);
        }

        return $digits;
    }
}
