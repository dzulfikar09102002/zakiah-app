<?php

namespace App\Http\Controllers;

use App\Models\OrderType;
use App\Http\Requests\StoreOrderTypeRequest;
use App\Http\Requests\UpdateOrderTypeRequest;
use App\Services\OrderTypeService;
use Inertia\Inertia;

class OrderTypeController extends Controller
{
    public function __construct(
        private OrderTypeService $service
    ) {}
    public function index()
    {
        $paymentMethods = $this->service->getPaymentMethods();
        $pagination = $this->service->getOrderTypes();
        return Inertia::render('order-types/index', compact('pagination', 'paymentMethods'));
    }

    public function store(StoreOrderTypeRequest $request)
    {
        $this->service->store($request->validated());
        
        return to_route('order-types.index')->with('success', 'Jenis pesanan berhasil ditambahkan');
    }

    public function update(UpdateOrderTypeRequest $request, OrderType $orderType)
    {
        $this->service->update($orderType, $request->validated());
        
        return to_route('order-types.index')->with('success', 'Jenis Pesanan berhasil diperbarui');
    }

    public function destroy(OrderType $orderType)
    {
        $this->service->delete($orderType);
        
        return to_route('order-types.index')->with('success', 'Jenis Pesanan berhasil dihapus');
    }
    public function restore(int $id)
    {
        $this->service->restore($id);
        
        return to_route('order-types.index')->with('success', 'Jenis Pesanan berhasil dipulihkan');
    }

    public function deleted()
    {
        $onlyTrashed = true;
        $paymentMethods = $this->service->getPaymentMethods();
        $pagination = $this->service->getDeletedOrderTypes();
        
        return Inertia::render('order-types/index', compact('pagination', 'onlyTrashed', 'paymentMethods'));
    }
}
