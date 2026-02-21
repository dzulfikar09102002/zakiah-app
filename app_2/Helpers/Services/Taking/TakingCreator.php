<?php

namespace App\Helpers\Services\Taking;

use App\Models\DailySale;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\Location;
use App\Models\SaleRefund;
use App\Models\SaleRefundDetail;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use App\Models\Taking;
use App\Models\TakingPaymentDetail;
use App\Models\User;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TakingCreator
{
    private Entity $entity;
    private Location $location;
    private Device $device;
    private User $user;
    private Employee $employee;
    private ?Taking $lastTaking;
    private array $params;
    private DateTime $timeNow, $localTimeNow;
    private DateTimeZone $timezone;

    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, Device $device, array $params, User $user, Employee $employee)
    {
        //
        $this->entity = $entity;
        $this->device = $device;
        $this->user = $user;
        $this->employee = $employee;

        $this->location = Location::findOrFail($params['locationId']);
        $this->timeNow = new DateTime();
        $this->timezone = new DateTimeZone($this->location->timezone);
        $this->localTimeNow = (new DateTime())->setTimezone($this->timezone);

        $this->lastTaking = $this->getLastTaking();
        $this->params = $params;
    }

    public function create(): Taking
    {
        $taking = new Taking();
        $taking->device_id = $this->device->id;
        $taking->checkpoint_device_id = $this->device->id;

        $taking->location_id = $this->location->id;
        $taking->entity_id = $this->entity->id;
        $taking->taking_at = $this->timeNow;
        $taking->local_taking_at = $this->localTimeNow;
        $taking->sale_reference_id = $this->params['saleReferenceId'];
        $taking->created_by = $this->user->id;
        $taking->updated_by = $this->user->id;

        $taking->employee_id = $this->employee->id;
        $taking->employee_first_name = $this->employee->first_name ?? '';
        $taking->employee_last_name = $this->employee->last_name ?? '';

        $taking->is_shift = $this->params['isShift'];
        if ($taking->is_shift) {
            $taking->shift_number = ($this->lastTaking?->shift_number ?? 0) + 1;
        }
        $taking->last_taking_at = $this->lastTaking?->taking_at;

        $taking->sales_count = count($this->params['saleTransactionIds']);
        $taking->refund_count = count($this->params['saleRefundIds']);

        $this->setPaymentSummaries($taking);
        $this->setSaleTransaction($taking);
        $this->setSaleRefund($taking);
        $this->setMoneyMovement($taking);
        $this->setCustomerDeposit($taking);

        $taking->save();

        $this->updateShiftTaking($taking);
        $this->updateDailySales($taking);
        $this->updateSaleTransaction($taking);
        $this->updateSaleRefund($taking);
        $this->saveTakingPaymentDetails($taking);

        return $taking;
    }

    private function getLastTaking(): ?Taking
    {
        return Taking::where('entity_id', $this->entity->id)
            ->where('location_id', $this->location->id)
            ->where('taking_at', '<', $this->timeNow)
            ->orderByDesc('id')
            ->first();
    }

    private function updateShiftTaking(Taking $taking)
    {
        if ($taking->is_shift) { return; }

        Taking::where('entity_id', $this->entity->id)
            ->where('location_id', $this->location->id)
            ->where('taking_at', '<', $this->timeNow)
            ->where('id', '<', $taking->id)
            ->where('parent_id', null)
            ->where('is_shift', true)
            ->update([
                'parent_id' => $taking->id,
            ]);
    }

    private function updateDailySales(Taking $taking)
    {
        $deviceId = $this->device->id;

        $dailySale = DailySale::where('entity_id', $this->entity->id)
            ->where('taking_id', null)
            ->where('location_id', $this->location->id)
            ->where(function (Builder $builder) use($deviceId) {
                $builder->where('checkpoint_device_id', $deviceId)->orWhere('checkpoint_device_id', null);
            })
            ->first();

        if ($dailySale == null) {
            $dailySale = new DailySale();
            $dailySale->entity_id = $this->entity->id;
            $dailySale->location_id = $this->location->id;
            $dailySale->device_id = $deviceId;
            $dailySale->checkpoint_device_id = $deviceId;
            $dailySale->sales_amount = 0;
            $dailySale->refund_amount = 0;
        }

        # end of day
        if (!$taking->is_shift) { 
            $dailySale->taking_id = $taking->id;
            $dailySale->employee_id = $this->employee->id;
            $dailySale->employee_first_name = $this->employee->first_name ?? '';
            $dailySale->employee_last_name = $this->employee->last_name ?? '';
        }

        $dailySale->sales_at = $this->timeNow;
        $dailySale->local_sales_at = $this->localTimeNow;
        $dailySale->sales_amount += $taking->net_sales_after_tax;
        $dailySale->refund_amount += $taking->net_sales_after_tax_refund;

        $dailySale->save();
    }

    private function setPaymentSummaries(Taking $taking)
    {
        $countedAmount = 0;
        $recordedAmount = 0;
        $differenceAmount = 0;

        foreach ($this->params['paymentSummaries'] as $paymentSummary)
        {
            $countedAmount += $paymentSummary['counted_amount'];
            $recordedAmount += $paymentSummary['recorded_amount'];
            $differenceAmount += $paymentSummary['difference_amount'];
        }

        $taking->counted_amount = $countedAmount;
        $taking->recorded_amount = $recordedAmount;
        $taking->difference_amount = $differenceAmount;
    }

    private function setSaleTransaction(Taking $taking)
    {
        $saleTransactionIds = $this->params['saleTransactionIds'];
        $taking->sale_transaction_ids = $saleTransactionIds;

        foreach (SaleTransaction::whereIn('id', $saleTransactionIds)->get() as $saleTransaction)
        {
            $taking->gross_sales += $saleTransaction->gross_sales;
            $taking->discount_amount += $saleTransaction->discount_amount_before_tax;
            $taking->promo_amount += $saleTransaction->promo_amount_before_tax;
            $taking->surcharge_amount += $saleTransaction->surcharge_amount_before_tax;
            $taking->free_of_charge_amount += $saleTransaction->free_of_charge_amount_before_tax;
            $taking->net_sales += $saleTransaction->net_sales;
            $taking->service_charge += $saleTransaction->service_charge;
            $taking->tax_amount += $saleTransaction->tax_amount;
            $taking->rounding_amount += $saleTransaction->rounding_amount;
            $taking->net_sales_after_tax += $saleTransaction->net_sales_after_tax;
        }

        $details = SaleTransactionDetail::whereIn('sale_transaction_id', $saleTransactionIds)
            ->groupBy('product_id')
            ->select(DB::raw('count(product_id) as total'))
            ->get();

        $taking->product_sold_count = count($details);

        $details = SaleTransactionDetail::whereIn('sale_transaction_id', $saleTransactionIds)
            ->groupBy('product_category_id')
            ->select(DB::raw('count(product_category_id) as total'))
            ->get();

        $taking->product_category_sold_count = count($details);
    }

    private function setSaleRefund(Taking $taking)
    {
        $saleRefundIds = $this->params['saleRefundIds'];
        $taking->sale_refund_ids = $saleRefundIds;
        $taking->gross_refund = 0;
        $taking->discount_amount_refund = 0;
        $taking->promo_amount_refund = 0;
        $taking->surcharge_amount_refund = 0;
        $taking->free_of_charge_amount_refund = 0;
        $taking->net_sales_refund = 0;
        $taking->service_charge_refund = 0;
        $taking->tax_amount_refund = 0;
        $taking->rounding_amount_refund = 0;
        $taking->net_sales_after_tax_refund = 0;

        foreach (SaleRefund::whereIn('id', $this->params['saleRefundIds'])->get() as $saleTransaction)
        {
            $taking->gross_refund += $saleTransaction->gross_sales;
            $taking->discount_amount_refund += $saleTransaction->discount_amount_before_tax;
            $taking->promo_amount_refund += $saleTransaction->promo_amount_before_tax;
            $taking->surcharge_amount_refund += $saleTransaction->surcharge_amount_before_tax;
            $taking->free_of_charge_amount_refund += $saleTransaction->free_of_charge_amount_before_tax;
            $taking->net_sales_refund += $saleTransaction->net_sales;
            $taking->service_charge_refund += $saleTransaction->service_charge;
            $taking->tax_amount_refund += $saleTransaction->tax_amount;
            $taking->rounding_amount_refund += $saleTransaction->rounding_amount;
            $taking->net_sales_after_tax_refund += $saleTransaction->net_sales_after_tax;
        }

        $details = SaleRefundDetail::whereIn('sale_refund_id', $saleRefundIds)
            ->groupBy('product_id')
            ->select(DB::raw('count(product_id) as total'))
            ->get();

        $taking->product_return_count = count($details);

        $details = SaleRefundDetail::whereIn('sale_refund_id', $saleRefundIds)
            ->groupBy('product_category_id')
            ->select(DB::raw('count(product_category_id) as total'))
            ->get();

        $taking->product_category_return_count = count($details);
    }

    private function setMoneyMovement(Taking $taking)
    {
        $taking->money_movement_in_amount = 0;
        $taking->money_movement_in_count = 0;
        $taking->money_movement_out_amount = 0;
        $taking->money_movement_out_count = 0;
    }

    private function setCustomerDeposit(Taking $taking)
    {
        $taking->customer_deposit_amount = 0;
        $taking->customer_deposit_count = 0;
    }

    private function saveTakingPaymentDetails(Taking $taking)
    {
        foreach ($this->params['paymentSummaries'] as $paymentSummary)
        {
            $detail = new TakingPaymentDetail();
            $detail->taking_id = $taking->id;
            $detail->fill($paymentSummary);
            $detail->save();
        }
    }

    private function updateSaleTransaction(Taking $taking)
    {
        $saleTransactionIds = $this->params['saleTransactionIds'];
        SaleTransaction::whereIn('id', $saleTransactionIds)->update([
            "taking_id" => $taking->id,
        ]);
    }

    private function updateSaleRefund(Taking $taking)
    {
        $saleRefundIds = $this->params['saleRefundIds'];
        SaleRefund::whereIn('id', $saleRefundIds)->update([
            "taking_id" => $taking->id,
        ]);
    }
}
