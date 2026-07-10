<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \League\OAuth2\Server\Exception\OAuthServerException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Kill reporting if this is an "access denied" (code 9) OAuthServerException.
        if ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException && $exception->getCode() == 9) {
            return;
        }

        // Return JSON response for API requests or if the client expects JSON
        if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
            $status = method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : 500;

            $message = config('app.debug')
                ? $exception->getMessage()
                : 'Something went wrong.';

            if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                $message = 'Resource not found.';
                $status = 404;
            }

            if ($exception instanceof AuthenticationException) {
                $message = 'Unauthorized.';
                $status = 401;
            }

            if ($exception instanceof ThrottleRequestsException) {
                $message = 'Too many requests. Please slow down.';
                $status = 429;
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        if ($exception instanceof ThrottleRequestsException) {
            // You can check by route name
            if ($request->routeIs('merchant.*')) {
                return response()->json([
                    'message' => 'Too many requests. Please slow down.',
                    'status' => 429,
                ], 429);
            }
        }

        return parent::render($request, $exception);
    }



}
