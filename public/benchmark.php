<?php

/*
 * |--------------------------------------------------------------------------
 * | Rift Framework Performance Benchmark
 * |--------------------------------------------------------------------------
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Теплый запуск для инициализации всего
new App\Benchmark\OperationOutcome\OperationService();
new App\Benchmark\Exceptions\ExceptionService();

function run_benchmark(string $name, callable $test, int $iterations = 10000): array {
    // Разогрев
    for ($i = 0; $i < 100; $i++) {
        $test();
    }
    
    // Чистые замеры
    $start = hrtime(true);
    $memStart = memory_get_usage();
    
    for ($i = 0; $i < $iterations; $i++) {
        $test();
    }
    
    return [
        'time' => (hrtime(true) - $start) / 1e+6, // наносекунды -> миллисекунды
        'memory' => memory_get_usage() - $memStart
    ];
}

function format_results(array $results): string {
    $output = "┌──────────────────────┬──────────────┬──────────────┬──────────────┐\n";
    $output .= "│ Test Case            │ Time (ms)    │ Memory (KB)  │ Winner       │\n";
    $output .= "├──────────────────────┼──────────────┼──────────────┼──────────────┤\n";
    
    foreach ($results as $name => $data) {
        $opTime = number_format($data['op']['time'], 2);
        $exTime = number_format($data['ex']['time'], 2);
        $opMem = number_format($data['op']['memory'] / 1024, 2);
        $exMem = number_format($data['ex']['memory'] / 1024, 2);
        
        $timeWinner = $data['op']['time'] < $data['ex']['time'] ? 'OP' : 'EX';
        $memWinner = $data['op']['memory'] < $data['ex']['memory'] ? 'OP' : 'EX';
        
        $output .= sprintf(
            "│ %-20s │ OP: %-8s │ OP: %-8s │ Time: %-3s    │\n",
            $name,
            $opTime,
            $opMem,
            $timeWinner
        );
        
        $output .= sprintf(
            "│                      │ EX: %-8s │ EX: %-8s │ Mem : %-3s    │\n",
            $exTime,
            $exMem,
            $memWinner
        );
        
        $output .= "├──────────────────────┼──────────────┼──────────────┼──────────────┤\n";
    }
    
    $output .= "└──────────────────────┴──────────────┴──────────────┴──────────────┘\n";
    
    return $output;
}

$opService = new App\Benchmark\OperationOutcome\OperationService();
$exService = new App\Benchmark\Exceptions\ExceptionService();

$tests = [
    'Simple Success' => [
        'op' => fn() => $opService->processSuccess(),
        'ex' => fn() => $exService->processSuccess()
    ],
    'Simple Error' => [
        'op' => fn() => $opService->processError(),
        'ex' => function() use ($exService) {
            try { $exService->processError(); } catch (\Exception $e) {}
        }
    ],
    'Chain (5 ops)' => [
        'op' => fn() => $opService->processChain(5),
        'ex' => function() use ($exService) {
            try { $exService->processChain(5); } catch (\Exception $e) {}
        }
    ],
    'Deep Chain (50)' => [
        'op' => fn() => $opService->processChain(50),
        'ex' => function() use ($exService) {
            try { $exService->processChain(50); } catch (\Exception $e) {}
        }
    ]
];

$results = [];
foreach ($tests as $name => $test) {
    $results[$name] = [
        'op' => run_benchmark("OP: $name", $test['op']),
        'ex' => run_benchmark("EX: $name", $test['ex'])
    ];
}
echo "<pre>";
var_dump($results);