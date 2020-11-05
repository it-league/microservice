<?php


namespace ITLeague\Microservice\Http\Bus;


final class Adapter
{
    private array $handlers = [];


    public function push(string $event, array $data): void
    {
        dispatch(new Job($data, $event))->onQueue($event);
    }

    public function getHandlers(string $event): array
    {
        return $this->handlers[$event] ?? [];
    }

    public function getEvents(): array
    {
        return array_keys($this->handlers);
    }

    public function listen($events, string $handler): void
    {
        foreach ((array)$events as $event) {
            if (is_subclass_of($handler, EventHandler::class)) {
                $this->handlers[$event][] = $handler;
            }
        }
    }
}
