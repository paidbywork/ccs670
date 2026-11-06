<?php
require dirname(__DIR__).'/bootstrap/app.php';
use App\Services\DashboardService;
foreach (['studentAssignmentStatusSql','teacherAssignmentStatusSql'] as $method) {
    $sql = DashboardService::$method();
    if (!str_contains($sql,'NOW()') || !str_contains($sql,'due_at')) throw new RuntimeException("Bad status query: $method");
}
if (!str_contains(DashboardService::studentAssignmentStatusSql(),"'submitted'")) throw new RuntimeException('Submission priority missing');
echo "PASS: dashboard status query definitions.\n";
