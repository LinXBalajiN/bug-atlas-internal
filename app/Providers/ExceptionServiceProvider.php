<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
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
                    if ($this->shouldHandleException($exception)) {
                        $this->sendErrorToBugAtlas($request, $exception);
                    }

                    return parent::render($request, $exception);
                }

                protected function shouldHandleException(Throwable $exception): bool
                {
                    $exceptionName = get_class($exception);

                    switch($exceptionName){
                        case "SyntaxError":
                        case "IndentationError":
                        case "NameError":
                        case "TypeError":
                        case "ValueError":
                        case "KeyError":
                        case "IndexError":
                        case "FileNotFoundError":
                        case "IOError":
                        case "AttributeError":
                        case "ZeroDivisionError":
                        case "ImportError":
                        case "KeyboardInterrupt":
                        case "AssertionError":
                        case "ArithmeticError":
                        case "MemoryError":
                        case "ValidationException":
                        case "ModelNotFoundException":
                        case "MethodNotAllowedHttpException":
                        case "NotFoundHttpException":
                        case "MaintenanceModeException":            
                        case "Illuminate\Database\QueryException":
                        case "AuthorizationException":
                        case "AuthenticationException":
                        case "FileNotFoundException":
                        case "HttpException":
                        case "TokenMismatchException":
                        case "ServiceNotFoundException":
                        case "ThrottleRequestsException":
                        case "BadMethodCallException":
                        case "PDOException":
                        case "ConnectException":
                        case "PermissionDeniedException":
                        case "DeadlockException":
                        case "ClientException":
                        case "ServerException":
                        case "ReflectionException":
                        case "Illuminate\Contracts\Container\BindingResolutionException": 
                        case "DivisionByZeroError":
                        case "InvalidArgumentException":
                        case "Error":
                        case "Illuminate\Http\Client\ConnectionException":
                        case "Illuminate\View\ViewException":
                        case "ErrorException":        
                        case "Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException":
                        case "Symfony\Component\Debug\Exception\FatalThrowableError":
                        case "GuzzleHttp\Exception\ClientException":
                        case "GuzzleHttp\Exception\ConnectException":
                        case "RuntimeException":
                        case "MissingDependencyException":                        

                            return true;
                            break;
                    }
                    return false;
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
                    dd($response);
                }
            };
        });
    }
}
