<?php

namespace App\Shared\Events;

use Illuminate\Queue\SerializesModels;
use App\User;

class UserCreated
{
    use SerializesModels;
    
    public $contact;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $contact)
    {
        $this->contact = $contact;
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
