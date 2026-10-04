<?php

namespace App\Actions;

use App\Models\MaintenanceRequest;

class UpdateRequestStatusAction
{
    public function execute(MaintenanceRequest $request, string $status): MaintenanceRequest
    {
        $request->update([
            'status' => $status,
            'completed_at' => $status === 'done' ? now() : null,
        ]);

        return $request;
    }
}