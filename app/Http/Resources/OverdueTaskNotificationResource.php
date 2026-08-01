<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OverdueTaskNotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'seen' => (int) $this->seen,
            'seen_at' => $this->seen_at,
            'task' => new TaskResource($this->whenLoaded('task')),
            'created_at' => $this->created_at,
        ];
    }
}
