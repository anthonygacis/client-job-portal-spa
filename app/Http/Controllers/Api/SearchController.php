<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Search\SearchEmployeeRequest;
use App\Models\User;

class SearchController extends Controller
{
    public function employees(SearchEmployeeRequest $request)
    {
        $validated = $request->validated();
        $query = '%' . $validated['q'] . '%';
        $users = User::where('name', 'LIKE', $query)
            ->orWhere('username', 'LIKE', $query)
            ->get()
            ->map(function ($item) {
                $item->text = $item->name;
                return $item;
            });

        return response()->json($users);
    }
}
