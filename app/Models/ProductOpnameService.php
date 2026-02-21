<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOpnameService extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'auto_approve',
        'note',
        'recorded_product_count',
        'counted_product_count',
        'difference_product_count',
        'recorded_stock',
        'counted_stock',
        'difference_stock',
    ];

    public function productOpnameServiceDetails(): HasMany {
        return $this->hasMany(ProductOpnameServiceDetail::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
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
}
