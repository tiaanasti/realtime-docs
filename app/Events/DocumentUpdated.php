<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentUpdated implements ShouldBroadcastNow
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
    )
    {
        // Refresh supaya data terbaru dikirim
        $this->document =
            $document->fresh();

        $this->user_id =
            is_object($user)
                ? $user->id
                : $user;

        $this->user_name =
            is_object($user)
                ? $user->name
                : 'Unknown';
    }

    // Broadcast channel
    public function broadcastOn(): array
    {
        return [

            new PresenceChannel(
                'document.' . $this->document->id
            )

        ];
    }

    // Event name
    public function broadcastAs(): string
    {
        return 'DocumentUpdated';
    }

    // Data broadcast
    public function broadcastWith(): array
    {
        return [

            'document' => [

                'id' =>
                    $this->document->id,

                'title' =>
                    $this->document->title,

                'content' =>
                    $this->document->content,

                'updated_at' =>
                    $this->document->updated_at

            ],

            'user_id' =>
                $this->user_id,

            'user_name' =>
                $this->user_name,

        ];
    }
}