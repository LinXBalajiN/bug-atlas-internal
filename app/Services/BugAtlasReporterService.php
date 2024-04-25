<?php

namespace App\Services;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Log;

class BugAtlasReporterService
{
    public function report($request, Throwable $exception): void
    {
        $errorMessage = $exception->getMessage();
        $errorTrace = $exception->getTraceAsString();
        $errorType = get_class($exception);
        $line = $exception->getLine();

        $body = [
            "request_url" => $request->fullUrl(),
            "request_method" => $request->method(),
            "payload" => json_encode($request->all()),
            "error_type" => $errorType,
            "error_message" => $errorMessage,
            "tag" => "",
            "meta" => [
                'error_line' => $line,
                'stacktrace' => $errorTrace,
            ]
        ];

        $apiKey = config('app.api_key');
        $secretKey = config('app.secret_key');

        $response = Http::withHeaders([
            "api_key" => $apiKey,
            "secret_key" => $secretKey,
            "Content-Type" => "application/json"
        ])->post('https://api.bugatlas.com/v1/api/errors', $body);

        Log::info('result' . $response);
        dd($body, $response->body());
    }
}
