<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\TagResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AdminTagResource extends TagResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'owner' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
