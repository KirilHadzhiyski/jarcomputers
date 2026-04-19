<?php

namespace App\Jobs;

use App\Models\RepairRequest;
use App\Services\Communications\LeadNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchRepairRequestNotifications implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $repairRequestId,
    ) {
    }

    public function handle(LeadNotificationService $leadNotificationService): void
    {
        $repairRequest = RepairRequest::query()->find($this->repairRequestId);

        if (! $repairRequest) {
            return;
        }

        $leadNotificationService->notifyNewRepairRequest($repairRequest);
    }
}
