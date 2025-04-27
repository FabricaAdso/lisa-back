<?php

namespace App\Services;

use Google\Client as Google_Client;
use Google\Service\Calendar as Google_Service_Calendar;

class GoogleCalendarService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Laravel Google Calendar');
        $this->client->setDeveloperKey(env('GOOGLE_CALENDAR_API_KEY')); // Solo API Key
        
        $this->service = new Google_Service_Calendar($this->client);
    }


    public function getHolidays($year = null)
    {
        $calendarId = "es.co#holiday@group.v.calendar.google.com";
        $year = $year ?? date('Y');
        
        $optParams = [
            'timeMin' => "{$year}-01-01T00:00:00Z",
            'timeMax' => "{$year}-12-31T23:59:59Z",
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ];
    
        try {
            $events = $this->service->events->listEvents($calendarId, $optParams)->getItems();
            
            // Filtrar SOLO eventos con description === "Día festivo"
            $holidays = array_filter($events, function($event) {
                $description = $event->getDescription();
                return $description === "Día festivo";
            });
            
            return array_values($holidays); 
        } catch (\Exception $e) {
            logger()->error('Google Calendar Error', [
                'method' => 'getHolidays',
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}