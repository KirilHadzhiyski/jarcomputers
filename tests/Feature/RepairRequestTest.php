<?php

namespace Tests\Feature;

use App\Jobs\DispatchRepairRequestNotifications;
use App\Models\Conversation;
use App\Models\RepairRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RepairRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_request_can_be_submitted_via_json(): void
    {
        Bus::fake();

        $response = $this->postJson(route('repair-requests.store'), [
            'name' => 'Иван Иванов',
            'phone' => '0888123456',
            'email' => 'ivan@example.com',
            'city' => 'София',
            'model' => 'iPhone 13',
            'issue' => 'Счупен дисплей',
            'preferred_contact' => 'email',
            'source_page' => '/kontakti',
            'gdpr_consent' => true,
        ]);

        $response->assertCreated()
            ->assertJsonFragment([
                'message' => 'Благодарим за заявката. Ще се свържем с вас в рамките на 1 час в работно време.',
            ]);

        $repairRequest = RepairRequest::query()->first();

        $this->assertNotNull($repairRequest);
        $this->assertDatabaseHas(RepairRequest::class, [
            'id' => $repairRequest->id,
            'name' => 'Иван Иванов',
            'phone' => '0888123456',
            'email' => 'ivan@example.com',
            'city' => 'София',
            'model' => 'iPhone 13',
            'preferred_contact' => 'email',
            'source_page' => '/kontakti',
            'source_channel' => 'website-form',
            'gdpr_consent' => true,
        ]);

        $this->assertDatabaseHas(Conversation::class, [
            'repair_request_id' => $repairRequest->id,
            'channel' => 'website-form',
            'customer_phone' => '0888123456',
            'customer_email' => 'ivan@example.com',
        ]);

        Bus::assertDispatched(DispatchRepairRequestNotifications::class);
    }

    public function test_repair_request_requires_name_phone_issue_and_gdpr_consent(): void
    {
        $response = $this->postJson(route('repair-requests.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'issue', 'gdpr_consent']);
    }
}
