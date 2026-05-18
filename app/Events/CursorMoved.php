<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CursorMoved implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $data;
    public $documentId;

    public function __construct($data, $documentId)
    {
        $this->data = $data;
        $this->documentId = $documentId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('document.' . $this->documentId);
    }

    public function broadcastAs()
    {
        return 'CursorMoved';
    }
}