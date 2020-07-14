<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use PhpParser\Node\Scalar\String_;

class HistorialModificacionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $description;

    public $proceso_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($description, $proceso_id)
    {
        $this->description = $description;
        $this->proceso_id = $proceso_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
