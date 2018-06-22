<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\SalesforceService;
use Forrest;

class UpdateSalesforceAccount
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
    public function handle(UserUpdated $event)
    {
        $salesforce_service = new SalesforceService;
        $results = $salesforce_service->updateAccount($event->contact); 
        
        if (!$results)
            return false;
        
        //Update the DB with the SalesforceID
        if (!$event->contact->SalesforceID)
        {
            $event->contact->SalesforceID = $response['id'];
            $event->contact->save();
        }
        return true;
    }
}
