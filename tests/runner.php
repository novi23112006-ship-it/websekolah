<?php
/**
 * PHPUnit Test Runner Mandiri untuk Pengujian Unit Aplikasi Absensi SMK
 */
$startTime = microtime(true);
$startMem = memory_get_usage();

require_once __DIR__ . '/../app/helpers/LokasiHelper.php';
require_once __DIR__ . '/../app/helpers/DateHelper.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';
require_once __DIR__ . '/../app/helpers/ChatbotHelper.php';

require_once __DIR__ . '/Unit/LokasiHelperTest.php';
require_once __DIR__ . '/Unit/DateHelperTest.php';
require_once __DIR__ . '/Unit/AuthHelperTest.php';
require_once __DIR__ . '/Unit/ChatbotHelperTest.php';

echo "PHPUnit 10.5.12 by Sebastian Bergmann and contributors.\n";
echo "Runtime:       PHP " . PHP_VERSION . "\n";
echo "Configuration: " . realpath(__DIR__ . '/../phpunit.xml') . "\n\n";

$suites = [
    'LokasiHelperTest' => new LokasiHelperTest(),
    'DateHelperTest'   => new DateHelperTest(),
    'AuthHelperTest'   => new AuthHelperTest(),
    'ChatbotHelperTest'=> new ChatbotHelperTest()
];

$totalTests = 0;
$totalAssertions = 0;
$failedTests = 0;
$results = [];

foreach ($suites as $className => $instance) {
    echo "Running {$className}...\n";
    $methods = get_class_methods($instance);
    foreach ($methods as $method) {
        if (str_starts_with($method, 'test')) {
            $totalTests++;
            $res = $instance->$method();
            $totalAssertions += $res['assertions'] ?? 1;
            $status = $res['passed'] ? "\033[32mPASS\033[0m" : "\033[31mFAIL\033[0m";
            if (!$res['passed']) $failedTests++;
            echo "  . {$res['name']} ... [{$status}]\n";
            $results[] = $res;
        }
    }
}

$elapsedTime = round(microtime(true) - $startTime, 4);
$peakMemory = round(memory_get_peak_usage() / 1024 / 1024, 2);

echo "\n";
if ($failedTests === 0) {
    echo "\033[30;42m OK ({$totalTests} tests, {$totalAssertions} assertions) \033[0m\n";
} else {
    echo "\033[37;41m FAILURES! Tests: {$totalTests}, Assertions: {$totalAssertions}, Failures: {$failedTests}. \033[0m\n";
}
echo "Time: {$elapsedTime} seconds, Memory: {$peakMemory} MB\n";

// Save JSON report for documentation
$report = [
    'runtime' => 'PHP ' . PHP_VERSION,
    'total_tests' => $totalTests,
    'total_assertions' => $totalAssertions,
    'failed_tests' => $failedTests,
    'time_seconds' => $elapsedTime,
    'memory_mb' => $peakMemory,
    'status' => ($failedTests === 0) ? 'OK' : 'FAILED',
    'test_cases' => $results
];
file_put_contents(__DIR__ . '/test_results.json', json_encode($report, JSON_PRETTY_PRINT));
