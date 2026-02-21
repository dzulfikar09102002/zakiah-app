<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirCustomerRequest;
use App\Http\Requests\Kasir\StoreKasirCustomerRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class KasirCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirCustomerRequest $request)
    {
        //
        $params = $request->validated();

        $keyword = $params['keyword'];
        $phoneSearch = $this->phoneNumberSearchAble($keyword);

        $datas = Customer::where('entity_id', $request->entity->id);
        if ($phoneSearch == null) {
            $datas->where('email', $keyword);
        } else {
            $datas->where(function (Builder $builder) use ($keyword, $phoneSearch) {
                $builder->where('email', $keyword)
                    ->orWhere(function (Builder $builder) use ($phoneSearch) {
                        $builder->whereIn('phone_number', $phoneSearch['phone_number'])
                            ->whereIn('phone_number_country_code', $phoneSearch['phone_number_country_code']);
                    });
            });
        }

        return $datas->cursorPaginate($request->limit)->appends($params);
    }

    public function store(StoreKasirCustomerRequest $request)
    {
        $params = $request->validated();

        $customer = new Customer();
        # not in fillable
        $customer->entity_id = $request->entity->id;
        $customer->status = 'active';
        $customer->created_by = $request->user()->id;
        $customer->updated_by = $request->user()->id;
        $customer->user_id = $this->getUser($params)->id;
        $customer->fill($params);

        $customer->save();

        $response = new BaseJsonResponse($customer);
        return $response->response();
    }

    # return
    # example 62812
    # return 2812, 812, 62812
    # example 0812
    # return 812, 12,  0812
    private function phoneNumberSearchAble(string $keyword, string $defaultPhoneCode = '62'): ?array
    {
        $codeLen = strlen($defaultPhoneCode);
        $keywordLen = strlen($keyword);

        if ($keywordLen < $codeLen) {
            return null;
        }

        $phoneNumbers = [$keyword];
        for ($i = 1; $i <= $codeLen; $i++) {
            array_push($phoneNumbers, substr($keyword, $i, $keywordLen));
        }

        $phoneNumberCode = [
            '0',
            $defaultPhoneCode,
        ];

        return [
            'phone_number' => $phoneNumbers,
            'phone_number_country_code' => $phoneNumberCode,
        ];
    }

    private function getUser($params): User
    {
        $user = User::where('phone_number', $params['phone_number'])
            ->where('phone_number_country_code', $params['phone_number_country_code'])
            ->first();

        if ($user == null) {
            $user = new User();
            $user->email = $params['email'] ?? null;
            $user->phone_number = $params['phone_number'];
            $user->phone_number_country_code = $params['phone_number_country_code'];
        }

        $user->name = $params['phone_number']. ' '. $params['phone_number_country_code'];
        $user->password = $params['phone_number'];

        # need to send email
        $user->save();

        return $user;
    }
}
