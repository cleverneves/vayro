<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const REASON_TRIP = 'trip';
    public const REASON_LEISURE = 'leisure';
    public const REASON_EVERYDAY = 'everyday';

    public const REASONS = [
        self::REASON_TRIP,
        self::REASON_LEISURE,
        self::REASON_EVERYDAY,
    ];

    public const RESERVING_STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS,
    ];

    protected $fillable = [
        'renter_id',
        'vehicle_id',
        'starts_on',
        'day_count',
        'reason',
        'comment',
        'status',
        'admin_note',
        'requested_on',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'requested_on' => 'date',
        'day_count' => 'integer',
    ];

    public function renter(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'renter_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Carro::class, 'vehicle_id');
    }

    public function exclusiveEndDate(): CarbonInterface
    {
        return $this->starts_on->copy()->addDays($this->day_count);
    }

    public function lastReservedDate(): CarbonInterface
    {
        return $this->starts_on->copy()->addDays($this->day_count - 1);
    }

    public function isReserving(): bool
    {
        return in_array($this->status, self::RESERVING_STATUSES, true);
    }

    public function overlapsPeriod(CarbonInterface $startsOn, int $dayCount): bool
    {
        $otherEnd = $startsOn->copy()->addDays($dayCount);

        return $this->starts_on->lt($otherEnd) && $startsOn->lt($this->exclusiveEndDate());
    }

    public function scopeReserving($query)
    {
        return $query->whereIn('status', self::RESERVING_STATUSES);
    }

    public function scopeOverlappingPeriod($query, string $startsOn, int $dayCount)
    {
        $exclusiveEnd = \Carbon\Carbon::parse($startsOn)->addDays($dayCount)->toDateString();

        return $query
            ->where('starts_on', '<', $exclusiveEnd)
            ->whereRaw('? < (starts_on + day_count)', [$startsOn]);
    }
}
