<?php

namespace App\Http\Requests;

use App\Enums\TaxSettingEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ImportProductRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Aturan validasi per-item identik dengan StoreProductRequest,
     * hanya dibungkus dalam array "products.*".
     */
    public function rules(): array
    {
        return [
            'products' => ['required', 'array', 'min:1'],
            'products.*.name' => ['required'],
            'products.*.sku' => ['required'],
            'products.*.barcode' => ['required'],
            'products.*.description' => ['nullable'],
            'products.*.sell_price' => ['required', 'integer', 'min:0'],
            'products.*.last_buying_price' => ['required', 'integer', 'min:0'],
            'products.*.product_category_id' => ['nullable'],
            'products.*.child_product_category_id' => ['nullable'],
            'products.*.product_unit_id' => ['required'],
            'products.*.product_sell_unit_id' => ['required'],
            'products.*.location_id' => ['required'],
            'products.*.image_url' => ['nullable'],
            'products.*.sell_to_customer' => ['boolean'],
            'products.*.service' => ['boolean'],
            'products.*.modifier' => ['boolean'],
            'products.*.allow_custom_price' => ['boolean'],
            'products.*.select_all_location' => ['boolean'],
            'products.*.location_ids' => ['nullable', 'array'],
            'products.*.exclude_location_ids' => ['nullable', 'array'],
            'products.*.tax_id' => ['nullable'],
            'products.*.tax_setting' => ['nullable', Rule::enum(TaxSettingEnum::class)],
            'products.*.product_unit_conversions' => ['nullable'],
            'products.*.product_unit_conversions.*.unit_id' => ['required'],
            'products.*.product_unit_conversions.*.quantity' => ['required', 'integer', 'min:0'],
            'products.*.product_unit_conversions.*.internal_price' => ['required', 'integer', 'min:0'],
            'products.*.stock_movements' => ['nullable', 'array'],
            'products.*.stock_movements.*.location_id' => ['required'],
            'products.*.stock_movements.*.stock' => ['required'],
            'products.*.stock_movements.*.buying_price' => ['required'],
            'products.*.product_sell_prices' => ['nullable'],
            'products.*.product_sell_prices.*.location_id' => ['nullable'],
            'products.*.product_sell_prices.*.order_type_id' => ['nullable'],
            'products.*.product_sell_prices.*.product_unit_id' => ['nullable'],
            'products.*.product_sell_prices.*.tax_id' => ['nullable'],
            'products.*.product_sell_prices.*.tax_setting' => ['nullable', Rule::enum(TaxSettingEnum::class)],
            'products.*.product_sell_prices.*.sell_price' => ['required', 'integer', 'min:0'],
            'products.*.supplier_name' => 'nullable',
        ];
    }

    public function attributes(): array
    {
        return [
            'products' => 'daftar produk',
            'products.*.name' => 'nama produk',
            'products.*.sell_price' => 'harga jual',
            'products.*.last_buying_price' => 'harga beli',
            'products.*.product_category_id' => 'kategori',
            'products.*.location_id' => 'lokasi',
            'products.*.product_unit_id' => 'satuan',
            'products.*.stock_movements.*.location_id' => 'lokasi stok',
            'products.*.stock_movements.*.stock' => 'stok awal',
        ];
    }
}