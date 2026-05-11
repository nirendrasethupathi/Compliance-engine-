<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('pe.tenant_id', $tenantId)
            ->where('pe.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->where('pe.overtime_hours', '>', 0)
            ->select([
                'e.name as employee_name',
                'e.father_name',
                'e.gender as sex',
                'e.designation',
                DB::raw('CONCAT(DATE_FORMAT(pc.period_from, "%d/%m/%Y"), " - ", DATE_FORMAT(pc.period_to, "%d/%m/%Y")) as overtime_dates'),
                'pe.overtime_hours as total_overtime',
                DB::raw('CASE WHEN pe.total_days_worked > 0 THEN ROUND(pe.basic_earned / pe.total_days_worked, 2) ELSE 0 END as normal_rate'),
                DB::raw('CASE WHEN pe.total_days_worked > 0 THEN ROUND((pe.basic_earned / pe.total_days_worked) * 2, 2) ELSE 0 END as overtime_rate'),
                'pe.overtime_wages as overtime_earnings',
                DB::raw('COALESCE(DATE_FORMAT(pe.payment_date, "%d/%m/%Y"), "") as payment_date'),
                DB::raw('"" as remarks'),
            ])
            ->orderBy('e.name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
