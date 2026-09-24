<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessOrder;
use App\Models\Conversation;
use App\Models\RepairRequest;

class AdminInboxController extends Controller
{
    public function index()
    {
        return view('admin.inbox.index', [
            'requests' => RepairRequest::latest()->paginate(10, ['*'], 'requests_page'),
            'conversations' => Conversation::withCount('messages')->latest('last_message_at')->paginate(15, ['*'], 'conversations_page'),
            'seo' => ['title' => 'Входящи заявки и съобщения', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function show(Conversation $conversation)
    {
        return view('admin.inbox.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages()->orderBy('received_at')->paginate(30),
            'order' => $conversation->repair_request_id ? BusinessOrder::where('repair_request_id', $conversation->repair_request_id)->first() : null,
            'seo' => ['title' => 'История на разговора', 'robots' => 'noindex,nofollow'],
        ]);
    }
}
