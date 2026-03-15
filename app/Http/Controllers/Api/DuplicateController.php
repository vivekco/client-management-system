<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DuplicateService;
use Illuminate\Http\Request;

class DuplicateController extends Controller
{
    protected DuplicateService $duplicateService;

    public function __construct(DuplicateService $duplicateService)
    {
        $this->duplicateService = $duplicateService;
    }

    public function index(Request $request)
    {
        return $this->duplicateService->index($request);
    }

    public function show($id)
    {
        return $this->duplicateService->show($id);
    }
}