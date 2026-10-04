<?php

namespace Tests\Unit;

use App\Actions\AssignTechnicianAction;
use App\Actions\UpdateRequestStatusAction;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_an_available_technician(): void
    {
        $request = MaintenanceRequest::factory()->create(['status' => 'new']);
        $technician = User::factory()->technician()->create();

        $assignedRequest = (new AssignTechnicianAction())->execute($request, $technician);

        $this->assertSame($technician->id, $assignedRequest->fresh()->technician_id);
        $this->assertSame('assigned', $assignedRequest->fresh()->status);
    }

    public function test_it_rejects_a_user_who_is_not_a_technician(): void
    {
        $request = MaintenanceRequest::factory()->create(['status' => 'new']);
        $user = User::factory()->dispatcher()->create();

        $this->expectException(ValidationException::class);

        (new AssignTechnicianAction())->execute($request, $user);
    }

    public function test_it_rejects_a_technician_with_an_existing_visit_at_the_same_time(): void
    {
        $technician = User::factory()->technician()->create();
        $firstRequest = MaintenanceRequest::factory()->create([
            'technician_id' => $technician->id,
            'status' => 'assigned',
            'scheduled_at' => '2026-10-01 07:00:00',
        ]);
        $requestToAssign = MaintenanceRequest::factory()->create([
            'status' => 'new',
            'scheduled_at' => $firstRequest->scheduled_at,
        ]);

        $this->expectException(ValidationException::class);

        (new AssignTechnicianAction())->execute($requestToAssign, $technician);
    }

    public function test_it_records_the_completion_time_when_marking_a_request_done(): void
    {
        $request = MaintenanceRequest::factory()->create(['status' => 'in_progress']);

        $updatedRequest = (new UpdateRequestStatusAction())->execute($request, 'done');

        $this->assertSame('done', $updatedRequest->fresh()->status);
        $this->assertNotNull($updatedRequest->fresh()->completed_at);
    }

    public function test_it_clears_the_completion_time_when_marking_a_request_cancelled(): void
    {
        $request = MaintenanceRequest::factory()->create([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        $updatedRequest = (new UpdateRequestStatusAction())->execute($request, 'cancelled');

        $this->assertSame('cancelled', $updatedRequest->fresh()->status);
        $this->assertNull($updatedRequest->fresh()->completed_at);
    }
}