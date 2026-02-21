<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexDailySaleRequest;
use App\Http\Requests\ShowDailySaleRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\DailySale;
use App\Models\Taking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DailySaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexDailySaleRequest $request)
    {
        //
        $params = $request->validated();

        $datas = DailySale::where('location_id', $params['loc'])
            ->where('entity_id', $request->entity->id)
            ->where('sales_at', '>=', $params['start_at'])
            ->where('sales_at', '<=', $params['end_at'])
            ->select(['id', 'taking_id', 'local_sales_at', 'sales_amount', 'refund_amount', 'employee_id', 'employee_first_name', 'employee_last_name'])
            ->orderByDesc('id');

        return $datas->paginate($request->limit)->appends($params);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowDailySaleRequest $request, string $id)
    {
        //
        $dailySale = DailySale::with([
            'location:id,name',
        ])->where('entity_id', $request->entity->id)->where('id', $id)->first();
        if ($dailySale == null) {
            $response = new BaseJsonResponse(null, __('general.not_found'));
            return $response->response(404);
        }

        $parentId = $dailySale->taking_id;

        $takings = Taking::with([
                'location:id,name',
                'takingPaymentDetails.paymentMethod:id,name',
                'takingTaxDetails',
            ])
            ->where('entity_id', $request->entity->id)
            ->where('checkpoint_device_id', $dailySale->checkpoint_device_id)
            ->where('location_id', $dailySale->location_id)
            ->orderBy('id');

        # not close the day yet
        if ($parentId == null) {
            $takings->where('parent_id', $parentId)->where('is_shift', true);
        } else {
            $takings->where(function (Builder $builder) use($parentId) {
                $builder
                    ->where(function (Builder $builder) use($parentId) {
                        $builder->where('parent_id', $parentId)->where('is_shift', true);
                    })
                    ->orWhere(function (Builder $builder) {
                        $builder->where('parent_id', null)->where('is_shift', false);
                    });
            });
        }

        $takings = $takings->get();
        $jsonResponse = array_merge(
            $dailySale->toArray(),
            ['takingAll' => $this->takingAll($dailySale, $takings)],
            ['takings' => $this->takingResponse($takings)],
        );

        $response = new BaseJsonResponse($jsonResponse);
        return $response->response();
    }

    public function showPdf(string $id)
    {
        //
        $dailySale = DailySale::with([
            'location',
        ])
            // ->where('entity_id', $request->entity->id)
            ->where('id', $id)->first();
        if ($dailySale == null) {
            $response = new BaseJsonResponse(null, __('general.not_found'));
            return $response->response(404);
        }

        $parentId = $dailySale->taking_id;

        $takings = Taking::with([
                'location',
                'takingPaymentDetails.paymentMethod:id,name',
                'takingTaxDetails',
            ])
            // ->where('entity_id', $request->entity->id)
            ->where('checkpoint_device_id', $dailySale->checkpoint_device_id)
            ->where('location_id', $dailySale->location_id)
            ->orderBy('id');

        # not close the day yet
        if ($parentId == null) {
            $takings->where('parent_id', $parentId)->where('is_shift', true);
        } else {
            $takings->where(function (Builder $builder) use($parentId) {
                $builder
                    ->where(function (Builder $builder) use($parentId) {
                        $builder->where('parent_id', $parentId)->where('is_shift', true);
                    })
                    ->orWhere(function (Builder $builder) {
                        $builder->where('parent_id', null)->where('is_shift', false);
                    });
            });
        }

        $takings = $takings->get();
        // $jsonResponse = array_merge(
        //     $dailySale->toArray(),
        //     ['takingAll' => $this->takingAll($dailySale, $takings)],
        //     ['takings' => $this->takingResponse($takings)],
        // );

        return view('pdf.daily_sale', $this->takingAll($dailySale, $takings));
    }

    private function takingResponse(Collection $takings): Collection
    {
        $responses = new Collection();
        foreach ($takings as $taking) {
            $responses->push(array_merge($taking->toArray(), [
                "salesSummaries" => $this->getSalesSummaries($taking),
            ]));
        }

        return $responses;
    }

    private function getSalesSummaries(Taking $taking)
    {
        return [
            'grossSales' => $taking->gross_sales,
            'discountBeforeTax' => $taking->discount_amount,
            'promoBeforeTax' => $taking->promo_amount,
            'surchargeBeforeTax' => $taking->surcharge_amount,
            'freeOfChargeBeforeTax' => $taking->free_of_charge_amount,
            'netSales' => $taking->net_sales,
            'serviceCharge' => $taking->service_charge,
            'taxAmount' => $taking->tax_amount,
            'roundingAmount' => $taking->rounding_amount,
            'netSalesAfterTax' => $taking->net_sales_after_tax,
        ];
    }

    private function takingAll(DailySale $dailySale, Collection $takings): array
    {
        $all = [
            'local_taking_at' => $dailySale->local_sales_at, 
            'location' => $dailySale->location, 
            'counted_amount' => 0,
            'discount_amount' => 0,
            'discount_amount_refund' => 0,
            'free_of_charge_amount' => 0,
            'free_of_charge_amount_refund' => 0,
            'gross_refund' => 0,
            'gross_sales' => 0,
            'net_sales' => 0,
            'net_sales_refund' => 0,
            'net_sales_after_tax' => 0,
            'net_sales_after_tax_refund' => 0,
            'promo_amount' => 0,
            'promo_amount_refund' => 0,
            'recorded_amount' => 0,
            'refund_count' => 0,
            'sales_count' => 0,
            'service_charge' => 0,
            'service_charge_refund' => 0,
            'surcharge_amount' => 0,
            'surcharge_amount_refund' => 0,
            'tax_amount' => 0,
            'tax_amount_refund' => 0,
            'employee_id' => $dailySale->employee_id,
            'employee_first_name' => $dailySale->employee_first_name,
            'employee_last_name' => $dailySale->employee_last_name,
            'taking_payment_details' => [],
        ];

        $payments = array();

        foreach ($takings as $taking) {
            $all['counted_amount'] += $taking['counted_amount'];
            $all['discount_amount'] += $taking['discount_amount'];
            $all['discount_amount_refund'] += $taking['discount_amount_refund'];
            $all['free_of_charge_amount'] += $taking['free_of_charge_amount'];
            $all['free_of_charge_amount_refund'] += $taking['free_of_charge_amount_refund'];
            $all['gross_refund'] += $taking['gross_refund'];
            $all['gross_sales'] += $taking['gross_sales'];
            $all['net_sales'] += $taking['net_sales'];
            $all['net_sales_refund'] += $taking['net_sales_refund'];
            $all['net_sales_after_tax'] += $taking['net_sales_after_tax'];
            $all['net_sales_after_tax_refund'] += $taking['net_sales_after_tax_refund'];
            $all['promo_amount'] += $taking['promo_amount'];
            $all['promo_amount_refund'] += $taking['promo_amount_refund'];
            $all['recorded_amount'] += $taking['recorded_amount'];
            $all['refund_count'] += $taking['refund_count'];
            $all['sales_count'] += $taking['sales_count'];
            $all['service_charge'] += $taking['service_charge'];
            $all['service_charge_refund'] += $taking['service_charge_refund'];
            $all['surcharge_amount'] += $taking['surcharge_amount'];
            $all['surcharge_amount_refund'] += $taking['surcharge_amount_refund'];
            $all['tax_amount'] += $taking['tax_amount'];
            $all['tax_amount_refund'] += $taking['tax_amount_refund'];

            foreach ($taking['takingPaymentDetails'] as $taking_payment_detail)
            {
                $payment_method_id = $taking_payment_detail['payment_method_id'];
                if (!array_key_exists($payment_method_id, $payments)) {
                    $payments[$payment_method_id] = [
                        'counted_amount' => 0,
                        'recorded_amount' => 0,
                        'difference_amount' => 0,
                        'sales_amount' => 0,
                    ];
                }

                $payments[$payment_method_id]['counted_amount'] += $taking_payment_detail['counted_amount'];
                $payments[$payment_method_id]['recorded_amount'] += $taking_payment_detail['recorded_amount'];
                $payments[$payment_method_id]['difference_amount'] += $taking_payment_detail['difference_amount'];
                $payments[$payment_method_id]['sales_amount'] += $taking_payment_detail['sales_amount'];
                $payments[$payment_method_id]['payment_method'] = $taking_payment_detail['paymentMethod'];
            }
        }

        foreach ($payments as $payment)
        {
            array_push($all['taking_payment_details'], $payment);
        }

        return $all;
    }
}
