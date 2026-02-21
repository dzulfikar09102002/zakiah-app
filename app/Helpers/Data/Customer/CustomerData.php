<?php

namespace App\Helpers\Data\Customer;

use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerCategoryRule;
use App\Models\Entity;
use App\Models\Location;

class CustomerData
{
    private int $id;
    private ?string $email;
    private string $firstName, $lastName, $phoneNumber, $phoneNumberCountryCode;
    private ?Customer $customer;
    private Entity $entity;
    private Location $location;
    private ?CustomerCategory $customerCategory;
    private ?CustomerCategoryRule $customerCategoryRule;
    
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of firstName
     */ 
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @return  self
     */ 
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of lastName
     */ 
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @return  self
     */ 
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the value of phoneNumber
     */ 
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set the value of phoneNumber
     *
     * @return  self
     */ 
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get the value of phoneNumberCountryCode
     */ 
    public function getPhoneNumberCountryCode()
    {
        return $this->phoneNumberCountryCode;
    }

    /**
     * Set the value of phoneNumberCountryCode
     *
     * @return  self
     */ 
    public function setPhoneNumberCountryCode($phoneNumberCountryCode)
    {
        $this->phoneNumberCountryCode = $phoneNumberCountryCode;

        return $this;
    }

    /**
     * Get the value of customerCategory
     */ 
    public function getCustomerCategory()
    {
        return $this->customerCategory;
    }

    /**
     * Set the value of customerCategory
     *
     * @return  self
     */ 
    public function setCustomerCategory($customerCategory)
    {
        $this->customerCategory = $customerCategory;

        return $this;
    }

    /**
     * Get the value of entity
     */ 
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */ 
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of customer
     */ 
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set the value of customer
     *
     * @return  self
     */ 
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone_number' => $this->phoneNumber,
            'phone_number_country_code' => $this->phoneNumberCountryCode,
            'customer_category' => $this->toCustomerCategoryArray(),
        ];
    }

    public function toCustomerCategoryArray(): ?array
    {
        $customerCategory = $this->getCustomerCategory();
        if ($customerCategory == null) return null;

        return [
            'id' => $customerCategory->id,
            'name' => $customerCategory->name,
            'required' => $customerCategory->required,
        ];
    }

    /**
     * Get the value of customerCategoryRule
     */ 
    public function getCustomerCategoryRule()
    {
        return $this->customerCategoryRule;
    }

    /**
     * Set the value of customerCategoryRule
     *
     * @return  self
     */ 
    public function setCustomerCategoryRule($customerCategoryRule)
    {
        $this->customerCategoryRule = $customerCategoryRule;

        return $this;
    }

    /**
     * Get the value of location
     */ 
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */ 
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }
}
