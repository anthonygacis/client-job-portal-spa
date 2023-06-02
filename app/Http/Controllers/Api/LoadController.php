<?php

namespace App\Http\Controllers\Api;

use App\Class\Calendar;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Load\LoadEmployeesRequest;
use App\Models\Appointment;
use App\Models\Deduction;
use App\Models\PayrollEntry;
use App\Models\WithholdTax;
use Illuminate\Support\Carbon;

class LoadController extends Controller
{
    public function loadEmployees(LoadEmployeesRequest $request)
    {
        $validated = $request->validated();

        $filterDate = $validated['year'] . '-' . str_pad($validated['month'], 2, '0', STR_PAD_LEFT) . '-01';

        $output = [];

        if ($validated["payroll_type"] == "general" || $validated["payroll_type"] == "contractual") {
            $payrollType = $validated['payroll_type'] == 'general' ? 'permanent' : $validated['payroll_type'];
            $appointments = Appointment::with([
                'employeeStatus' => function ($query) use ($payrollType) {
                    $query->where('status_title', 'LIKE', '%' . $payrollType . '%');
                },
                'salaryGrade',
                'position',
                'user'
            ])
                ->whereHas('employeeStatus', function ($query) use ($payrollType) {
                    $query->where('status_title', 'LIKE', '%' . $payrollType . '%');
                })
                ->where('office_id', $validated['office'])
                ->where('is_active', true)
                ->where('is_daily', false)
                ->whereDate('from_date', '<=', Carbon::parse($filterDate))
                ->whereDate('to_date', '>=', Carbon::parse($filterDate))
                ->get();

            foreach ($appointments as $appointment) {
                $temp["appointment_id"] = $appointment->id;
                $temp["user_id"] = $appointment->user->id;
                $temp["full_name"] = $appointment->user->name;
                $temp["position"] = $appointment->position->position_title;
                $temp["emp_status"] = $appointment->is_daily ? 'Contractual (Daily)' : $appointment->employeeStatus->status_title;
                $temp["is_daily"] = $appointment->is_daily;
                $temp["salary"] = $appointment->salaryGrade->amount;
                $temp["raw_salary"] = $appointment->salaryGrade->amount;

                // check if there is a draft payroll
                $start = $validated["year"] . "-" . $validated["month"] . "-01";
                $end = date("Y-m-t", strtotime($start));
                $draftPayroll = PayrollEntry::with("payrollEntryItem", "payrollEntryItem.inclusion", "payrollEntryItem.deduction")
                    ->where("appointment_id", $appointment->id)
                    ->where("user_id", $appointment->user->id)
                    ->where("coverage_start", $start)
                    ->where("coverage_end", $end)
                    ->where("payroll_type", $validated["payroll_type"])
                    ->first();

                if ($draftPayroll) {
                    $temp["draft"] = true;
                    $temp["draft_id"] = $draftPayroll->id;
                    $temp["use_credits"] = true;
                    $temp["total_credits_used"] = $draftPayroll->total_credits_used;
                    $temp["total_absences"] = $draftPayroll->no_of_absences;
                    $temp["leave_without_pay"] = [];
                    // for leave without pay
                    foreach ($draftPayroll->payrollEntryItem as $item) {
                        if ($item->leave_type) {
                            $t["leave_type"] = $item->leave_type;
                            $t["amount"] = $item->amount;
                            $temp["leave_without_pay"][] = $t;
                        }
                    }

                    // for deductions
                    $temp["deductions"] = [];
                    $temp["raw_total_deductions"] = 0;
                    foreach ($draftPayroll->payrollEntryItem as $item) {
                        if ($item->deduction_id) {
                            $t["id"] = $item->deduction->id;
                            $t["name"] = $item->deduction->deduction_name;
                            $t["computed_value"] = $item->amount;
                            $t["is_mandatory"] = $item->deduction->is_mandatory;

                            $temp["raw_total_deductions"] += $item->amount;
                            $temp["deductions"][] = $t;
                        }
                    }

                    // for receivables
                    $temp["receivables"] = [];
                    $temp["raw_total_receivables"] = 0;
                    foreach ($draftPayroll->payrollEntryItem as $item) {
                        if ($item->inclusion_id) {
                            $t["id"] = $item->inclusion->id;
                            $t["name"] = $item->inclusion->inclusion_name;
                            $t["computed_value"] = $item->amount;

                            $temp["raw_total_receivables"] += $item->amount;
                            $temp["receivables"][] = $t;
                        }
                    }
                } else {
                    // load mandatory deductions
                    $temp["deductions"] = [];
                    $temp["total_deductions"] = 0;
                    if ($payrollType == 'permanent') {
                        $deductions = Deduction::where("is_mandatory", true)->where("percentage_amount", ">=", 0)->get();
                        foreach ($deductions as $deduction) {
                            $de_name = strtolower($deduction->deduction_name);
                            $basicSalary = $appointment->salaryGrade?->amount;
                            $deductType = $deduction->mandatory_type;
                            $p = $deduction->percentage_amount / 100;
                            $exact = $deduction->percentage_amount;

                            $d["id"] = $deduction->id;
                            $d["name"] = $deduction->deduction_name;
                            $d["is_mandatory"] = $deduction->is_mandatory;
                            $d["mandatory_type"] = $deduction->mandatory_type;
                            $d["percent"] = $p;
                            $d["exact"] = $exact;

                            if ((str_contains($de_name, "bir") || str_contains($de_name, "tax") || str_contains($de_name, "withheld")) &&
                                $deduction->percentage_amount == 0) { // probably a bir tax withheld type of deduction
                                $months = strtolower($appointment->employeeStatus->status_title) == 'permanent' ? 12 : Calendar::diffInFiscalMonth($appointment->from_date, $appointment->to_date);
                                $annual = $basicSalary * $months;

                                // check from the tax table and compute
                                $taxTable = WithholdTax::with("withholdTaxItem")
                                    ->where("is_active", true)
                                    ->where('effectivity_start', '<=', now()->format('Y-m-d'))
                                    ->where('effectivity_end', '>=', now()->format('Y-m-d'))
                                    ->first();
                                if ($taxTable) {
                                    $taxItem = $taxTable->withholdTaxItem->where('compensation_start', '<=', $annual)->sortByDesc('level')->first();
                                    if ($taxItem) {
                                        $pTax = ($annual - $taxItem->compensation_start + 1) * $taxItem->percentage_tax;
                                        $netTax = $pTax + $taxItem->base_tax;

                                        // divide net tax by total no. of months
                                        $netTax /= $months;

                                        $d["computed_value"] = $netTax;
                                    }
                                } else {
                                    $d["computed_value"] = 0;
                                }
                            } else {
                                $d["computed_value"] = $deductType === 'percentage' ? $basicSalary * $p : $exact;
                            }

                            $temp["total_deductions"] += $d['computed_value'];
                            $temp["deductions"][] = $d;
                        }
                    }
                    $temp["raw_total_deductions"] = $temp["total_deductions"];

                    $temp["draft"] = false;
                    $temp["draft_id"] = null;
                    $temp["use_credits"] = false;
                    $temp["total_credits_used"] = 0;
                    $temp["total_absences"] = 0;
                    $temp["leave_without_pay"] = [];
                    $temp["receivables"] = [];
                    $temp["raw_total_receivables"] = 0;
                }

                $temp["raw_net_home_pay"] = $temp["raw_salary"] - $temp["raw_total_deductions"] + $temp["raw_total_receivables"];

                $output[] = $temp;
            }
            array_multisort(array_column($output, 'full_name'), SORT_ASC, $output);
        } else if ($validated["payroll_type"] == "mid-year" || $validated["payroll_type"] == "year-end") {
            $payrollType = 'permanent';
            $appointments = Appointment::with([
                'employeeStatus' => function ($query) use ($payrollType) {
                    $query->where('status_title', 'LIKE', '%' . $payrollType . '%');
                },
                'salaryGrade',
                'position',
                'user'
            ])
                ->whereHas('employeeStatus', function ($query) use ($payrollType) {
                    $query->where('status_title', 'LIKE', '%' . $payrollType . '%');
                })
                ->where('office_id', $validated['office'])
                ->where('is_active', true)
                ->where('is_daily', false)
                ->whereDate('from_date', '<=', Carbon::parse($filterDate))
                ->whereDate('to_date', '>=', Carbon::parse($filterDate))
                ->get();

            foreach ($appointments as $appointment) {
                $temp["user_id"] = $appointment->user->id;
                $temp["appointment_id"] = $appointment->id;
                $temp["full_name"] = $appointment->user->name;
                $temp["position"] = $appointment->position->position_title;
                $temp["emp_status"] = $appointment->is_daily ? 'Contractual (Daily)' : $appointment->employeeStatus->status_title;
                $temp["is_daily"] = $appointment->is_daily;
                $temp["salary"] = $appointment->salaryGrade->amount;
                $temp["raw_salary"] = $appointment->salaryGrade->amount;

                $temp["deductions"] = [];
                $temp["total_deductions"] = 0;
                $temp["raw_total_deductions"] = 0;
                $temp["total_deductions"] = number_format(0, 2);

                // check if there is a draft payroll
                $start = $validated["year"] . "-" . $validated["month"] . "-01";
                $end = date("Y-m-t", strtotime($start));
                $draft_payroll = PayrollEntry::with("payrollEntryItem")
                    ->where("appointment_id", $appointment->id)
                    ->where("user_id", $appointment->user->id)
                    ->where("coverage_start", $start)
                    ->where("coverage_end", $end)
                    ->where("payroll_type", $validated["payroll_type"])
                    ->first();

                if ($draft_payroll) {
                    $temp["draft"] = true;
                    $temp["draft_id"] = $draft_payroll->id;
                    $temp["use_credits"] = true;
                    $temp["total_credits_used"] = $draft_payroll->total_credits_used;
                    $temp["total_absences"] = $draft_payroll->no_of_absences;
                    $temp["leave_without_pay"] = [];
                } else {
                    $temp["draft"] = false;
                    $temp["draft_id"] = null;
                    $temp["use_credits"] = false;
                    $temp["total_credits_used"] = 0;
                    $temp["total_absences"] = 0;
                    $temp["leave_without_pay"] = [];
                }

                $temp["raw_net_home_pay"] = $temp["raw_salary"] - $temp["raw_total_deductions"];
                $temp["net_home_pay"] = number_format($temp["raw_net_home_pay"], 2);

                $output[] = $temp;
            }
            array_multisort(array_column($output, 'full_name'), SORT_ASC, $output);
        } else if ($validated["payroll_type"] == "daily") {
            $payrollType = 'contractual';
            $appointments = Appointment::with([
                'employeeStatus' => function ($query) use ($payrollType) {
                    $query->where('status_title', 'LIKE', '%' . $payrollType . '%');
                },
                'salaryGrade',
                'position',
                'user'
            ])
                ->whereHas('employeeStatus', function ($query) use ($payrollType) {
                    $query->where('status_title', 'LIKE', '%' . $payrollType . '%');
                })
                ->where('office_id', $validated['office'])
                ->where('is_active', true)
                ->where('is_daily', true)
                ->whereDate('from_date', '<=', Carbon::parse($filterDate))
                ->whereDate('to_date', '>=', Carbon::parse($filterDate))
                ->get();

            foreach ($appointments as $appointment) {
                $temp["user_id"] = $appointment->user->id;
                $temp["appointment_id"] = $appointment->id;
                $temp["full_name"] = $appointment->user->name;
                $temp["position"] = $appointment->position->position_title;
                $temp["emp_status"] = $appointment->is_daily ? 'Contractual (Daily)' : $appointment->employeeStatus->status_title;
                $temp["is_daily"] = $appointment->is_daily;
                $temp["salary"] = $appointment->meta['daily_rate'] ?? 0;
                $temp["raw_salary"] = $appointment->meta['daily_rate'] ?? 0;

                $temp["deductions"] = [];
                $temp["total_deductions"] = 0;
                $temp["raw_total_deductions"] = 0;
                $temp["total_deductions"] = number_format(0, 2);

                // check if there is a draft payroll
                $start = $validated["year"] . "-" . $validated["month"] . "-01";
                $end = date("Y-m-t", strtotime($start));
                $draft_payroll = PayrollEntry::with("payrollEntryItem")
                    ->where("appointment_id", $appointment->id)
                    ->where("user_id", $appointment->user->id)
                    ->where("coverage_start", $start)
                    ->where("coverage_end", $end)
                    ->where("payroll_type", $validated["payroll_type"])
                    ->first();

                if ($draft_payroll) {
                    $temp["draft"] = true;
                    $temp["draft_id"] = $draft_payroll->id;
                    $temp["use_credits"] = true;
                    $temp["total_credits_used"] = $draft_payroll->total_credits_used;
                    $temp["total_absences"] = $draft_payroll->no_of_absences;
                    $temp["leave_without_pay"] = [];
                    $temp["salary"] = $draft_payroll->basic_salary;
                    $temp['meta'] = $draft_payroll->meta;
                } else {
                    $temp["draft"] = false;
                    $temp["draft_id"] = null;
                    $temp["use_credits"] = false;
                    $temp["total_credits_used"] = 0;
                    $temp["total_absences"] = 0;
                    $temp["leave_without_pay"] = [];
                }

                $temp["raw_net_home_pay"] = $temp["salary"] - $temp["raw_total_deductions"];
                $temp["net_home_pay"] = number_format($temp["raw_net_home_pay"], 2);

                $output[] = $temp;
            }
            array_multisort(array_column($output, 'full_name'), SORT_ASC, $output);
        } else if ($validated["payroll_type"] == "extra" || $validated["payroll_type"] == "pei") {
            $appointments = Appointment::with([
                'employeeStatus',
                'salaryGrade',
                'position',
                'user'
            ])
                ->where('office_id', $validated['office'])
                ->where('is_active', true)
                ->whereDate('from_date', '<=', Carbon::parse($filterDate))
                ->whereDate('to_date', '>=', Carbon::parse($filterDate))
                ->get();

            foreach ($appointments as $appointment) {
                $temp["user_id"] = $appointment->user->id;
                $temp["appointment_id"] = $appointment->id;
                $temp["full_name"] = $appointment->user->name;
                $temp["position"] = $appointment->position->position_title;
                $temp["emp_status"] = $appointment->is_daily ? 'Contractual (Daily)' : $appointment->employeeStatus->status_title;
                $temp["is_daily"] = $appointment->is_daily;
                $temp["salary"] = 0;
                $temp["raw_salary"] = 0;

                $temp["deductions"] = [];
                $temp["total_deductions"] = 0;
                $temp["raw_total_deductions"] = 0;
                $temp["total_deductions"] = number_format(0, 2);

                // check if there is a draft payroll
                $start = $validated["year"] . "-" . $validated["month"] . "-01";
                $end = date("Y-m-t", strtotime($start));
                $draft_payroll = PayrollEntry::with("payrollEntryItem")
                    ->where("appointment_id", $appointment->id)
                    ->where("user_id", $appointment->user->id)
                    ->where("coverage_start", $start)
                    ->where("coverage_end", $end)
                    ->where("payroll_type", $validated["payroll_type"])
                    ->first();

                if ($draft_payroll) {
                    $temp["draft"] = true;
                    $temp["draft_id"] = $draft_payroll->id;
                    $temp["use_credits"] = true;
                    $temp["total_credits_used"] = $draft_payroll->total_credits_used;
                    $temp["total_absences"] = $draft_payroll->no_of_absences;
                    $temp["leave_without_pay"] = [];
                    $temp["salary"] = $draft_payroll->basic_salary;
                    $temp["raw_salary"] = $draft_payroll->basic_salary;
                    $temp['meta'] = $draft_payroll->meta;
                } else {
                    $temp["draft"] = false;
                    $temp["draft_id"] = null;
                    $temp["use_credits"] = false;
                    $temp["total_credits_used"] = 0;
                    $temp["total_absences"] = 0;
                    $temp["leave_without_pay"] = [];
                }

                $temp["raw_net_home_pay"] = $temp["raw_salary"] - $temp["raw_total_deductions"];
                $temp["net_home_pay"] = number_format($temp["raw_net_home_pay"], 2);

                $output[] = $temp;
            }
            array_multisort(array_column($output, 'full_name'), SORT_ASC, $output);
        }

        return response()->json($output);
    }
}
