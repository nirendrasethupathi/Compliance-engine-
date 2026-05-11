# FORM XIII – Complete Workflow Analysis & Fix

## CURRENT WORKFLOW ANALYSIS

### 1. DATA FLOW TRACE

```
Route (ComplianceOrchestratorController::run)
  ↓
ComplianceOrchestrator::execute()
  ↓
FormXIIIApiService::fetch()
  ├─ Queries: contract_labour_deployment + workforce_employee
  ├─ Returns: ['records' => [...], 'meta' => [...], 'tenant' => [...], 'branch' => [...]]
  ↓
FormXIIIGenerator::generate()
  ├─ Processes rawData['records']
  ├─ Maps to: ['header' => [...], 'rows' => [...], 'is_nil' => bool]
  ↓
Blade Template (form_xiii_register_of_workmen.blade.php)
  ├─ Receives: $header, $rows, $entries, $cards, $slips, $totals, $is_nil
  ├─ Renders: Table with employee rows
  ↓
PDF Generation (DomPDF)
  └─ Output: PDF file
```

---

## IDENTIFIED ISSUES

### Issue 1: Data Structure Mismatch
**Problem:** Generator returns `['header' => [...], 'rows' => [...]]` but blade expects different structure.

**Current Generator Output:**
```php
[
    'header' => [
        'form_title' => '...',
        'period' => '...',
        'branch' => [...],
        'tenant' => [...]
    ],
    'contractor_name' => '...',
    'establishment_name' => '...',
    'work_nature' => '...',
    'work_location' => '...',
    'principal_employer' => '...',
    'rows' => [...],
    'is_nil' => bool
]
```

**Blade Expects:**
```php
$header = [
    'tenant' => ['name' => '...', 'address' => '...'],
    'branch' => ['name' => '...', 'address' => '...']
]
$rows = [...]
```

### Issue 2: Header Data Not Properly Mapped
**Problem:** Blade uses `data_get($header, 'tenant.name')` but generator passes `$rawData['tenant']` which has different keys.

**Current Keys in Generator:**
- `$rawData['tenant']['establishment_name']` → Should be `name`
- `$rawData['tenant']['name']` → Should be `principal_employer_name`
- `$rawData['branch']['name']` → Should be `establishment_name`

### Issue 3: Empty Values Not Showing NIL
**Problem:** Blade template shows empty strings instead of "NIL" for missing values.

**Current Blade:**
```php
{{ $row['name'] ?? '' }}  // Shows empty if null
```

**Should Be:**
```php
{{ $row['name'] ?? 'NIL' }}  // Shows NIL if null
```

### Issue 4: Age/Sex Formatting Issue
**Problem:** Age calculation and sex formatting not consistent.

**Current:**
```php
{{ $row['age'] ?? '' }}{{ !empty($row['sex']) ? ' / ' . $row['sex'] : '' }}
```

**Should Handle:**
- Age as number or "NIL"
- Sex as M/F or "NIL"
- Format as "25 / M" or "NIL / NIL"

---

## REQUIRED FIXES

### Fix 1: Update FormXIIIGenerator

**File:** `app/Services/Compliance/FormGenerator/FormXIIIGenerator.php`

**Changes:**
- Properly map header data to match blade expectations
- Ensure all row values have NIL fallback
- Fix age/sex formatting

```php
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
```

### Fix 2: Update Blade Template

**File:** `resources/views/compliance/forms/form_xiii_register_of_workmen.blade.php`

**Changes:**
- Use proper data structure from generator
- Show NIL for all empty values
- Fix age/sex display format

```php
<td class="col-1">{{ $index + 1 }}</td>
<td class="col-2">{{ $row['name'] ?? 'NIL' }}</td>
<td class="col-3">{{ $row['age'] ?? 'NIL' }} / {{ $row['sex'] ?? 'NIL' }}</td>
<td class="col-4">{{ $row['father_name'] ?? 'NIL' }}</td>
<td class="col-5">{{ $row['designation'] ?? 'NIL' }}</td>
<td class="col-6">{{ $row['permanent_address'] ?? 'NIL' }}</td>
<td class="col-7">{{ $row['local_address'] ?? 'NIL' }}</td>
<td class="col-8">{{ $row['joining_date'] ?? 'NIL' }}</td>
<td class="col-9"></td>
<td class="col-10">{{ $row['termination_date'] ?? 'NIL' }}</td>
<td class="col-11">{{ $row['termination_reason'] ?? 'NIL' }}</td>
<td class="col-12">{{ $row['remarks'] ?? 'NIL' }}</td>
```

### Fix 3: Verify API Service

**File:** `app/Services/Compliance/FormApis/FormXIIIApiService.php`

**Status:** ✅ CORRECT - Already returns proper structure with tenant/branch details

---

## VERIFICATION CHECKLIST

- [ ] FormXIIIApiService fetches employee records correctly
- [ ] FormXIIIGenerator maps data to proper structure
- [ ] Blade template receives correct $header and $rows
- [ ] All empty values display as "NIL"
- [ ] Age/sex formatted as "25 / M"
- [ ] Empty state shows: empty row + NIL row
- [ ] PDF renders correctly
- [ ] No clipped rows or missing borders
- [ ] Table alignment proper
- [ ] Government styling maintained

---

## TESTING COMMANDS

```bash
# Test API Service
php artisan tinker
>>> $service = app(\App\Services\Compliance\FormApis\FormXIIIApiService::class);
>>> $data = $service->fetch(1, 1, 1, 2024);
>>> dd($data);

# Test Generator
>>> $generator = app(\App\Services\Compliance\FormGenerator\FormXIIIGenerator::class);
>>> $formData = $generator->generate($data);
>>> dd($formData);

# Test Full Execution
>>> $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
>>> $result = $orchestrator->execute(1, 1, 1, 2024, 'FORM_XIII', 'preview');
>>> dd($result);
```

---

## EXPECTED RESULT

✅ Employee records dynamically fetched from database
✅ All columns populate with data or "NIL"
✅ Age/sex formatted correctly
✅ Empty state handled with empty row + NIL row
✅ Table alignment and borders correct
✅ Government layout preserved
✅ PDF renders without issues
✅ Dynamic rows generated properly
✅ Contractor and establishment details populated

