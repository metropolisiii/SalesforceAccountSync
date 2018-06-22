<?php

namespace App\Services;
use Forrest;

use App\User;

class SalesforceService{
    
    public function __construct()
    {
         Forrest::authenticate();
    }
    
   
    public function createAccount($user, $method = 'post')
    {
        $body = array();
        
        //Get Salesforce field map
        $sf_fields = array_flip(json_decode(file_get_contents(resource_path("json/salesforce_db_user_map.json")), true));

        //Fill in all fields from mapping and save to DB
        
        foreach ($user->getAttributes() as $key=>$value)
        {
            if ($key == 'DOB')
            {
                $d = \DateTime::createFromFormat('Y-m-d', $value);
                if ($d && $d->format('Y-m-d') === $value)
                    $body[$sf_fields[$key]] = $value; 
            }
            else if ($key == 'CRMEthnicity')
            {
                switch ($value)
                {
                    case 'American Indian or Alaska Native':
                        $value = 'Native American';
                        break;
                    case 'Asian':
                        $value = 'Asian/Pacific Islander';
                        break;
                    case 'African American':
                        $value = 'Black';
                        break;
                    case 'Native Hawaiian or Other Pacific Islander':
                        $value = 'Asian/Pacific Islander';
                        break;
                    case 'Hispanic or Latino':
                        $value = 'Hispanic';
                        break;
                    case 'Prefer not to say':
                        $value = 'Unknown';
                        break;
                }
                
                $body[$sf_fields[$key]] = $value; 
            }
            else if ($key == 'Gender')
            {
                switch ($value)
                {
                    case 'F':
                        $value = 'Female';
                        break;
                    case 'M':
                        $value = 'Male';
                        break;
                    default:
                        $value = '--None--';
                        break;
                }
                $body[$sf_fields[$key]] = $value; 
            }
            else if ($key == 'TitleLevel')
            {
                $value = User::$titleLevelHumanBiding[$value];
                
                if ($value == 'Unknown')
                    $value = '';
                
                $body[$sf_fields[$key]] = $value; 
            }
            else if ($key == 'AdminID')
            {
                $admin = \App\AdminUser::find($value);
                
                $body['EP_Owner__pc'] = $this->getUserByName($admin['Name']);
            }
            else if (array_key_exists($key, $sf_fields) && $key != 'ID')
                $body[$sf_fields[$key]] = $value;             
        }
        
        $body['Status__pc'] = 'Open';
        $body['City__pc'] = 'New York';
        
        if ($method == 'patch')
            unset($body['Id']);
        
        try{
            $response = Forrest::sobjects('Account'.($method == 'patch' ? '/'.$user->SalesforceID : ''),[
                'method' => $method,
                'body'   => $body
            ]);
        }
        catch(Exception $e)
        {           
            return false;
        };  
        
        return $response;
    }

    public function updateAccount($user)
    {
        return $this->createAccount($user, 'patch');
    }
    
    public function getUserByName($name)
    {
        try{
            $results = Forrest::query("SELECT Id FROM User WHERE Name = '{$name}'");
        }
        catch (Exception $e)
        {
            return  '';
        }
        
        if ($results['totalSize'] == 0)
            return "";
        return $results['records'][0]['Id'];
    }
    
    public function addContactTypeToAccount($type)
    {
        $body = [];
        
        $user = \App\User::find($type->UserID);
        
        switch ($type->contact_type->Type)
        {
            case 'Attendee':
                $body['Attendee__c'] = 1;
                break;
            case 'Artist':
               $body['EP__c'] = 1;
                break;
            case 'Performer':
               $body['EP__c'] = 1;
                break;
            case 'Speaker':
               $body['EP__c'] = 1;
                break;
            case 'Staff':
               $body['Staff__c'] = 1;
                break;                
            case 'Press':
               $body['Press__c'] = 1;
                break;            
        }
        if (!empty($body))
        {
            try
            {                
                Forrest::sobjects('Account/'.$user->SalesforceID,[
                    'method' => 'patch',
                    'body'   => $body
                ]);
            }
            catch (Exception $e)
            {
                \Log::error($e);
                return false;
            }
        }
        return true;
    }
}