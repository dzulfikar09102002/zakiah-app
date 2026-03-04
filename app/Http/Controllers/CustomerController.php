<?php

namespace App\Http\Controllers;

use App\Enums\PhoneNumberCountryCodeEnum;
use App\Enums\StatusEnum;
use App\Helpers\Constants\PageConstants;
use App\Helpers\Exceptions\NotFoundException;
use App\Http\Requests\ActiveCustomerRequest;
use App\Http\Requests\ArchiveCustomerRequest;
use App\Http\Requests\IndexCustomerRequest;
use App\Http\Requests\ShowCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $service
) {}
    public function index()
    {
        $locations = $this->service->getLocations();
        $phoneCountryCodes = PhoneNumberCountryCodeEnum::options();
        $customerCategories = $this->service->getCustomerCategories();
        $pagination = $this->service->getCustomers();
        return Inertia::render("customers/index", compact("pagination", "customerCategories", "phoneCountryCodes", "locations"));
    }
   
    public function store(StoreCustomerRequest $request)
    {
        $this->service->store($request->validated());
        return to_route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        return to_route('customers.index')->with('success', 'Pelanggan berhasil diperbarui');
    }
}
