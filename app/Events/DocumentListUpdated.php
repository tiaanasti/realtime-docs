<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentListUpdated implements ShouldBroadcast
{
    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;

//   Public variables
    public $documentId;

    public $action;

// CPnstructor
    public function __construct(
        int $documentId,
        string $action
    )
    {
        $this->documentId = $documentId;

        $this->action = $action;
    }

   
// Broadcast channels
    public function broadcastOn(): array
    {
        return [

            new Channel('documents')

        ];
    }

    // Event name
    public function broadcastAs(): string
    {
        return 'document.list.updated';
    }

// Broadcast data
    public function broadcastWith(): array
    {
        return [

            'document_id' => $this->documentId,

            'action' => $this->action,

        ];
    }
}