<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\ProjectResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AdminProjectResource extends ProjectResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'owner' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
