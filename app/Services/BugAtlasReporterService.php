<?php

namespace App\Services;

use Throwable;
use Illuminate\Http\Request;
use App\Traits\ApiBugAtlas;

class BugAtlasReporterService
{
    use ApiBugAtlas;

    public function report($request, Throwable $exception): void
    {
        $body = [
            "request_url" => $request->fullUrl(),
            "request_method" => $request->method(),
            "payload" => json_encode($request->all()),
            "error_type" => get_class($exception),
            "error_message" => $exception->getMessage(),
            "tag" => config('bugatlas.tag'),
            "meta" => [
                'error_line' => $exception->getLine(),
                'stacktrace' => $exception->getTraceAsString(),
            ]
        ];
        $this->processApiResponse("/api/errors", $body);
    }
}
