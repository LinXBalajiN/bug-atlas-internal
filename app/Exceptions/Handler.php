<?php

namespace App\Exceptions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {

        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Exception) {
            $errorMessage = $exception->getMessage();
            $errorTrace = $exception->getTraceAsString();
        
            $time = time();
            $time = date('F jS Y, g:i:s', $time);
            $hostname = gethostname();
            $errorType = get_class($exception);
            $line = $exception->getLine();
            $location = $exception->getFile();
            $requestInfo = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->header(),
                'payload' => $request->all(),
            ];
        
            $errorResponse = [
                'time' => $time,
                'hostname' => $hostname,
                'error' => $errorMessage,
                'error_type' => $errorType,
                'error_location' => $location,
                'error_line' => $line,
                'request' => $requestInfo,
                'trace' => $errorTrace,
            ];
        
            // dd($errorResponse);
            return response()->json(['status' => 'Failed', 'message' => $errorResponse]);
        }        

        return parent::render($request, $exception);
    }
}
