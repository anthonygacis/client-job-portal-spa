<?php

namespace App\Class;

use App\Models\Appointment;
use App\Models\Deduction;
use App\Models\EmployeeStatus;
use App\Models\Fund;
use App\Models\Inclusion;
use App\Models\Office;
use App\Models\Position;
use App\Models\SalaryGrade;
use App\Models\User;
use App\Models\WithholdTax;

class GenerateDatatableData
{
    public static function users(int $currentPage, int $perPage, string|null $searchText, string $field = 'created_at', string $sort = 'asc')
    {
        return User::when($searchText, function ($query) use ($searchText) {
            $q = "%$searchText%";
            $query->where('name', 'LIKE', $q);
        })
            ->orderBy($field, $sort)
            ->paginate($perPage, ['*'], 'page', $currentPage)
            ->through(function ($item) {
                $roles = $item->getRoleNames()->join(', ');
                $item->roles_text = $roles === '' ? 'No roles' : $roles;
                return $item;
            })
            ->toArray();
    }
}
