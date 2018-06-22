<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\SalesforceService;
use Forrest;

class CreateSalesforceAccount
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        Forrest::authenticate();
    }

    /**
     * Handle the event.
     *
     * @param  CreateContact  $event
     * @return void
     */
    public function handle(UserCreated $event)
    {
        $salesforce_service = new SalesforceService;
        
        $results = $salesforce_service->createAccount($event->contact); 
        
        if (!$results)
            return false;
        
        //Update the DB with the SalesforceID
        $event->contact->SalesforceID = $results['id'];
        $event->contact->save();
        
        return true;
    }
}
