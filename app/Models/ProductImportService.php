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
        'entity_id',
        'employee_id',
        'code',
        'employee_requested_by',
        'requested_at',
        'local_requested_at',
        'employee_approved_by',
        'approved_at',
        'local_approved_at',
        'file_url',
        'imported_product_count',
        'imported_product_quantity',
        'product_created_count',
        'product_unit_created_count',
        'product_category_created_count',
        'order_type_created_count',
        'status',
        'created_by',
        'updated_by',
    ];

    public function productImportServiceDetails(): HasMany {
        return $this->hasMany(ProductImportServiceDetail::class);
    }

    public function employeeApproved(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_approved_by');
    }
}