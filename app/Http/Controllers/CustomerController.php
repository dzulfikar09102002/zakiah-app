<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Helpers\Constants\PageConstants;
use App\Helpers\Exceptions\NotFoundException;
use App\Http\Requests\ActiveCustomerRequest;
use App\Http\Requests\ArchiveCustomerRequest;
use App\Http\Requests\IndexCustomerRequest;
use App\Http\Requests\ShowCustomerRequest;
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
        $pagination = $this->service->getCustomers();
        return Inertia::render("customers/index", compact("pagination"));
    }
   
}
