<?php

namespace App\Helpers\Services\ProductImported;

use App\Imports\ProductImportedServiceDetailImport;
use App\Models\Entity;
use App\Models\ProductImportService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportedApproveService
{
    private const PATH = 'public/productImport';

    private Entity $entity;
    private ProductImportService $importService;

    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, ProductImportService $importService)
    {
        //
        $this->entity = $entity;
        $this->importService = $importService;
    }

    public function process() {
        # start transcation
        DB::beginTransaction();
        try {
            Excel::import(new ProductImportedServiceDetailImport($this->entity, $this->importService), self::PATH . '/'. basename($this->importService->file_url));
            DB::commit();

            // for now not delete
            // Storage::delete(self::PATH . '/'. basename($this->importService->file_url));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();

            throw $e;
        }
    }

    // private function
}
