<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoViewed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $videoId;
    public $views;

    /**
     * Create a new event instance.
     *
     * @param  int  $videoId
     * @param  int  $views
     * @return void
     */
    public function __construct($videoId, $views)
    {
        $this->videoId = $videoId;
        $this->views = $views;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        // public channel for frontend clients to listen to
        return new Channel('video.' . $this->videoId);
    }

    /**
     * Optionally rename the event on frontend
     */
    public function broadcastAs()
    {
        return 'VideoViewed';
    }
}
