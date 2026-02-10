<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'contractor_id',
        'status',
        'total_amount',
        'submitted_at',
        'offer_file_path',
        'offer_file_name',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function contractor()
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }

    public function bidItems()
    {
        return $this->hasMany(BidItem::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeAwarded($query)
    {
        return $query->where('status', 'accepted');
    }

    // Methods
    public function submit()
    {
        $this->calculateTotal();
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function calculateTotal()
    {
        $total = $this->bidItems()->sum(\DB::raw('unit_price * quantity'));
        $this->update(['total_amount' => $total]);
        return $total;
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isAwarded()
    {
        return $this->status === 'accepted';
    }
}
