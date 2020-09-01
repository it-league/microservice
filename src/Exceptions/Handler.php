<?php

namespace ITLeague\Microservice\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use ITLeague\Microservice\Traits\ApiResponse;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Throwable $exception
     *
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if (config('app.debug') === false) {
            return $this->renderApiException($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param \Throwable $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function renderApiException(Throwable $exception)
    {
        $code = $exception->getCode();
        $detail = $exception->getMessage();

        if ($exception instanceof AuthenticationException) {
            $code = Response::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof AuthorizationException) {
            $code = Response::HTTP_FORBIDDEN;
        } elseif ($exception instanceof ModelNotFoundException || $exception instanceof FileNotFoundException) {
            $code = Response::HTTP_NOT_FOUND;
            $detail = 'Entity not found!';
        } elseif ($exception instanceof NotFoundHttpException) {
            $code = Response::HTTP_NOT_FOUND;
            $detail = 'Endpoint not found';
        } elseif ($exception instanceof ValidationException) {
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
            $detail = $exception->errors();
        } elseif ($exception instanceof RelationNotFoundException) {
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return $this->respondError(
            $detail ?: 'Unexpected error. Try later',
            $code ?: Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
