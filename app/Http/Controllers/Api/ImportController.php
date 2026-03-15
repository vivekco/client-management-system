<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function import(Request $request)
    {
        return $this->importService->import($request);
    }

    public function summaries(Request $request)
    {
        return $this->importService->summaries($request);
    }

    public function logs(Request $request)
    {
        return $this->importService->logs($request);
    }
}