<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;

class AdminTaskResource extends TaskResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'project' => new AdminProjectResource($this->whenLoaded('project')),
        ];
    }
}
