<?php

namespace App\Helpers\Services\Customer;

use App\Helpers\Data\Customer\CustomerData;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerCategoryRule;
use App\Models\Entity;
use App\Models\Location;

class CustomerDataBuilder
{
    private array $customerParam;
    private Entity $entity;
    private Location $location;
    private ?CustomerCategory $customerCategory;
    private ?CustomerCategoryRule $customerCategoryRule;

    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, Location $location, ?array $customerParam)
    {
        //
        $this->customerParam = $customerParam;
        $this->entity = $entity;
        $this->location = $location;

        $this->customerCategory = $this->entity->customerCategories()->active()->orderByRaw('case required when true then 0 else 1 end')->first();
        $this->customerCategoryRule = $this->customerCategory?->customerCategoryRule()->first();
    }

    public function build(): ?CustomerData
    {
        //
        if ($this->customerParam == null) {
            return null;
        }

        $params = $this->customerParam;

        $customer = Customer::where('phone_number', $params['phone_number'])
            ->where('phone_number_country_code', $params['phone_number_country_code'])
            ->first();

        $customerData = new CustomerData();

        return $customerData->setEmail($params['email'])
                            ->setId($customer?->id ?? 0)
                            ->setCustomer($customer)
                            ->setFirstName($params['first_name'])
                            ->setLastName($params['last_name'])
                            ->setPhoneNumber($params['phone_number'])
                            ->setPhoneNumberCountryCode($params['phone_number_country_code'])
                            ->setCustomerCategory($this->customerCategory)
                            ->setCustomerCategoryRule($this->customerCategoryRule)
                            ->setEntity($this->entity)
                            ->setLocation($this->location);
    }
}
