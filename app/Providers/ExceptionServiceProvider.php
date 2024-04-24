<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Request $request): void
    {
        $this->app->bind('Illuminate\Contracts\Debug\ExceptionHandler', function ($app) {
            return new class($app) extends \Illuminate\Foundation\Exceptions\Handler {
                public function render($request, Throwable $exception)
                {
                    if ($this->shouldHandleValidationException($exception)) {
                        return parent::render($request, $exception);
                    }

                    if ($this->shouldHandleOtherException($exception)) {
                        $this->sendErrorToBugAtlas($request, $exception);
                    }

                    return parent::render($request, $exception);
                }

                protected function shouldHandleValidationException(Throwable $exception): bool
                {
                    return $exception instanceof ValidationException;
                }

                protected function shouldHandleOtherException(Throwable $exception): bool
                {
                    return $exception instanceof \Exception || $exception instanceof \DivisionByZeroError;
                }

                protected function sendErrorToBugAtlas($request, Throwable $exception): void
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
                    $postField = json_encode($body);

                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => 'https://api.bugatlas.com/v1/api/errors',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $postField,
                        CURLOPT_HTTPHEADER => [
                            "api_key: $apiKey",
                            "secret_key: $secretKey",
                            "Content-Type: application/json"
                        ],
                    ]);
                    $response = curl_exec($curl);
                    curl_close($curl);

                    Log::info('result' . $response);
                }
            };
        });
    }
}
