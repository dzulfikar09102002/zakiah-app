<?php

namespace App\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Log;
use Throwable;

class Helper
{
    public static function getPaginatedData(EloquentBuilder|QueryBuilder $builder)
    {
        return $builder->paginate(request('per_page', 10))->withQueryString();
    }

    public static function logException(Throwable $e, array $context = []): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $caller = 'Unknown';
        $method = 'unknown';

        foreach ($backtrace as $trace) {

            if (! isset($trace['class'])) {
                continue;
            }

            $class = class_basename($trace['class']);

            if (
                str_contains($class, 'Controller') ||
                str_contains($class, 'Job') ||
                str_contains($class, 'Command') ||
                str_contains($class, 'Listener') ||
                str_contains($class, 'Service')
            ) {
                $caller = $class;
                $method = $trace['function'] ?? 'unknown';
                break;
            }
        }

        Log::error(
            "\n".
            "==================== 🚨 APPLICATION ERROR 🚨 ====================\n".
            "Location : {$caller}@{$method}\n".
            "Message  : {$e->getMessage()}\n".
            "File     : {$e->getFile()}:{$e->getLine()}\n".
            "===============================================================\n",
            array_merge($context, [
                'caller' => $caller,
                'method' => $method,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ])
        );
    }
}