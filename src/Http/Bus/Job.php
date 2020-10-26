<?php


namespace ITLeague\Microservice\Http\Bus;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ITLeague\Microservice\Facades\MicroserviceBus;
use Throwable;

final class Job implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $data;
    private string $event;

    public function __construct(array $data, string $event)
    {
        $this->data = $data;
        $this->event = $event;
    }

    public function handle(): void
    {
        foreach (MicroserviceBus::getHandlers($this->event) as $handler) {
            (new $handler())->handle($this->data);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        // TODO: разобраться с логированием
        \Log::error($exception->getMessage());
    }
}
