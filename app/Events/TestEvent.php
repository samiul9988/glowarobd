<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TestEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($message = 'This is a test event message.')
    {
        $this->message = $message;
    }

    /**
     * The channel on which the event is broadcast.
     */
    public function broadcastOn()
    {
        return new Channel('test-channel');
    }

    /**
     * Optional event name when received by Echo.
     */
    public function broadcastAs()
    {
        return 'TestEvent';
    }
}
