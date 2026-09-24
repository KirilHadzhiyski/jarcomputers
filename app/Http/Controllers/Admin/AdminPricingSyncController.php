<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationConnection;
use App\Services\Integrations\IntegrationException;
use App\Services\Integrations\IntegrationManager;

class AdminPricingSyncController extends Controller
{
    public function store(IntegrationManager $manager)
    {
        try {
            $manager->enqueue(IntegrationConnection::where('provider', 'price_feed')->firstOrFail());
        } catch (IntegrationException $exception) {
            return redirect()->route('admin.integrations.index')->withErrors(['integration' => $exception->getMessage()]);
        }

        return redirect()->route('admin.integrations.index')->with('status', 'Получаването на конкурентни цени е добавено в опашката.');
    }
}
