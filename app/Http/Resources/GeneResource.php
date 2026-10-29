<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'alliance_id' => $this->alliance_id,
            'symbol' => $this->symbol,
            'name' => $this->name,
            'species' => $this->species,
        ];
    }
}
