<?php


namespace ITLeague\Microservice\Http\Bus;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Queue;

final class Adapter
{
    private array $handlers = [];


    /**
     * @param string $event
     * @param array|JsonResource $data
     */
    public function push(string $event, $data): void
    {
        if($data instanceof JsonResource) {
            $data = Arr::get($data->toResponse(request())->getData(true), $data::$wrap);
        }

        Log::info('Event pushed', ['event' => $event, 'data' => json_encode($data)]);
        Queue::pushOn($event, new Job($data, $event));
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
