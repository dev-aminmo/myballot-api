<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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

        if( $request->is('api/*')){
            if ($exception instanceof ModelNotFoundException) {
                $model = strtolower(class_basename($exception->getModel()));

                return response()->json([
                    'error' => 'model not found',
                    "code"=>404
                ], 404);
            }
            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => 'page not found',
                    "code"=>404
                ], 404);

            }
        }
        if ($exception instanceof AuthorizationException)
        {
            return response()->json(['error' => 'Not authorized.',
                "code"=>403
                ],403);
        }
        if ($exception instanceof \ErrorException) {
            return parent::render($request, $exception);
        } else {
            return parent::render($request, $exception);
        }
    }
}
