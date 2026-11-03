<?php

use App\Helpers\Data\Product\ProductRequest;
use App\Helpers\Services\Product\ProductCreatorServices;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\ProductUnit;
use App\Models\User;
use App\Services\ProductService;
use App\Services\StockRemainingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Tabel domain tidak ada di database/migrations, jadi skema minimal dibuat di sini.
 * Baris stok dimasukkan lewat DB::table karena duplikat di production juga tidak lewat Eloquent.
 */
beforeEach(function () {
    Schema::create('entities', function (Blueprint $table) {
        $table->id();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('entity_id');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('locations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_units', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->string('name');
        $table->string('sku')->nullable();
        $table->string('barcode')->nullable();
        $table->string('status')->default('active');
        $table->unsignedBigInteger('product_category_id')->nullable();
        $table->unsignedBigInteger('product_unit_id')->nullable();
        $table->integer('sell_price')->default(0);
        $table->integer('cost_of_goods_sold')->default(0);
        $table->integer('last_buying_price')->default(0);
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_location_stocks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id');
        $table->unsignedBigInteger('location_id');
        $table->unsignedBigInteger('product_unit_id');
        $table->integer('stock')->default(0);
        $table->integer('last_in_stock')->default(0);
        $table->integer('last_out_stock')->default(0);
        $table->integer('last_buy_price')->default(0);
        $table->integer('average_buy_price')->default(0);
        $table->integer('lowest_buy_price')->default(0);
        $table->integer('highest_buy_price')->default(0);
        $table->char('checksum', 32);
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_stock_movements', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id');
        $table->unsignedBigInteger('location_id');
        $table->unsignedBigInteger('product_unit_id');
        $table->unsignedBigInteger('original_product_unit_id')->nullable();
        $table->string('resource_type');
        $table->unsignedBigInteger('resource_id');
        $table->unsignedInteger('original_stock_in')->default(0);
        $table->unsignedInteger('original_stock_out')->default(0);
        $table->unsignedInteger('original_buying_price')->default(0);
        $table->unsignedInteger('conversion_stock')->default(0);
        $table->unsignedInteger('stock_in');
        $table->unsignedInteger('stock_out');
        $table->unsignedInteger('buying_price');
        $table->timestamps();
    });

    $user = User::factory()->create();

    DB::table('entities')->insert(['id' => 1]);
    DB::table('employees')->insert(['user_id' => $user->id, 'entity_id' => 1]);
    DB::table('locations')->insert([
        ['id' => 2, 'entity_id' => 1, 'name' => 'Tulangan'],
        ['id' => 3, 'entity_id' => 1, 'name' => 'Mojosari'],
    ]);
    DB::table('product_units')->insert(['id' => 1, 'name' => 'PCS']);
    DB::table('product_categories')->insert(['id' => 1, 'name' => 'Kemasan']);
    DB::table('products')->insert([
        'id' => 100,
        'entity_id' => 1,
        'name' => 'Paperbag',
        'sku' => 'SKU-100',
        'barcode' => 'BAR-100',
        'product_category_id' => 1,
        'product_unit_id' => 1,
    ]);

    // Lokasi 3 punya dua baris (0 dan 36), lokasi 2 satu baris (22).
    stockRow(id: 10, locationId: 2, stock: 22);
    stockRow(id: 11, locationId: 3, stock: 0);
    stockRow(id: 12, locationId: 3, stock: 36);

    $this->actingAs($user);
});

function stockRow(int $id, int $locationId, int $stock, array $extra = []): void
{
    DB::table('product_location_stocks')->insert([
        'id' => $id,
        'product_id' => 100,
        'location_id' => $locationId,
        'product_unit_id' => 1,
        'stock' => $stock,
        'checksum' => md5((string) $id),
        ...$extra,
    ]);
}

function reduceStock(int $locationId, int $delta): void
{
    $request = new ProductRequest;
    $request->setProductUnit(ProductUnit::query()->find(1));
    $request->setLocationIds(collect([2, 3]));
    $request->setStockMovements([
        ['location_id' => $locationId, 'stock' => $delta, 'buying_price' => 1000],
    ]);

    $method = new ReflectionMethod(ProductCreatorServices::class, 'createStockMovement');
    $method->invoke(new ProductCreatorServices($request), Product::query()->find(100));
}

it('sums duplicate rows for the product dropdown stock', function () {
    $options = app(ProductService::class)->getDropdownOptions('Paperbag', 3);

    expect($options[0]['stock'])->toBe(36);

    DB::table('product_location_stocks')->where('id', 11)->update(['stock' => 5]);

    expect(app(ProductService::class)->getDropdownOptions('Paperbag', 3)[0]['stock'])->toBe(41);
});

