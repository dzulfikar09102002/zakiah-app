<?php

namespace App\Jobs;

use App\Helpers\Services\ProductImported\ProductImportedApproveService;
use App\Models\Entity;
use App\Models\ProductImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductImportedApproveJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Entity $entity,
        public ProductImportService $productImportService,
    )
    {
        //
        $this->entity = $entity->withoutRelations();
        $this->productImportService = $productImportService->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $service = new ProductImportedApproveService($this->entity, $this->productImportService);
        $service->process();
    }
}
