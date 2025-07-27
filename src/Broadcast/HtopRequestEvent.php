<?php

namespace Htop\Broadcast;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class HtopRequestEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('htop');
    }

    public function broadcastAs(): string
    {
        return 'new-request';
    }

    public function broadcastWith(): array
    {
        return [
            'method' => request()->method(),
            'path' => request()->path(),
            'status' => response()->status() ?? 200,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
