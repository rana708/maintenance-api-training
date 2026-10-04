<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\UpdateRequestStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRequestStatusRequest;
use App\Http\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;

class RequestStatusController extends Controller
{
    public function update(UpdateRequestStatusRequest $request, MaintenanceRequest $maintenanceRequest, UpdateRequestStatusAction $updateStatus): MaintenanceRequestResource
    {
        return new MaintenanceRequestResource($updateStatus->execute($maintenanceRequest, $request->validated('status'))->fresh());
    }
}
