<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientService
{
    public function index(Request $request)
    {
        if ($request->boolean('duplicates') && $request->boolean('unique')) {
            return response()->json([
                'message' => 'You cannot request both duplicates and unique at the same time.',
            ], 422);
        }

        $query = Client::query()->orderBy('id', 'desc');

        if ($request->boolean('duplicates')) {
            $query->whereNotNull('duplicate_group_id');
        } elseif ($request->boolean('unique')) {
            $query->whereNull('duplicate_group_id');
        }

        $perPage = (int) $request->get('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    public function export(Request $request)
    {
        if ($request->boolean('duplicates') && $request->boolean('unique')) {
            return response()->json([
                'message' => 'You cannot request both duplicates and unique at the same time.',
            ], 422);
        }

        $query = Client::query()->orderBy('id', 'asc');

        if ($request->boolean('duplicates')) {
            $query->whereNotNull('duplicate_group_id');
        } elseif ($request->boolean('unique')) {
            $query->whereNull('duplicate_group_id');
        }

        $fileName = 'clients_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // same CSV format
            fputcsv($handle, ['company_name', 'email', 'phone_number']);

            $query->chunk(500, function ($clients) use ($handle) {
                foreach ($clients as $client) {
                    fputcsv($handle, [
                        $client->company_name,
                        $client->email,
                        $client->phone_number,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
