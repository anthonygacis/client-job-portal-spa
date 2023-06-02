<?php

namespace App\Class;

use Illuminate\Support\Carbon;

class Calendar
{
    public static function diffInFiscalMonth($start, $end)
    {
        if (!$start instanceof Carbon) {
            $from = Carbon::createFromFormat('Y-m-d', $start);
        } else {
            $from = $start;
        }
        if (!$end instanceof Carbon) {
            $to = Carbon::createFromFormat('Y-m-d', $end);
        } else {
            $to = $end;
        }

        $from_year = $from->year;
        $to_year = $to->year;

        $from_month = $from->month;
        $to_month = $to->month;

        $count = 0;
        // TODO refine this month difference for any test cases
        if ($from_year > $to_year) {
            $count = -1; // invalid year range
        } else {
            if ($from->day == 1) { // count for 1 month is the end of the month
                if ($from_year == $to_year) {
                    if ($from_month > $to_month) {
                        $count = -1; // invalid month range
                    } else {
                        $count = ($to_month - $from_month) + 1;
                    }
                } else {
                    $count = (13 - $from_month); // including the month itself
                }
            } else { // count for 1 month is the next following month
                if ($from_year == $to_year) {
                    if ($from_month > $to_month) {
                        $count = -1; // invalid month range
                    } else {
                        $count = ($to_month - $from_month);
                    }
                } else {
                    $count = (12 - $from_month);
                }
            }
        }
//
//        $diff_in_months = $to->diffInMonths($from);
//        $diff_in_days = $to->diffInDays($from);
//
//        $d = cal_days_in_month(CAL_GREGORIAN,2,2021);

        return $count;
    }
}
