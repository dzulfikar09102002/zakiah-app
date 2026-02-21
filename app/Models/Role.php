<?php

namespace App\Models;

use App\Helpers\Constants\PageNameConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** 
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entity_permission' => 'array',
            'location_permission' => 'array',
            'allow_pos' => 'boolean',
            'allow_backoffice' => 'boolean',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public static function defaultEntityPermission(): array
    {
        return array_merge(
            Role::defaultEntityAccess(PageNameConstants::EmployeeMenu),
            Role::defaultEntityAccess(PageNameConstants::CustomerOrderMenu),
            Role::defaultEntityAccess(PageNameConstants::CustomerMenu),
            Role::defaultEntityAccess(PageNameConstants::CustomerPointMenu),
            Role::defaultEntityAccess(PageNameConstants::CustomerCategoryMenu),
            Role::defaultEntityAccess(PageNameConstants::ProductMenu),
            Role::defaultEntityAccess(PageNameConstants::PromoMenu),
            Role::defaultEntityAccess(PageNameConstants::BrandMenu),
            Role::defaultEntityAccess(PageNameConstants::DailySalesMenu),
            Role::defaultEntityAccess(PageNameConstants::LocationMenu),
            Role::defaultEntityAccess(PageNameConstants::LoyaltyMenu),
            Role::defaultEntityAccess(PageNameConstants::OrderTypeMenu),
            Role::defaultEntityAccess(PageNameConstants::PaymentMethodMenu),
            Role::defaultEntityAccess(PageNameConstants::ProductCategoryMenu),
            Role::defaultEntityAccess(PageNameConstants::DashboardMenu),
            Role::defaultEntityAccess(PageNameConstants::ReportByProductMenu),
            Role::defaultEntityAccess(PageNameConstants::ReportSalesMenu),
            Role::defaultEntityAccess(PageNameConstants::ReportSalesByLocationMenu),
            Role::defaultEntityAccess(PageNameConstants::ReportStockMovementMenu),
            Role::defaultEntityAccess(PageNameConstants::ReportStockCardMenu),
            Role::defaultEntityAccess(PageNameConstants::ReportEmployeeSummary),
            Role::defaultEntityAccess(PageNameConstants::ProductImportMenu),
            Role::defaultEntityAccess(PageNameConstants::ProductOpnameMenu),
            Role::defaultEntityAccess(PageNameConstants::ProductAdjustmentStockMenu),
            Role::defaultEntityAccess(PageNameConstants::ProductTransferMenu),
            Role::defaultEntityAccess(PageNameConstants::ProductUnitMenu),
            Role::defaultEntityAccess(PageNameConstants::RoleMenu),
            Role::defaultEntityAccess(PageNameConstants::SaleTransactionMenu),
            Role::defaultEntityAccess(PageNameConstants::TaxMenu),
        );
    }

    private static function locationEntityAccess(): array
    {
        return array(
            'location' => [
                'index' => false,
                'show' => false,
                'store' => false,
                'update' => false,
                'destroy' => false,
                'archive' => false,
                'activate' => false,
            ]
        );
    }

    private static function brandEntityAccess(): array
    {
        return array(
            'brand' => [
                'index' => false,
                'show' => false,
                'store' => false,
                'update' => false,
                'destroy' => false,
                'archive' => false,
                'activate' => false,
            ]
        );
    }

    private static function employeeEntityAccess(): array
    {
        return array(
            'employee' => [
                'index' => false,
                'show' => false,
                'store' => false,
                'update' => false,
                'destroy' => false,
                'archive' => false,
                'activate' => false,
            ]
        );
    }

    private static function customerOrderEntityAccess(): array
    {
        return array(
            PageNameConstants::CustomerOrderMenu => [
                'index' => false,
                'show' => false,
                'store' => false,
                'update' => false,
                'destroy' => false,
                'archive' => false,
                'activate' => false,
            ]
        );
    }

    private static function defaultEntityAccess($menu): array
    {
        return array(
            $menu => [
                'index' => true,
                'show' => true,
                'store' => true,
                'update' => true,
                'destroy' => true,
                'archive' => true,
                'activate' => true,
                'approve' => true,
                'reject' => true,
                'cancel' => true,
                'void' => true,
                'apply_promo' => true,
            ]
        );
    }
}
