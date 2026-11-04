<?php

namespace App\Models;

use App\Enums\OpnameSessionStatus;
use App\Enums\OpnameSessionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOpnameSession extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $appends = ['status'];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'opname_date' => 'date:Y-m-d',
            'start_at' => 'datetime',
            'review_at' => 'datetime',
            'end_at' => 'datetime',
            'ins_date' => 'datetime',
            'upd_date' => 'datetime',
        ];
    }

    public function getStatusAttribute(): string
    {
        return $this->currentStatus()->value;
    }

    public function currentStatus(): OpnameSessionStatus
    {
        return match (true) {
            $this->end_at !== null => OpnameSessionStatus::Finished,
            $this->review_at !== null => OpnameSessionStatus::Review,
            $this->start_at !== null => OpnameSessionStatus::Started,
            default => OpnameSessionStatus::Scheduled,
        };
    }

    public function sessionType(): OpnameSessionType
    {
        return OpnameSessionType::from($this->type);
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return match (OpnameSessionStatus::tryFrom($status)) {
            OpnameSessionStatus::Scheduled => $query->whereNull('start_at'),
            OpnameSessionStatus::Started => $query->whereNotNull('start_at')->whereNull('review_at')->whereNull('end_at'),
            OpnameSessionStatus::Review => $query->whereNotNull('review_at')->whereNull('end_at'),
            OpnameSessionStatus::Finished => $query->whereNotNull('end_at'),
            default => $query,
        };
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pic_employee_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_session_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_session_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'product_opname_session_employees', 'session_id', 'employee_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(ProductOpnameSessionScan::class, 'session_id');
    }

    public function opnameDocument(): BelongsTo
    {
        return $this->belongsTo(ProductOpnameService::class, 'product_opname_service_id');
    }

    public function adjustmentDocument(): BelongsTo
    {
        return $this->belongsTo(ProductAdjustmentStock::class, 'product_adjustment_stock_id');
    }
}
