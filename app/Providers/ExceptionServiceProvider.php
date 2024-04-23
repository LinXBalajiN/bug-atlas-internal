<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Exception;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind('Illuminate\Contracts\Debug\ExceptionHandler', function ($app) {
            return new class($app) extends \Illuminate\Foundation\Exceptions\Handler {
                public function render($request, Throwable $exception)
                {
                    if ($exception instanceof ValidationException) {
                        return parent::render($request, $exception); //if controller or Request have validation for input values then not show the exception & error.
                    }

                    if ($exception instanceof \Exception || $exception instanceof \DivisionByZeroError) {
                        $errorMessage = $exception->getMessage();
                        $errorTrace = $exception->getTraceAsString();
                    
                        $errorType = get_class($exception);
                        $line = $exception->getLine();
                        
                        // $response = Http::get(config('app.url'));
                        // $headers = [
                        // 'api_key' => config('app.api_key'),
                        // 'secret_key' => config('app.secret_key'),
                        // 'Content-Type' => 'application/json'
                        // ];
                        $body = [
                            'request_url' => $request->url(),
                            'request_method' => $request->method(),
                            'payload' => json_encode($request->all()),
                            'error_type' => (string)$errorType,
                            'error_message' => $errorMessage,
                            'tag' => "tag",
                            'meta' => [
                                'error_line' =>$line,
                                'stacktrace' => $errorTrace,
                                    ]
                            ];
                        // $response = Http::withHeaders($headers)
                        //             ->post('https://api.bugatlas.com/v1/api/errors', $body);

                        $apiKey = config('app.api_key');
                        $secretKey = config('app.secret_key');
                        $postField = json_encode($body);
                        // dd($postField);
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.bugatlas.com/v1/api/errors',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        // CURLOPT_POSTFIELDS => $postField,
                        CURLOPT_POSTFIELDS =>'{
                            "request_url": "/v1/projects",
                            "request_method": "POST",
                            "payload": "payload",
                            "error_type": 0,
                            "error_message": "Project creation failed",
                            "tag": "tag",
                            "meta": {
                                "meta": "data"
                            }
                        }',
                        CURLOPT_HTTPHEADER => array(
                            "api_key: $apiKey",
                            "secret_key: $secretKey",
                            "Content-Type: application/json"
                        ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        LOG::info('result'. $response);  
                        dd(response);                      
                    }        

                    return parent::render($request, $exception);
                }
                
            };
        });
    }
}
