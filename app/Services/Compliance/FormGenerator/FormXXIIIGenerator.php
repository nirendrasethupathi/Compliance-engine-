<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXIII';
    protected string $view = 'compliance.forms.form_xxiii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'name'              => $record['employee_name'] ?? 'NIL',
                'father_name'       => $record['father_name'] ?? 'NIL',
                'sex'               => $record['sex'] ?? 'NIL',
                'designation'       => $record['designation'] ?? 'NIL',
                'overtime_dates'    => $record['overtime_dates'] ?? 'NIL',
                'total_overtime'    => isset($record['total_overtime']) && $record['total_overtime'] > 0
                                        ? number_format((float)$record['total_overtime'], 2)
                                        : 'NIL',
                'normal_rate'       => isset($record['normal_rate']) && $record['normal_rate'] > 0
                                        ? number_format((float)$record['normal_rate'], 2)
                                        : 'NIL',
                'overtime_rate'     => isset($record['overtime_rate']) && $record['overtime_rate'] > 0
                                        ? number_format((float)$record['overtime_rate'], 2)
                                        : 'NIL',
                'overtime_earnings' => isset($record['overtime_earnings']) && $record['overtime_earnings'] > 0
                                        ? number_format((float)$record['overtime_earnings'], 2)
                                        : 'NIL',
                'payment_date'      => $record['payment_date'] ?: 'NIL',
                'remarks'           => $record['remarks'] ?? '',
            ];
        }

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'contractor_name'    => $tenant['name'] ?? 'N/A',
                'work_location'      => $branch['address'] ?? 'N/A',
                'establishment_name' => $branch['name'] ?? 'N/A',
                'principal_employer' => $tenant['name'] ?? 'N/A',
                'month_year'         => $this->formatPeriod($month, $year),
                'tenant'             => $tenant,
                'branch'             => $branch,
            ],
            'rows'   => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
