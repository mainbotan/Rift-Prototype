<?php

// https://habr.com/ru/sandbox/248424/
namespace App\Addition\Examples;

use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class SomeService 
{
    /**
     * Базовый пример успешной операции
     */
    public static function simpleSuccess(): OperationOutcome
    {
        $result = ['data' => 'Successful operation'];
        return Operation::success($result);
    }

    /**
     * Успешная операция с метриками
     */
    public static function successWithMetrics(): OperationOutcome
    {
        $result = ['user_id' => 123, 'name' => 'Huila'];
        
        $metrics = [
            'execution_time_ms' => 45.2,
            'memory_usage_mb' => 12.7,
            'database_queries' => 3
        ];
        
        return Operation::success($result, $metrics);
    }

    /**
     * Успешная операция с дебаг-информацией
     */
    public static function successWithDebug(): OperationOutcome
    {
        $result = ['status' => 'processed'];
        
        $debug = [
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3),
            'request_id' => uniqid()
        ];
        
        return Operation::success($result, null, $debug);
    }

    /**
     * Простая ошибка с кодом HTTP 400
     */
    public static function simpleError(): OperationOutcome
    {
        return Operation::error(
            Operation::HTTP_BAD_REQUEST,
            'Invalid input parameters'
        );
    }

    /**
     * Ошибка с дебаг-информацией
     */
    public static function errorWithDebug(): OperationOutcome
    {
        return Operation::error(
            Operation::HTTP_NOT_FOUND,
            'User not found',
            [
                'searched_id' => 999,
                'available_ids' => [1, 2, 3],
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)
            ]
        );
    }

    /**
     * Комплексный пример с обработкой бизнес-логики
     */
    public static function processUser(int $userId): OperationOutcome
    {
        // Валидация
        if ($userId <= 0) {
            return Operation::error(
                Operation::HTTP_BAD_REQUEST,
                'Invalid user ID',
                ['received_id' => $userId]
            );
        }

        // Бизнес-логика
        try {
            $user = self::fetchUserFromDb($userId);
            
            if (!$user) {
                return Operation::error(
                    Operation::HTTP_NOT_FOUND,
                    'User not found in database',
                    ['searched_id' => $userId]
                );
            }

            $processedUser = self::processUserData($user);
            
            $metrics = [
                'db_query_time' => 12.5,
                'processing_time' => 23.1
            ];
            
            $debug = [
                'original_data' => $user,
                'processed_at' => date('Y-m-d H:i:s')
            ];
            
            return Operation::success($processedUser, $metrics, $debug);
            
        } catch (\Exception $e) {
            return Operation::error(
                Operation::HTTP_INTERNAL_SERVER_ERROR,
                'Processing failed',
                [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }
    }

    private static function fetchUserFromDb(int $userId): ?array
    {
        // Имитация запроса к БД
        if ($userId === 1) {
            return ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'];
        }
        return null;
    }

    private static function processUserData(array $user): array
    {
        // Имитация обработки данных
        return [
            'id' => $user['id'],
            'username' => strtolower($user['name']),
            'email_verified' => true
        ];
    }


    // FP

    /**
     * Демонстрация работы метода withMetric()
     */
    public static function demoWithMetric(): OperationOutcome
    {
        $result = Operation::success(['initial' => 'data'])
            ->withMetric('start_time', microtime(true))
            ->withMetric('service', 'user_service');
            
        // Добавляем метрику после некоторых операций
        return $result->withMetric('end_time', microtime(true));
    }

    /**
     * Демонстрация работы методов then()
     */
    public static function demoThen(): OperationOutcome
    {
        return Operation::success(['id' => 1, 'name' => 'Alice'])
            ->then(function($data) {
                // Преобразуем данные и возвращаем новый OperationOutcome
                return Operation::success([
                    'user' => $data,
                    'timestamp' => time()
                ]);
            });
    }
    /**
     * Демонстрация работы методов then() и map()
     */
    public static function demoThenAndMap(): OperationOutcome
    {
        return Operation::success(['id' => 1, 'name' => 'Alice'])
            ->then(function($data) {
                // Преобразуем данные и возвращаем новый OperationOutcome
                return Operation::success([
                    'user' => $data,
                    'timestamp' => time()
                ]);
            })
            ->map(function($data) {
                // Только преобразуем данные
                $data['user']['name'] = strtoupper($data['user']['name']);
                return $data;
            });
    }
    public static function demoMap(): OperationOutcome
    {
        return Operation::success(['id' => 1, 'name' => 'Alice'])
            ->map(function($data) {
                // Только преобразуем данные
                $data['name'] = strtoupper($data['name']);
                return $data;
            });
    }

    /**
     * Демонстрация работы метода catch()
     */
    public static function demoCatch(): OperationOutcome
    {
        return Operation::error(404, 'User not found')
            ->catch(function($error, $code, $meta) {
                /**
                 * We log the error and return a new result.
                 */
                $debug['logged_at'] = date('Y-m-d H:i:s');
                return Operation::error(
                    $code,
                    "Handled: $error",
                    $debug
                );
            });
    }

    /**
     * Демонстрация работы метода tap()
     */
    public static function demoTap(): OperationOutcome
    {
        return Operation::success(['value' => 42])
            ->tap(function($result) {
                /**
                 * Logging without changing the result
                 */
                error_log("Processing value: {$result['value']}");
            })
            ->map(function($result) {
                $result['value'] *= 2;
                return $result;
            })
            ->tap(function($result) {
                error_log("Doubled value: {$result['value']}");
            });
    }

    /**
     * Демонстрация работы метода ensure()
     */
    public static function demoEnsure(): OperationOutcome
    {
        return Operation::success(['age' => 17])
            ->ensure(
                fn($data) => $data['age'] >= 18,
                'User must be at least 18 years old',
                403
            );
    }

    /**
     * Демонстрация работы метода merge()
     */
    public static function demoMerge(): OperationOutcome
    {
        $userData = Operation::success(['id' => 1, 'name' => 'Alice']);
        $userStats = Operation::success(['logins' => 42, 'last_login' => '2023-01-01']);

        return $userData->merge($userStats, function($data, $stats) {
            return array_merge($data, ['stats' => $stats]);
        });
    }

    /**
     * Демонстрация работы метода toJson()
     */
    public static function demoToJson(): string
    {
        $outcome = Operation::success(
            ['id' => 1, 'name' => 'Alice'],
            ['metrics' => ['time' => 12.3]],
            ['debug' => ['request_id' => 'abc123']]
        );

        // Стандартное преобразование
        $json1 = $outcome->toJson();

        // Кастомное преобразование
        $json2 = $outcome->toJson(function(OperationOutcome $outcome) {
            return [
                'user' => $outcome->result,
                'execution_time' => $outcome->getMetric('time'),
                'success' => $outcome->isSuccess()
            ];
        });

        return $json1 . "\n\n" . $json2;
    }

    /**
     * Комплексный пример с цепочкой вызовов
     */
    public static function demoChain(): OperationOutcome
    {
        return Operation::success(['id' => 1, 'name' => ' alice '])
            ->withMetric('start_time', microtime(true))
            ->map(function($user) {
                $user['name'] = trim($user['name']);
                return $user;
            })
            ->ensure(
                fn($user) => !empty($user['name']),
                'Name cannot be empty',
                400
            )
            ->map(function($user) {
                $user['name'] = ucfirst($user['name']);
                return $user;
            })
            ->then(function($user) {
                return self::fetchUserStats($user['id'])
                    ->map(function($stats) use ($user) {
                        return array_merge($user, ['stats' => $stats]);
                    });
            })
            ->addDebugData('ahuenno', 'yes')
            ->withMetric('end_time', microtime(true));
    }

    private static function fetchUserStats(int $userId): OperationOutcome
    {
        // Имитация получения статистики
        if ($userId === 1) {
            return Operation::success([
                'logins' => 42,
                'last_login' => '2023-01-01'
            ]);
        }
        return Operation::error(404, 'Stats not found');
    }

    public static function demoMetricsAndDebug(): OperationOutcome
    {
        // 1. Create successful operation with initial metrics and debug data
        $operation = Operation::success(
            result: ['user_id' => 123],
            metrics: ['start_time' => microtime(true)],
            debug: ['init_source' => 'user_service']
        );

        // 2. Add metrics during processing
        $operation
            ->withMetric('db_queries', 5)
            ->withMetric('cache_hits', 12);

        // 3. Add debug information
        $operation
            ->addDebugData('processing_stage', 'data_validation')
            ->addDebugData('memory_usage', memory_get_usage());

        // 4. Demonstrate metric retrieval
        $queries = $operation->getMetric('db_queries'); // 5
        $cacheHits = $operation->getMetric('cache_hits'); // 12

        // 5. Demonstrate debug retrieval
        $source = $operation->getDebug('init_source'); // 'user_service'
        $stage = $operation->getDebug('processing_stage'); // 'data_validation'

        // 6. Final modification before return
        $operation
            ->withMetric('end_time', microtime(true))
            ->addDebugData('completed_at', date('Y-m-d H:i:s'));

        return $operation;
    }
}