<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$b   = DB::table('branches')->first();
$tid = $b->tenant_id;
$bid = $b->id;

// Test 1: raw selectRaw
$row = DB::table('workforce_payroll_entry as pe')
    ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
    ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
    ->where('e.tenant_id', $tid)
    ->where('e.branch_id', $bid)
    ->whereYear('pc.period_from', 2025)
    ->whereMonth('pc.period_from', 12)
    ->selectRaw("e.employee_code, e.name AS employee_name, pe.basic_earned AS basic_earned, pe.gross_salary AS gross_salary, pe.net_salary AS net_salary, COALESCE(pe.pf_employee,0) AS pf_employee")
    ->orderBy('e.employee_code')
    ->first();

echo "Test1 basic_earned: " . $row->basic_earned . " gross: " . $row->gross_salary . PHP_EOL;

// Test 2: via FormBApiService
$svc  = new \App\Services\Compliance\FormApis\FormBApiService();
$data = $svc->fetch($tid, $bid, 12, 2025);
$r    = $data['records'][0];
echo "FormB basic_earned: " . $r['basic_earned'] . " gross: " . $r['gross_salary'] . PHP_EOL;

// Test 3: via EPFInspectionApiService
$svc2  = new \App\Services\Compliance\FormApis\EPFInspectionApiService();
$data2 = $svc2->fetch($tid, $bid, 12, 2025);
$r2    = $data2['records'][0];
echo "EPF basic_earned: " . $r2['basic_earned'] . " gross: " . $r2['gross_salary'] . PHP_EOL;

// Test 4: via FormXVIIApiService
$svc3  = new \App\Services\Compliance\FormApis\FormXVIIApiService();
$data3 = $svc3->fetch($tid, $bid, 12, 2025);
$r3    = $data3['records'][0];
echo "XVII basic_earned: " . $r3['basic_earned'] . " uan: " . $r3['uan'] . PHP_EOL;
