<?php

namespace App\Helpers\Data\Product;

use App\Enums\TaxSettingEnum;
use App\Helpers\UniqueCodeGenerator;
use App\Models\Entity;
use App\Models\Location;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Support\Collection;

class ProductRequest
{
    private string $code, $sku, $barcode, $name, $searchName;
    private ?string $description;
    private bool $sellToCustomer, $service, $modifier, $allowCustomPrice, $selectAllLocation;
    private int $sellPrice, $stock, $costFfGoodsSold;
    private array $fillable;

    private Entity $entity;
    private User $createdBy, $updatedBy;
    private ?Tax $tax;
    private ?TaxSettingEnum $taxSetting;
    private ?Location $location;
    private ?ProductCategory $productCategory;
    private ?ProductUnit $productSellUnit, $productUnit;

    /**
     * int[]
     */
    private Collection $locationIds;
    private array $stockMovements;

    /**
     * int[]
     */
    private Collection $excludeLocationIds;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
        $this->code = UniqueCodeGenerator::generateCode();
    }

    public function fillable(): array
    {
        return [];
    }

    /**
     * Get the value of code
     */ 
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get the value of sku
     */ 
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * Set the value of sku
     *
     * @return  self
     */ 
    public function setSku(string $sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get the value of barcode
     */ 
    public function getBarcode(): string
    {
        return $this->barcode;
    }

    /**
     * Set the value of barcode
     *
     * @return  self
     */ 
    public function setBarcode(string $barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName(string $name)
    {
        $this->name = $name;
        $this->setSearchName($this->name);

        return $this;
    }
    
    /**
     * Get the value of name
     */ 
    public function getSearchName(): string
    {
        return $this->searchName;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setSearchName(string $searchName)
    {
        $this->searchName = $searchName;

        return $this;
    }

    /**
     * Get the value of description
     */ 
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of sellToCustomer
     */ 
    public function getSellToCustomer(): bool
    {
        return $this->sellToCustomer;
    }

    /**
     * Set the value of sellToCustomer
     *
     * @return  self
     */ 
    public function setSellToCustomer(bool $sellToCustomer)
    {
        $this->sellToCustomer = $sellToCustomer;

        return $this;
    }

    /**
     * Get the value of service
     */ 
    public function getService(): bool
    {
        return $this->service;
    }

    /**
     * Set the value of service
     *
     * @return  self
     */ 
    public function setService(bool $service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get the value of modifier
     */ 
    public function getModifier(): bool
    {
        return $this->modifier;
    }

    /**
     * Set the value of modifier
     *
     * @return  self
     */ 
    public function setModifier(bool $modifier)
    {
        $this->modifier = $modifier;

        return $this;
    }

    /**
     * Get the value of allowCustomPrice
     */ 
    public function getAllowCustomPrice(): bool
    {
        return $this->allowCustomPrice;
    }

    /**
     * Set the value of allowCustomPrice
     *
     * @return  self
     */ 
    public function setAllowCustomPrice(bool $allowCustomPrice)
    {
        $this->allowCustomPrice = $allowCustomPrice;

        return $this;
    }

    /**
     * Get the value of selectAllLocation
     */ 
    public function getSelectAllLocation(): bool
    {
        return $this->selectAllLocation;
    }

    /**
     * Set the value of selectAllLocation
     *
     * @return  self
     */ 
    public function setSelectAllLocation(bool $selectAllLocation)
    {
        $this->selectAllLocation = $selectAllLocation;

        return $this;
    }

    /**
     * Get the value of sellPrice
     */ 
    public function getSellPrice(): int
    {
        return $this->sellPrice;
    }

    /**
     * Set the value of sellPrice
     *
     * @return  self
     */ 
    public function setSellPrice(int $sellPrice)
    {
        $this->sellPrice = $sellPrice;

        return $this;
    }

    /**
     * Get the value of stock
     */ 
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * Set the value of stock
     *
     * @return  self
     */ 
    public function setStock(int $stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get the value of entity
     */ 
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */ 
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of tax
     */ 
    public function getTax(): ?Tax
    {
        return $this->tax;
    }

    /**
     * Set the value of tax
     *
     * @return  self
     */ 
    public function setTax(?Tax $tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get the value of location
     */ 
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */ 
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of productCategory
     */ 
    public function getProductCategory(): ?ProductCategory
    {
        return $this->productCategory;
    }

    /**
     * Set the value of productCategory
     *
     * @return  self
     */ 
    public function setProductCategory(?ProductCategory $productCategory)
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    /**
     * Get the value of productSellUnit
     */ 
    public function getProductSellUnit(): ?ProductUnit
    {
        return $this->productSellUnit;
    }

    /**
     * Set the value of productSellUnit
     *
     * @return  self
     */ 
    public function setProductSellUnit(?ProductUnit $productSellUnit)
    {
        $this->productSellUnit = $productSellUnit;

        return $this;
    }

    /**
     * Get the value of productUnit
     */ 
    public function getProductUnit(): ?ProductUnit
    {
        return $this->productUnit;
    }

    /**
     * Set the value of productUnit
     *
     * @return  self
     */ 
    public function setProductUnit(?ProductUnit $productUnit)
    {
        $this->productUnit = $productUnit;

        return $this;
    }

    /**
     * Get the value of taxSetting
     */ 
    public function getTaxSetting(): ?TaxSettingEnum
    {
        return $this->taxSetting;
    }

    /**
     * Set the value of taxSetting
     *
     * @return  self
     */ 
    public function setTaxSetting(?TaxSettingEnum $taxSetting)
    {
        $this->taxSetting = $taxSetting;

        return $this;
    }

    /**
     * Get int[]
     */ 
    public function getLocationIds(): Collection
    {
        return $this->locationIds;
    }

    /**
     * Set int[]
     *
     * @return  self
     */ 
    public function setLocationIds(Collection $locationIds)
    {
        $this->locationIds = $locationIds;

        return $this;
    }

    /**
     * Get int[]
     */ 
    public function getExcludeLocationIds(): Collection
    {
        return $this->excludeLocationIds;
    }

    /**
     * Set int[]
     *
     * @return  self
     */ 
    public function setExcludeLocationIds(Collection $excludeLocationIds)
    {
        $this->excludeLocationIds = $excludeLocationIds;

        return $this;
    }

    /**
     * Get the value of fillable
     */ 
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * Set the value of fillable
     *
     * @return  self
     */ 
    public function setFillable(array $fillable)
    {
        $this->fillable = $fillable;

        if (array_key_exists('tax_setting', $fillable)) {
            $this->setTaxSetting($fillable['tax_setting']);
        }

        if (array_key_exists('sell_price', $fillable)) {
            $this->setSellPrice($fillable['sell_price']);
        }

        if (array_key_exists('last_buying_price', $fillable)) {
            $this->setCostFfGoodsSold($fillable['last_buying_price']);
        }

        return $this;
    }

    /**
     * Get the value of stockMovements
     */ 
    public function getStockMovements(): array
    {
        return $this->stockMovements;
    }

    /**
     * Set the value of stockMovements
     *
     * @return  self
     */ 
    public function setStockMovements(array $stockMovements)
    {
        $this->stockMovements = $stockMovements;

        return $this;
    }

    /**
     * Get the value of createdBy
     */ 
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the value of createdBy
     *
     * @return  self
     */ 
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the value of updatedBy
     */ 
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set the value of updatedBy
     *
     * @return  self
     */ 
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get the value of costFfGoodsSold
     */ 
    public function getCostFfGoodsSold()
    {
        return $this->costFfGoodsSold;
    }

    /**
     * Set the value of costFfGoodsSold
     *
     * @return  self
     */ 
    public function setCostFfGoodsSold($costFfGoodsSold)
    {
        $this->costFfGoodsSold = $costFfGoodsSold;

        return $this;
    }
}
