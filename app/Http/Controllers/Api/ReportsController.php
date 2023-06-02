<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GenerateReports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $data = Crypt::decrypt($request->get('payload'));
        if ($data['name'] == 'records') {
            return GenerateReports::payrollRecords($data['payload']);
        } elseif ($data['name'] == 'payslip') {
            return GenerateReports::payslips($data['payload']);
        }

        return response()->json($data);
    }
}
