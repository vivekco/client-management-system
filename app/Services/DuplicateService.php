<?php

namespace App\Services;

use App\Models\DuplicateGroup;
use App\Models\Client;
use Illuminate\Http\Request;

class DuplicateService
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $groups = DuplicateGroup::query()
            ->withCount('clients')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($groups);
    }

    public function show($id)
    {
        $group = DuplicateGroup::find($id);

        if (!$group) {
            return response()->json([
                'message' => 'Duplicate group not found.'
            ], 404);
        }

        $clients = Client::where('duplicate_group_id', $group->id)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'group' => $group,
            'clients' => $clients,
        ]);
    }
    public function generateSignature($company, $email, $phone)
    {
        return md5(
            strtolower(trim($company)) .
                strtolower(trim($email)) .
                trim($phone)
        );
    }
}
