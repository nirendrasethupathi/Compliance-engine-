<?php

namespace App\Services\Compliance;

class CsvColumnMapper
{
    /**
     * Canonical field → accepted aliases (all lowercase, underscored).
     * The canonical key is always the first alias tried.
     */
    private const ALIASES = [
        'employees' => [
            // ── Required ──────────────────────────────────────────────────────
            'employee_code' => ['employee_code', 'emp_code', 'empcode', 'code', 'emp_id', 'employee_id', 'staff_code'],
            'name'          => ['name', 'employee_name', 'emp_name', 'full_name', 'worker_name'],
            // ── Optional ─────────────────────────────────────────────────────
            'father_name'      => ['father_name', 'fathers_name', 'father', 'guardian_name'],
            'gender'           => ['gender', 'sex'],
            'date_of_birth'    => ['date_of_birth', 'dob', 'birth_date', 'birthdate'],
            'designation'      => ['designation', 'post', 'position', 'job_title', 'role'],
            'department'       => ['department', 'dept', 'division', 'section'],
            'mobile'           => ['mobile', 'mobile_no', 'phone', 'contact', 'phone_number', 'mobile_number'],
            'email'            => ['email', 'email_id', 'email_address'],
            'permanent_address'=> ['permanent_address', 'address', 'addr', 'residence'],
            'pf_number'        => ['pf_number', 'uan', 'uan_number', 'pf_no', 'epf_no'],
            'esi_number'       => ['esi_number', 'esic_ip', 'esi_ip', 'esi_no', 'esic_no'],
            'pan'              => ['pan', 'pan_number', 'pan_no'],
            'aadhaar'          => ['aadhaar', 'aadhar', 'aadhaar_number', 'aadhar_no'],
            'bank_account'     => ['bank_account', 'account_number', 'bank_acc', 'acc_no'],
            'bank_name'        => ['bank_name', 'bank'],
            'ifsc'             => ['ifsc', 'ifsc_code', 'ifsc_no'],
            'date_of_joining'  => ['date_of_joining', 'joining_date', 'doj', 'join_date'],
            'marital_status'   => ['marital_status', 'marital', 'marriage_status'],
            'nationality'      => ['nationality', 'nation'],
            'skill_type'       => ['skill_type', 'skill', 'category', 'worker_category'],
            'basic_salary'     => ['basic_salary', 'basic', 'salary', 'basic_wage'],
        ],

        'payroll' => [
            // ── Required ──────────────────────────────────────────────────────
            'employee_code' => ['employee_code', 'emp_code', 'empcode', 'code', 'emp_id'],
            'gross_salary'  => ['gross_salary', 'gross', 'gross_wage', 'gross_wages', 'total_earnings', 'gross_pay'],
            'net_salary'    => ['net_salary', 'net', 'net_pay', 'net_payment', 'take_home', 'net_wages'],
            // ── Optional ─────────────────────────────────────────────────────
            'basic_earned'      => ['basic_earned', 'basic_salary', 'basic', 'basic_wage', 'basic_wages'],
            'da_earned'         => ['da_earned', 'da', 'dearness_allowance'],
            'hra_earned'        => ['hra_earned', 'hra', 'house_rent_allowance'],
            'overtime_wages'    => ['overtime_wages', 'overtime', 'ot_wages', 'ot_amount', 'overtime_amount'],
            'other_allowances'  => ['other_allowances', 'allowance', 'allowances', 'bonus', 'special_allowance'],
            'total_days_worked' => ['total_days_worked', 'payable_days', 'working_days', 'days_worked', 'paid_days'],
            'unpaid_leave_days' => ['unpaid_leave_days', 'absent', 'absent_days', 'loss_of_pay', 'lop'],
            'pf_employee'       => ['pf_employee', 'pf', 'epf', 'pf_deduction'],
            'esi_employee'      => ['esi_employee', 'esi', 'esic', 'esi_deduction'],
            'professional_tax'  => ['professional_tax', 'pt', 'prof_tax', 'p_tax'],
            'lwf'               => ['lwf', 'labour_welfare_fund', 'lw_fund'],
            'other_deductions'  => ['other_deductions', 'deductions', 'other_deduct', 'misc_deduction'],
            'total_deductions'  => ['total_deductions', 'total_deduction', 'deduction_total'],
            'overtime_hours'    => ['overtime_hours', 'ot_hours', 'overtime_hrs'],
            'payment_date'      => ['payment_date', 'pay_date', 'salary_date'],
            'payment_mode'      => ['payment_mode', 'pay_mode', 'mode_of_payment'],
        ],

        'attendance' => [
            // ── Required ──────────────────────────────────────────────────────
            'employee_code' => ['employee_code', 'emp_code', 'empcode', 'code', 'emp_id'],
            'working_days'  => ['working_days', 'total_days', 'days_worked', 'total_working_days'],
            // ── Optional ─────────────────────────────────────────────────────
            'employee_name'    => ['employee_name', 'name', 'emp_name', 'worker_name'],
            'designation'      => ['designation', 'post', 'position'],
            'present_days'     => ['present_days', 'present', 'days_present', 'attended_days'],
            'absent_days'      => ['absent_days', 'absent', 'days_absent', 'loss_of_pay', 'lop'],
            'weekly_off'       => ['weekly_off', 'weekly_offs', 'week_off', 'wo'],
            'paid_leave'       => ['paid_leave', 'pl', 'earned_leave', 'el'],
            'paid_holidays'    => ['paid_holidays', 'holidays', 'national_holidays', 'nh'],
            'overtime_hours'   => ['overtime_hours', 'ot_hours', 'overtime', 'ot'],
            'shift'            => ['shift', 'shift_name', 'shift_type'],
            'attendance_status'=> ['attendance_status', 'status'],
            'attendance_date'  => ['attendance_date', 'date', 'month_date'],
        ],
    ];

