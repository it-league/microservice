<?php


namespace ITLeague\Microservice\HealthCheck;


use Exception;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class RabbitMQHealthCheck extends HealthCheck
{
    protected $name = 'rabbitmq';

    public function status(): Status
    {
        try {
            // TODO: добавить после разворачивания в docker
        } catch (Exception $e) {
            return $this->problem(
                'Failed to connect to RabbitMQ',
                [
                    'exception' => $this->exceptionContext($e),
                ]
            );
        }
    }
}
