<?php


namespace ITLeague\Microservice\Http\Bus;


use Auth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use ITLeague\Microservice\Facades\MicroserviceBus;
use ITLeague\Microservice\Models\User;
use Throwable;

final class Job implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $data;
    private string $event;
    private ?User $user;

    public function __construct(array $data, string $event)
    {
        $this->data = $data;
        $this->event = $event;
        $this->user = Auth::check() ? Auth::user() : null;
    }

    public function handle(): void
    {
        Log::info('Event listened', ['event' => $this->event, 'data' => json_encode($this->data)]);

        if ($this->user instanceof Authenticatable) {
            Auth::setUser($this->user);
        }

        foreach (MicroserviceBus::getHandlers($this->event) as $handler) {
            (new $handler())->handle($this->event, $this->data);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        // TODO: разобраться с логированием
    }
}
