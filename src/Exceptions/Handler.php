<?php

namespace ITLeague\Microservice\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\JsonResponse;
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
     * @param \Throwable $e
     *
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if (config('app.debug') === false) {
            return $this->renderApiException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param \Throwable $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function renderApiException(Throwable $exception): JsonResponse
    {
        $code = $exception->getCode();
        $detail = $exception->getMessage();

        if ($exception instanceof AuthenticationException) {
            $code = Response::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof AuthorizationException) {
            $code = Response::HTTP_FORBIDDEN;
        } elseif ($exception instanceof ModelNotFoundException || $exception instanceof FileNotFoundException) {
            $code = Response::HTTP_NOT_FOUND;
            $detail = __('Entity not found');
        } elseif ($exception instanceof NotFoundHttpException) {
            $code = Response::HTTP_NOT_FOUND;
            $detail = __('Endpoint not found');
        } elseif ($exception instanceof ValidationException) {
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
            $detail = $exception->errors();
        } elseif ($exception instanceof RelationNotFoundException) {
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return $this->respondError(
            $detail ?: __('Unexpected error. Try later'),
            $code ?: Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
