<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(Request $request)
{
    $query = Client::query();

    // Filter duplicates
    if ($request->boolean('duplicates')) {
        $query->whereNotNull('duplicate_group_id');
    } 
    // Filter unique records
    elseif ($request->boolean('unique')) {
        $query->whereNull('duplicate_group_id');
    }

    // Pagination size (default 20)
    $perPage = $request->get('per_page', 20);

    return response()->json(
        $query->paginate($perPage)
    );
}
}
