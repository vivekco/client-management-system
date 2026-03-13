<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ImportCsvRequest;
use App\Jobs\ImportClientsJob;

class ImportController extends Controller
{
    public function import(ImportCsvRequest $request)
    {
        $path = $request->file('file')->store('imports');

        ImportClientsJob::dispatch($path);

        return response()->json([
            'status' => 'success',
            'message' => 'CSV import started'
        ]);
    }
}
