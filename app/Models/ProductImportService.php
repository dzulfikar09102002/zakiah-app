<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductImportService extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_note',
        'note',
        'auto_approve',
    ];

    public function productImportServiceDetails(): HasMany {
        return $this->hasMany(ProductImportServiceDetail::class);
    }

    public function employeeApproved(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_approved_by');
    }
}
