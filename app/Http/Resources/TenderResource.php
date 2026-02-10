<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'deadline' => $this->deadline,
            'status' => $this->status,
            'budget' => (float) $this->budget,
            'isUnlocked' => $this->isUnlockedBy($request->user()),
            'boq_file_url' => $this->documents()->where('document_type', 'boq_pdf')->latest()->first()?->url,
            
            // Include BOQ items if loaded
            'boqItems' => BoqItemResource::collection($this->whenLoaded('boqItems')),
            
            // Include bids count if available
            'bids_count' => $this->when(isset($this->bids_count), $this->bids_count ?? 0),
            
            // Award information (only if awarded)
            'awarded_bid_id' => $this->when($this->status === 'awarded', $this->awarded_bid_id),
            'awarded_date' => $this->when($this->status === 'awarded', $this->awarded_date),
            
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