it('sums duplicate rows in the import stock lookup', function () {
    DB::table('product_location_stocks')->where('id', 11)->update(['stock' => 5]);

    expect(app(ProductService::class)->getCurrentStockMap(['SKU-100']))
        ->toEqual(['SKU-100' => [2 => 22, 3 => 41]]);
});

it('lists a duplicated product once with its summed stock in the remaining stock report', function () {
    DB::table('product_location_stocks')->where('id', 11)->update(['stock' => 5]);

    $rows = app(StockRemainingService::class)->getRemainingStock(3);

    expect($rows->total())->toBe(1)
        ->and($rows->items()[0]->stock)->toBe(41)
        ->and($rows->items()[0]->product->name)->toBe('Paperbag');
});

it('lists a duplicated product once with its summed stock in the remaining stock pdf', function () {
    DB::table('product_location_stocks')->where('id', 11)->update(['stock' => 5]);

    $rows = app(StockRemainingService::class)->getRemainingStockForPdf(3)->all();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['stock'])->toBe(41)
        ->and($rows[0]['category'])->toBe('Kemasan');
});

it('hides products whose summed stock cancels out in the remaining stock report', function () {
    DB::table('product_location_stocks')->where('id', 11)->update(['stock' => -36]);

    expect(app(StockRemainingService::class)->getRemainingStock(3)->total())->toBe(0);
});

it('shows a product with exactly one item left in the remaining stock report', function () {
    DB::table('product_location_stocks')->where('id', 10)->update(['stock' => 1]);

    $rows = app(StockRemainingService::class)->getRemainingStock(2);

    expect($rows->total())->toBe(1)
        ->and($rows->items()[0]->stock)->toBe(1);
});

it('exports a duplicated product once with its summed stock', function () {
    DB::table('product_location_stocks')->where('id', 11)->update(['stock' => 5]);

    $rows = collect(app(StockRemainingService::class)->getAllStockForExport(3))->values();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['Stok'])->toBe(41);
});

it('validates a stock reduction against the summed stock of the location', function () {
    reduceStock(locationId: 3, delta: -10);

    expect(ProductStockMovement::query()->where('location_id', 3)->sum('stock_out'))->toEqual(10);
});

it('rejects a stock reduction above the summed stock of the location', function () {
    expect(fn () => reduceStock(locationId: 3, delta: -37))
        ->toThrow(ValidationException::class);

    expect(ProductStockMovement::query()->count())->toBe(0);
});

it('keeps buy price history when a movement hits a row with zero stock', function () {
    DB::table('product_location_stocks')->where('id', 10)->update([
        'stock' => 0,
        'lowest_buy_price' => 1000,
        'highest_buy_price' => 5000,
        'average_buy_price' => 3000,
    ]);

    $movement = new ProductStockMovement;
    $movement->product_id = 100;
    $movement->location_id = 2;
    $movement->product_unit_id = 1;
    $movement->resource_type = 'Test';
    $movement->resource_id = 1;
    $movement->original_stock_in = 5;
    $movement->original_buying_price = 2000;
    $movement->conversion_stock = 1;
    $movement->stock_in = 5;
    $movement->stock_out = 0;
    $movement->buying_price = 2000;
    $movement->save();

    $row = DB::table('product_location_stocks')->where('id', 10)->first();

    expect($row->stock)->toEqual(5)
        ->and($row->lowest_buy_price)->toEqual(1000)
        ->and($row->highest_buy_price)->toEqual(5000)
        ->and($row->average_buy_price)->toEqual(2500);
});

it('starts buy price history from the movement when the stock row is new', function () {
    $movement = new ProductStockMovement;
    $movement->product_id = 100;
    $movement->location_id = 2;
    $movement->product_unit_id = 5;
    $movement->resource_type = 'Test';
    $movement->resource_id = 1;
    $movement->original_stock_in = 4;
    $movement->original_buying_price = 2000;
    $movement->conversion_stock = 1;
    $movement->stock_in = 4;
    $movement->stock_out = 0;
    $movement->buying_price = 2000;
    $movement->save();

    $row = DB::table('product_location_stocks')
        ->where('location_id', 2)->where('product_unit_id', 5)->first();

    expect($row->stock)->toEqual(4)
        ->and($row->lowest_buy_price)->toEqual(2000)
        ->and($row->highest_buy_price)->toEqual(2000);
});
