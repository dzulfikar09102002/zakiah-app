<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTransferService extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'from_location_id',
        'to_location_id',
        'product_ids',
        'product_category_ids',
        'request_note',
    ];

    public function productTransferServiceDetails(): HasMany {
        return $this->hasMany(ProductTransferServiceDetail::class);
    }

    public function fromLocation(): BelongsTo {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function entity(): BelongsTo {
        return $this->belongsTo(Entity::class);
    }

    public function employeeRequestedBy(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_requested_by');
    }

    public function employeeApprovedBy(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_approved_by');
    }

    public function employeeRejectedBy(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_rejected_by');
    }

    public function employeeCancelledBy(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_cancelled_by');
    }
}