    /**
     * Build a reverse lookup: normalised_alias → canonical_field for a given type.
     */
    public static function buildLookup(string $type): array
    {
        $lookup = [];
        foreach (self::ALIASES[$type] ?? [] as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                $lookup[$alias] = $canonical;
            }
        }
        return $lookup;
    }

    /**
     * Map raw CSV headers to canonical field names.
     * Returns [canonical => csvIndex] for recognised columns.
     * Unknown columns are collected in $skipped.
     */
    public static function mapHeaders(array $rawHeaders, string $type, array &$skipped = []): array
    {
        $lookup  = self::buildLookup($type);
        $mapping = []; // canonical => index

        foreach ($rawHeaders as $index => $raw) {
            $normalised = strtolower(trim(preg_replace('/[\s\-\/]+/', '_', $raw)));
            $normalised = preg_replace('/[^a-z0-9_]/', '', $normalised);

            if (isset($lookup[$normalised])) {
                $canonical = $lookup[$normalised];
                // First occurrence wins (handles duplicate-ish headers)
                if (!isset($mapping[$canonical])) {
                    $mapping[$canonical] = $index;
                }
            } else {
                $skipped[] = $raw;
            }
        }

        return $mapping;
    }

    /**
     * Extract a canonical row from raw CSV data using the header mapping.
     */
    public static function extractRow(array $rawData, array $headerMapping): array
    {
        $row = [];
        foreach ($headerMapping as $canonical => $index) {
            $row[$canonical] = isset($rawData[$index]) ? trim($rawData[$index]) : '';
        }
        return $row;
    }

    public static function requiredFields(string $type): array
    {
        return match ($type) {
            'employees'  => ['employee_code', 'name', 'father_name', 'date_of_birth', 'gender', 'mobile', 'pf_number'],
            'payroll'    => ['employee_code', 'gross_salary', 'net_salary'],
            'attendance' => ['employee_code', 'working_days'],
            default      => [],
        };
    }

    public static function knownFields(string $type): array
    {
        return array_keys(self::ALIASES[$type] ?? []);
    }
}
