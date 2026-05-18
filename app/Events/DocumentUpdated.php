<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentUpdated implements ShouldBroadcast
{
    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;

    public $document;

    public $user_id;

    public $user_name;

    public function __construct(
        Document $document,
        $user
    ) {
        $this->document = $document;

        $this->user_id =
            $user->id;

        $this->user_name =
            $user->name;
    }

    public function broadcastOn(): array
    {
        return [

            new PresenceChannel(
                'document.' . $this->document->id
            )

        ];
    }

    public function broadcastAs(): string
    {
        return 'DocumentUpdated';
    }

    public function broadcastWith(): array
    {
        return [

            'document' =>
                $this->document,

            'user_id' =>
                $this->user_id,

            'user_name' =>
                $this->user_name,

        ];
    }
}