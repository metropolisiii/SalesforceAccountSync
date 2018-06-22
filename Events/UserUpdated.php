<?php

namespace App\Shared\Events;

use App\ConferenceRegistration;
use App\Lib\Helpers\CryptHelper;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param User $user
	 */
     
    public $contact;
    
    public function __construct(User $user)
    {
    	$this->contact = $user;
        
        // Updated email
    	if ($user->Email != $user->getOriginal('Email'))
    	{
	    	foreach ($user->conference_registrations as $registration)
		    {
		    	if ( ! in_array($registration->ConferenceID, [162, 163]))
			        continue;

			    ConferenceRegistration::where('ID', $registration->ID)
				    ->update([
					    'InvitationLink' => $registration->generateInvitationKey()
				    ]);
		    }
    	}
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
