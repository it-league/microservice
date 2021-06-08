<?php


namespace ITLeague\Microservice\HealthCheck;


use Exception;
use Illuminate\Support\Facades\Redis;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class RedisHealthCheck extends HealthCheck
{
    protected $name = 'redis';

    public function status(): Status
    {
        try {
            Redis::ping();
        } catch (Exception $e) {
            return $this->problem(
                'Failed to connect to redis',
                [
                    'exception' => $this->exceptionContext($e),
                ]
            );
        }

        return $this->okay();
    }
}
