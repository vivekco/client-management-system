<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Models\ImportSummary;
use Illuminate\Http\Request;
use App\Jobs\ImportClientsJob;

class ImportService
{
    public function import(Request $request)
    {
        $path = $request->file('file')->store('imports');

        ImportClientsJob::dispatch($path);

        return response()->json([
            'status' => 'success',
            'message' => 'CSV import started'
        ]);
    }

    public function summaries(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $summaries = ImportSummary::query()
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($summaries);
    }

    public function logs(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $query = ImportLog::query()->orderBy('id', 'desc');

        if ($request->filled('file_name')) {
            $query->where('file_name', $request->file_name);
        }

        return response()->json($query->paginate($perPage));
    }
}
