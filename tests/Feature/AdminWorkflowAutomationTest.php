<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_workflow_and_order_visual_steps(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->post('/admin/workflows', [
            'client_user_id' => $client->id,
            'name' => 'Сравнение на конкурентни цени',
            'description' => 'Събиране и съпоставяне по SKU.',
            'status' => 'active',
            'trigger_type' => 'schedule',
            'frequency_minutes' => 60,
            'source_type' => 'rest_api',
            'source_url' => 'https://prices.example.com/feed',
            'category_filters' => "Лаптопи\nКомпоненти",
            'approval_mode' => 'review',
            'token' => 'must-never-enter-the-log',
        ]);

        $response->assertSessionHasNoErrors();
        $workflow = Workflow::query()->firstOrFail();

        $response->assertRedirect(route('admin.workflows.edit', $workflow));
        $this->assertSame(['Лаптопи', 'Компоненти'], $workflow->category_filters);
        $this->assertSame($admin->id, $workflow->created_by);

        $this->actingAs($admin)->post(route('admin.workflows.steps.store', $workflow), [
            'name' => 'Получаване на оферти',
            'type' => 'collect',
            'configuration' => '{"method":"GET"}',
            'is_enabled' => '1',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.workflows.steps.store', $workflow), [
            'name' => 'Съпоставяне по SKU',
            'type' => 'match',
            'configuration' => '{"key":"sku"}',
            'is_enabled' => '1',
        ])->assertRedirect();

        $secondStep = WorkflowStep::query()->where('type', 'match')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.workflows.steps.move', [$workflow, $secondStep]), [
            'direction' => 'up',
        ])->assertRedirect();

        $this->assertSame('match', $workflow->steps()->first()->type);
        $this->assertDatabaseCount('workflow_runs', 0);

        $audit = AuditLog::query()->where('route_name', 'admin.workflows.store')->firstOrFail();
        $this->assertContains('name', $audit->metadata['changed_fields']);
        $this->assertNotContains('token', $audit->metadata['changed_fields']);
        $this->assertStringNotContainsString('must-never-enter-the-log', json_encode($audit->metadata));
    }

    public function test_workflow_pages_and_audit_log_are_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->get('/admin/workflows')->assertOk()->assertSee('Работни процеси');
        $this->actingAs($admin)->get('/admin/workflow-runs')->assertOk()->assertSee('Изпълнения на процесите');
        $this->actingAs($admin)->get('/admin/audit-log')->assertOk()->assertSee('Журнал на действията');

        $this->actingAs($user)->get('/admin/workflows')->assertForbidden();
        $this->actingAs($user)->get('/admin/audit-log')->assertForbidden();
    }

    public function test_workflow_requires_https_source_and_valid_step_configuration(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/workflows', [
            'name' => 'Unsafe source',
            'status' => 'draft',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'source_url' => 'http://example.com/feed',
            'approval_mode' => 'review',
        ])->assertSessionHasErrors('source_url');

        $workflow = Workflow::query()->create([
            'name' => 'Safe workflow',
            'slug' => 'safe-workflow',
            'status' => 'draft',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'approval_mode' => 'review',
        ]);

        $this->actingAs($admin)->post(route('admin.workflows.steps.store', $workflow), [
            'name' => 'Broken JSON',
            'type' => 'collect',
            'configuration' => '{broken}',
            'is_enabled' => '1',
        ])->assertSessionHasErrors('configuration');

        $this->assertDatabaseCount('workflow_steps', 0);
    }
}
