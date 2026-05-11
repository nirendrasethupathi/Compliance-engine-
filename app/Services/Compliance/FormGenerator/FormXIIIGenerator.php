<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

class FormXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIII';
    protected string $view = 'compliance.forms.form_xiii_register_of_workmen';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $age = $this->calculateAge($record['date_of_birth'] ?? null) ?? 'NIL';
            $sex = $record['gender'] ?? 'NIL';
            
            $rows[] = [
                'name' => $record['name'] ?? 'NIL',
                'age' => $age,
                'sex' => $sex,
                'father_name' => $record['father_name'] ?? 'NIL',
                'designation' => $record['designation'] ?? 'NIL',
                'permanent_address' => $record['permanent_address'] ?? 'NIL',
                'local_address' => $record['local_address'] ?? 'NIL',
                'joining_date' => $this->formatDate($record['joining_date'] ?? null) ?? 'NIL',
                'termination_date' => $this->formatDate($record['termination_date'] ?? null) ?? 'NIL',
                'termination_reason' => $record['termination_reason'] ?? 'NIL',
                'remarks' => $record['remarks'] ?? 'NIL',
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM XIII - Register of Workmen Employed by Contractor',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'tenant' => [
                    'name' => $rawData['tenant']['name'] ?? 'NIL',
                    'address' => $rawData['tenant']['address'] ?? 'NIL',
                ],
                'branch' => [
                    'name' => $rawData['branch']['name'] ?? 'NIL',
                    'address' => $rawData['branch']['address'] ?? 'NIL',
                ],
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }

    private function calculateAge(?string $dateOfBirth): ?string
    {
        if (!$dateOfBirth) {
            return null;
        }

        try {
            $dob = Carbon::parse($dateOfBirth);
            return (string) $dob->diffInYears(Carbon::now());
        } catch (\Exception $e) {
            return null;
        }
    }

    private function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return null;
        }
    }
}
