<?php

namespace ITLeague\Microservice;

use Exception;
use Illuminate\Queue\WorkerOptions;
use ITLeague\Microservice\Facades\MicroserviceBus;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Consumer;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

class BusConsumer extends Consumer
{
    protected array $consumerTags = [];

    public function daemon($connectionName, $queue, WorkerOptions $options): void
    {
        if ($this->supportsAsyncSignals()) {
            $this->listenForSignals();
        }

        $lastRestart = $this->getTimestampOfLastQueueRestart();

        /** @var RabbitMQQueue $connection */
        $connection = $this->manager->connection($connectionName);
        $this->channel = $connection->getChannel();

        $this->channel->basic_qos(
            $this->prefetchSize,
            $this->prefetchCount,
            null
        );


        foreach (MicroserviceBus::getEvents() as $event) {
            $queue = config('app.name') . '.' . $event;
            $this->channel->queue_declare($queue, false, false, true, false);
            $this->channel->queue_bind($queue, config('queue.connections.rabbitmq.options.exchange.name'), $event);
            $this->consumerTags[] = $this->channel->basic_consume(
                $queue,
                '',
                false,
                false,
                false,
                false,
                function (AMQPMessage $message) use ($connection, $options, $connectionName, $queue): void {
                    $this->gotJob = true;

                    $job = new RabbitMQJob(
                        $this->container,
                        $connection,
                        $message,
                        $connectionName,
                        $queue
                    );

                    if ($this->supportsAsyncSignals()) {
                        $this->registerTimeoutHandler($job, $options);
                    }

                    $this->runJob($job, $connectionName, $options);
                }
            );
        }

        while ($this->channel->is_consuming()) {
            // Before reserving any jobs, we will make sure this queue is not paused and
            // if it is we will just pause this worker for a given amount of time and
            // make sure we do not need to kill this worker process off completely.
            if (! $this->daemonShouldRun($options, $connectionName, $queue)) {
                $this->pauseWorker($options, $lastRestart);
                continue;
            }

            // If the daemon should run (not in maintenance mode, etc.), then we can wait for a job.
            try {
                $this->channel->wait(null, true, (int)$options->timeout);
            } catch (AMQPRuntimeException $exception) {
                $this->exceptions->report($exception);

                $this->kill(1);
            } catch (Exception $exception) {
                $this->exceptions->report($exception);

                $this->stopWorkerIfLostConnection($exception);
            } catch (Throwable $exception) {
                $this->exceptions->report($exception = new FatalThrowableError($exception));

                $this->stopWorkerIfLostConnection($exception);
            }

            // If no job is got off the queue, we will need to sleep the worker.
            if (! $this->gotJob) {
                $this->sleep($options->sleep);
            }

            // Finally, we will check to see if we have exceeded our memory limits or if
            // the queue should restart based on other indications. If so, we'll stop
            // this worker and let whatever is "monitoring" it restart the process.
            $this->stopIfNecessary($options, $lastRestart, $this->gotJob ? true : null);

            $this->gotJob = false;
        }
    }

    /**
     * Stop listening and bail out of the script.
     *
     * @param int $status
     *
     * @return void
     */
    public function stop($status = 0): void
    {
        // Tell the server you are going to stop consuming.
        // It will finish up the last message and not send you any more.
        foreach ($this->consumerTags as $consumerTag) {
            $this->channel->basic_cancel($consumerTag, false, true);
        }

        parent::stop($status);
    }
}
