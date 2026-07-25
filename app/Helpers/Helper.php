<?php

namespace App\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Log;

class Helper
{
    public static function getPaginatedData(EloquentBuilder|QueryBuilder $builder)
    {
        return $builder->paginate(request('per_page', 10))->withQueryString();
    }

    public static function logException(Exception $e, array $context = []): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        $controller = 'Unknown';
        $method = 'unknown';

        foreach ($backtrace as $trace) {
            if (
                isset($trace['class']) &&
                str_contains($trace['class'], 'Controller')
            ) {
                $controller = class_basename($trace['class']);
                $method = $trace['function'] ?? 'unknown';
                break;
            }
        }

        Log::error("{$controller}@{$method} gagal", array_merge($context, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]));
    }
}