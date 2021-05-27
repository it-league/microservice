<?php

namespace ITLeague\Microservice\Logger;

use Illuminate\Support\Facades\Log;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LogstashLogger
{
    /**
     * @param array $config
     * @return LoggerInterface
     */
    public function __invoke(array $config): LoggerInterface
    {
        $tmpSocket = @fsockopen("tcp://{$config['host']}", $config['port']);

        if (!$tmpSocket) {
            return Log::channel('stack');
        }

        @fclose($tmpSocket);

        $handler = new SocketHandler("tcp://{$config['host']}:{$config['port']}");
        $handler->setFormatter(new LogstashFormatter(config('app.name')));

        return new Logger($config['name'], [$handler]);
    }
}
