<?php

namespace App\Http\Controllers\Api;

use App\Class\Constant;
use App\Http\Controllers\Controller;
use App\Models\Deduction;
use App\Models\EmployeeStatus;
use App\Models\Inclusion;
use App\Models\Office;
use App\Models\Position;
use App\Models\SalaryGrade;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class GeneralController extends Controller
{
    public function load(Request $request)
    {
        $data = [];
        if ($request->has('items')) {
            $splits = explode(',', $request->get('items'));
            if (in_array('office', $splits)) {
                $data['office'] = Office::all();
            }
            if (in_array('position', $splits)) {
                $data['position'] = Position::all();
            }
            if (in_array('emp-status', $splits)) {
                $data['emp_status'] = EmployeeStatus::all();
            }
            if (in_array('salary-grade', $splits)) {
                $data['salary_grade'] = SalaryGrade::all();
            }
            if (in_array('deductions', $splits)) {
                $data['deductions'] = Deduction::where('is_mandatory', false)
                    ->get();
            }
            if (in_array('deductions-mandatory', $splits)) {
                $data['deductions_mandatory'] = Deduction::where('is_mandatory', true)
                    ->get();
            }
            if (in_array('deductions-all', $splits)) {
                $data['deductions_all'] = Deduction::all();
            }
            if (in_array('inclusions', $splits)) {
                $data['inclusions'] = Inclusion::all();
            }
            if (in_array('user', $splits)) {
                $data['users'] = User::all();
            }
            if (in_array('permissions', $splits)) {
                $data['permissions'] = Constant::getPermissionDescriptions();
            }
            if (in_array('features', $splits)) {
                $data['features'] = Constant::getPermissions();
            }
            if (in_array('roles', $splits)) {
                $data['roles'] = Role::with('permissions')->get();
            }
        }

        return response()->json($data);
    }
}
