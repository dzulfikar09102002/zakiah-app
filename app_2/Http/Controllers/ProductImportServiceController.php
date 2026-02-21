<?php

namespace App\Http\Controllers;

use App\Helpers\Services\ProductImported\ProductImportedApproveService;
use App\Helpers\Services\ProductImported\ProductImportedFileReaderService;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\IndexProductImportRequest;
use App\Http\Requests\ShowProductImportRequest;
use App\Http\Requests\StoreProductImportServiceRequest;
use App\Http\Requests\UpdateProductImportServiceRequest;
use App\Http\Requests\UploadProductImportServiceRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\ProductImportService;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductImportServiceController extends Controller
{
    private const PATH = 'public/productImport';

    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductImportRequest $request)
    {
        //
        $datas = ProductImportService::where('entity_id', $request->entity->id);

        if ($request->exists('statuses')) {
            $datas = $datas->whereIn('status', $request->statuses);
        }

        return $datas->paginate($request->limit ?? 15);
    }

    public function upload(UploadProductImportServiceRequest $request)
    {
        $file = $request->file('file');
        $fileUrl = $file->hashName();
        $file->storeAs(self::PATH, $fileUrl);

        $service = new ProductImportedFileReaderService($fileUrl);
        $result = $service->convert();

        if (count($result) < 1) {
            $response = new BaseJsonResponse(null, 'File upload salah');
            return $response->response(500);
        }

        $maxLength = 100;
        if (count($result[0]) > $maxLength) {
            $response = new BaseJsonResponse(null, "Tidak boleh lebih dari $maxLength baris");
            return $response->response(400);
        }

        $response = new BaseJsonResponse($fileUrl);
        return $response->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductImportServiceRequest $request)
    {
        //
        $data = new ProductImportService();

        # start transcation
        DB::beginTransaction();
        try {
            # not in fillable
            $data->entity_id = $request->entity->id;
            $data->file_url = $request->file_url;

            $now = new DateTime();
            $data->code = UniqueCodeGenerator::generateCode();
            $data->employee_id = $request->employee->id;
            $data->employee_requested_by = $request->employee->id;
            $data->requested_at = $now;
            $data->local_requested_at = $now->setTimezone(new DateTimeZone($request->entity->timezone));
            $data->created_by = $request->user()->id;
            $data->updated_by = $request->user()->id;

            if ($request->auto_approve == 'true') {
                $data->employee_approved_by = $request->employee->id;
                $data->approved_at = $now;
                $data->local_approved_at = $now->setTimezone(new DateTimeZone($request->entity->timezone));
            }

            $data->note = $request->note;
            $data->request_note = $request->request_note;

            $data->save();

            # call service sync # next move to job
            // ProductImportedApproveJob::dispatch($request->entity, $data);
    
            $service = new ProductImportedApproveService($request->entity, $data);
            $service->process();

            DB::commit();

            $response = new BaseJsonResponse($data);
            return $response->response();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            
            //delete failed file
            // Storage::delete(self::PATH . '/'. basename($fileUrl));

            $response = new BaseJsonResponse(null, 'Gagal Upload');
            return $response->response(500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProductImportRequest $request, ProductImportService $productImportService)
    {
        //
        if ($request->entity->id != $productImportService->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        $response = new BaseJsonResponse($productImportService->load('productImportServiceDetails'));
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductImportServiceRequest $request, ProductImportService $productImportService)
    {
        //
    }
}
