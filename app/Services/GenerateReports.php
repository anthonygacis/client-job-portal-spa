<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Office;
use App\Models\PayrollEntry;
use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class GenerateReports
{
    public static function payrollRecords($payload)
    {
        $emps = Appointment::with("user", "salaryGrade", "position")->where("office_id", $payload['office_id'])->get();

        $start = $payload['year'] . "-" . $payload['month'] . "-01";
        $end = date("Y-m-t", strtotime($start));

        $office = Office::find($payload['office_id']);

        $output["data"] = [];

        if ($office) {
            $output["office"] = $office->office_name;
        } else {
            $output["office"] = "";
        }

        $period_from = date("01-M-y", strtotime($start));
        $period_to = date("d-M-y", strtotime($end));

        $output["overall_basic_salary"] = 0;
        $output["overall_receivables"] = 0;
        $output["overall_deductions"] = 0;
        $output["overall_home_pay"] = 0;
        $output["period_cover"] = $period_from . " to " . $period_to;
        $output["inclusion_ids"] = [];
        $output["deduction_ids"] = [];
        foreach ($emps as $emp) {
            $payroll = PayrollEntry::where("appointment_id", $emp->id)
                ->where("payroll_type", $payload['payroll_type'])
                ->where("coverage_start", $start)
                ->where("coverage_end", $end)->first();

            if ($payroll) {
                $temp["full_name"] = $emp->user->name;
                $temp["emp_id"] = $emp->user->userPrimary->agency_emp_no;
                $temp["position"] = $emp->position->position_title;
                $temp["basic_salary"] = number_format($emp->salaryGrade->amount, 2);
                $temp["coverage"] = $payroll->payroll_type;
                $temp["start"] = $start;
                $temp["end"] = $end;
                $temp["total_receivables"] = number_format($payroll->total_receivables + $emp->salaryGrade->amount, 2);
                $temp["total_deductions"] = number_format($payroll->total_deductions, 2);
                $temp["inclusions"] = [];
                $temp["deductions"] = [];
                foreach ($payroll->payrollEntryItem as $payrollEntry) {
                    if ($payrollEntry->amount != 0) {
                        if ($payrollEntry->inclusion_id) {
                            $inclusion = $payrollEntry->inclusion;
                            $temp["inclusions"][] = [
                                "id" => $payrollEntry->inclusion_id,
                                "name" => $inclusion->inclusion_name,
                                "amount" => number_format($payrollEntry->amount, 2),
                            ];
                            if (!in_array($payrollEntry->inclusion_id, array_column($output["inclusion_ids"], 'id'))) {
                                $output["inclusion_ids"][] = [
                                    "id" => $payrollEntry->inclusion_id,
                                    "name" => $inclusion->inclusion_name,
                                ];
                            }
                        }
                        if ($payrollEntry->deduction_id) {
                            $deduction = $payrollEntry->deduction;
                            $temp["deductions"][] = [
                                "id" => $payrollEntry->deduction_id,
                                "name" => $deduction->deduction_name,
                                "amount" => number_format($payrollEntry->amount, 2),
                            ];
                            if (!in_array($payrollEntry->deduction_id, array_column($output["deduction_ids"], 'id'))) {
                                $output["deduction_ids"][] = [
                                    "id" => $payrollEntry->deduction_id,
                                    "name" => $deduction->deduction_name,
                                ];
                            }
                        }
                    }
                }
                $temp["net_home_pay"] = number_format($payroll->net_home_pay, 2);

                $output["overall_basic_salary"] += $emp->salaryGrade->amount;
                $output["overall_receivables"] += $payroll->total_receivables + $emp->salaryGrade->amount;
                $output["overall_deductions"] += $payroll->total_deductions;
                $output["overall_home_pay"] += $payroll->net_home_pay;

                $output["data"][] = $temp;
            }
        }

        $output["overall_basic_salary"] = number_format($output["overall_basic_salary"], 2);
        $output["overall_receivables"] = number_format($output["overall_receivables"], 2);
        $output["overall_deductions"] = number_format($output["overall_deductions"], 2);
        $output["overall_home_pay"] = number_format($output["overall_home_pay"], 2);

        // load settings
        $output["signatory"] = "";
        $setting = Setting::where("name", "signatories")->first();
        if ($setting) {
            $output["signatory"] = $setting->value;
        } else {
            $output["signatory"] = "";
        }

        $snappy = App::make('snappy.pdf.wrapper');
        $html = View::make('reports-template.payroll-records-template')->with(compact('output'));
        $snappy->loadHTML($html)
            ->setPaper('legal')
            ->setOrientation('Landscape')
            ->setOption('title', 'Payroll Records')
            ->setOption('margin-top', 3)
            ->setOption('margin-bottom', 3)
            ->setOption('margin-left', 3)
            ->setOption('margin-right', 3)
            ->setOption('dpi', 200)
            ->setOption('print-media-type', true)
            ->setOption('enable-local-file-access', true);
//            ->setOption('header-html', dirname(__FILE__) . '\..\resources\views\header.html');
        // return $snappy->inline();
//        $snappy->save('benchmark-snappy.pdf', true);
        return $snappy->inline();
    }

    public static function payslips($payload)
    {
        $emps = Appointment::with("user", "user.leaveCredit", "salaryGrade", "position")
            ->where("office_id", $payload['office_id'])->get();

        $start = $payload['year'] . "-" . str_pad($payload['month'], 2, '0', STR_PAD_LEFT) . "-01";
        $end = date("Y-m-t", strtotime($start));

        $output["data"] = [];

        $office = Office::find($payload['office_id']);
        $output['office'] = $office->office_name ?? '';
        foreach ($emps as $emp) {
            $payroll = PayrollEntry::with("payrollEntryItem", "payrollEntryItem.inclusion", "payrollEntryItem.deduction")
                ->where("appointment_id", $emp->id)
                ->where("payroll_type", $payload['payroll_type'])
                ->where("coverage_start", $start)
                ->where("coverage_end", $end)->first();

            if ($payroll) {
                $temp["full_name"] = $emp->user->name;
                $temp["emp_id"] = $emp->user->userPrimary->agency_emp_no;
                $temp["position"] = $emp->position->position_title;
                $temp["basic_salary"] = number_format($emp->salaryGrade->amount, 2);
                $temp["coverage"] = $payroll->payroll_type;
                $temp["start"] = $start;
                $temp["end"] = $end;
                $temp["total_receivables"] = number_format($payroll->total_receivables, 2);
                $temp["total_deductions"] = number_format($payroll->total_deductions, 2);
                $temp["net_home_pay"] = number_format($payroll->net_home_pay, 2);
                $temp["leave_credit_sick"] = $emp->user->leaveCredit->sick_leave;
                $temp["leave_credit_vacation"] = $emp->user->leaveCredit->vacation_leave;
                $temp["inclusions"] = [];
                $temp["deductions"] = [];

                // for inclusions
                foreach ($payroll->payrollEntryItem as $pei) {
                    if ($pei->inclusion_id) {
                        $t["name"] = $pei->inclusion->inclusion_name;
                        $t["amount"] = number_format($pei->amount, 2);
                        $temp["inclusions"][] = $t;
                    }
                }

                // for deductions
                foreach ($payroll->payrollEntryItem as $pei) {
                    if ($pei->deduction_id) {
                        if ($pei->amount != 0) {
                            $t["name"] = $pei->deduction->deduction_name;
                            $t["amount"] = number_format($pei->amount, 2);
                            $temp["deductions"][] = $t;
                        }
                    }
                }

                $output["data"][] = $temp;
            }
        }

        // load settings
        $setting = Setting::where("name", "signatories")->first();
        if ($setting) {
            $output["signatory"] = $setting->value;
        } else {
            $output["signatory"] = "";
        }

        $snappy = App::make('snappy.pdf.wrapper');
        $html = View::make('reports-template.payroll-payslip-template')
            ->with(compact('output'));
        $snappy->loadHTML($html)
            ->setOrientation('Portrait')
            ->setOption('page-height', 160)
            ->setOption('page-width', 100)
            ->setOption('title', 'Payroll Slips')
            ->setOption('margin-top', 3)
            ->setOption('margin-bottom', 3)
            ->setOption('margin-left', 3)
            ->setOption('margin-right', 3)
            ->setOption('header-spacing', 10)
            ->setOption('dpi', 200)
            ->setOption('print-media-type', true)
            ->setOption('enable-local-file-access', true);
        return $snappy->inline();
    }
}
