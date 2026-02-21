<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Helpers\Data\Customer\CustomerData;
use App\Models\Customer;
use App\Models\User;

class CustomerFinder
{
    private int $totalAmount;
    private ?CustomerData $customerData;

    /**
     * Create a new class instance.
     */
    public function __construct(?CustomerData $customerData, int $totalAmount)
    {
        //
        $this->customerData = $customerData;
        $this->totalAmount = $totalAmount;
    }

    public function create() : ?Customer
    {
        if (!$this->validateCustomerParam()) {
            return null;
        }

        $customer = $this->customerData->getCustomer();
        $customerCategory = $this->customerData->getCustomerCategory();
        $customerCategoryRule = $this->customerData->getCustomerCategoryRule();

        # if not validated can't create customer
        if ($customer != null || ($customerCategoryRule && !$customerCategoryRule->validateRule($this->totalAmount))) {
            return $customer;
        }

        $customer = new Customer();
        $customer->entity_id = $this->customerData->getEntity()->id;
        $customer->location_id = $this->customerData->getLocation()->id;
        $customer->customer_category_id = $customerCategory?->id;
        $customer->user_id = $this->getUser()->id;

        $customer->email = $this->customerData->getEmail();
        $customer->phone_number = $this->customerData->getPhoneNumber();
        $customer->phone_number_country_code = $this->customerData->getPhoneNumberCountryCode();
        $customer->first_name = $this->customerData->getFirstName();
        $customer->last_name = $this->customerData->getLastName();
        $customer->save();

        $customer->customerPoint()->create();

        return $customer;
    }

    private function validateCustomerParam()
    {
        if ($this->customerData == null) {
            return false;
        }

        if ($this->customerData->getPhoneNumber() == '' || $this->customerData->getPhoneNumberCountryCode() == '') {
            return false;
        }

        return true;
    }

    private function getUser(): User
    {
        $user = User::where('phone_number', $this->customerData->getPhoneNumber())
            ->where('phone_number_country_code', $this->customerData->getPhoneNumberCountryCode())
            ->first();

        if ($user == null) {
            $user = new User();
            $user->email = $this->customerData->getEmail();
            $user->phone_number = $this->customerData->getPhoneNumber();
            $user->phone_number_country_code = $this->customerData->getPhoneNumberCountryCode();
        }

        $user->name = $this->customerData->getFirstName(). ' '. $this->customerData->getLastName();
        $user->password = $this->customerData->getPhoneNumber();

        # need to send email
        $user->save();

        return $user;
    }
}
