<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyCountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => $this['date'],
            'quantidade' => $this['count'],
        ];
    }
}
