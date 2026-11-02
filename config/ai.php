<?php

return [
    'forecast_url' => env('AI_FORECAST_URL', 'http://127.0.0.1:8091/v1/forecast'),
    'explain_url' => env('AI_EXPLAIN_URL', 'http://127.0.0.1:8091/v1/explain'),
    'secret' => env('AI_SERVICE_SECRET'),
    'timeout' => (int) env('AI_SERVICE_TIMEOUT', 30),
    'retry_times' => (int) env('AI_SERVICE_RETRY_TIMES', 2),
    'history_days' => (int) env('AI_HISTORY_DAYS', 365),
];
