<?php

namespace App\Http\Controllers\Api;

use App\Class\GenerateDatatableData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DatatableLoadRequest;
use App\Models\Fund;
use App\Models\SalaryGrade;
use Illuminate\Http\Request;

class DataTableController extends Controller
{
    public function index(DatatableLoadRequest $request)
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'];
        $field = $validated['order_field'];
        $sort = $validated['order_type'];
        $currentPage = $validated['current_page'];
        $searchText = $validated['search'];

        $data = [];
        $meta = [];
        if ($request->has('model')) {
            if ($request->get('model') == 'users') {
                $data = GenerateDatatableData::users($currentPage, $perPage, $searchText, $field, $sort);
            }
        }

        $otherData = [
            'field' => $field,
            'order' => $sort,
            'meta' => $meta
        ];

        return response()->json($data + $otherData);
    }
}
