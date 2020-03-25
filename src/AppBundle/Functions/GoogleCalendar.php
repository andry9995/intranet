<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 05/04/2018
 * Time: 11:38
 */

namespace AppBundle\Functions;

use AppBundle\Entity\GoogleCalendarConfig;
use Doctrine\ORM\EntityManager;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class GoogleCalendar
{
    private $ROOT_DIR;
    //private $APPLICATION_NAME = 'Google Calendar API PHP';
    private $APPLICATION_NAME = 'Google Calendar API';
    private $CREDENTIALS_PATH;
    private $CLIENT_SECRET_PATH;
    private $REFRESH_TOKEN_PATH;
    private $SCOPES;
    private $kernel;
    //private $AUTH_CODE = '4/AABo5eNuTffWFtTpIVB2xHJwA84Sk1Y9yEuhT2otIxHd4a78b4_08Tg';
    //private $AUTH_CODE = 'AIzaSyBzz4FYT9PNKO0XuNXG33jPOgRHrP7SZoY';
    private $AUTH_CODE = '4/ZgA8ZLTTwp6UaLbJD5dYV5X-Y-2g5KnUO9Q8BLv7dRZtbGFugdlKwxIXRlNfDJYacsB6OQeBRrmg7S26a4AGn-Q';

    /** @var GoogleCalendarConfig */
    private $config;

    private $fromScriptura = false;

    /** @var \DateTime */
    private $timeMin;

    /**
     * @return bool
     */
    public function isFromScriptura()
    {
        return $this->fromScriptura;
    }

    /**
     * @param bool $fromScriptura
     */
    public function setFromScriptura($fromScriptura)
    {
        $this->fromScriptura = $fromScriptura;
    }

    /**
     * @return \DateTime
     */
    public function getTimeMin()
    {
        return $this->timeMin;
    }

    /**
     * @param \DateTime $timeMin
     */
    public function setTimeMin($timeMin)
    {
        $this->timeMin = $timeMin;
    }

    /**
     * @return \DateTime
     */
    public function getTimeMax()
    {
        return $this->timeMax;
    }

    /**
     * @param \DateTime $timeMax
     */
    public function setTimeMax($timeMax)
    {
        $this->timeMax = $timeMax;
    }

    /** @var \DateTime */
    private $timeMax;

    /**
     * @return GoogleCalendarConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param GoogleCalendarConfig $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function __construct()
    {
        global $kernel;
        $this->kernel = $kernel;
        $this->ROOT_DIR = $this->kernel->getRootDir();
        $this->CREDENTIALS_PATH = $this->ROOT_DIR . '/credentials/calendar_token.json';
        $this->CLIENT_SECRET_PATH = $this->ROOT_DIR . '/credentials/gcal_client_secret.json';
        $this->REFRESH_TOKEN_PATH = $this->ROOT_DIR . '/credentials/calendar_refresh_token.json';
        $this->SCOPES = implode(' ', array(
                Google_Service_Calendar::CALENDAR)
        );
    }

    /**
     * @throws \Google_Exception
     */
    public function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName($this->APPLICATION_NAME);
        $client->setScopes($this->SCOPES);
        $client->setAuthConfig($this->CLIENT_SECRET_PATH);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        // Load previously authorized credentials from a file.
        if (file_exists($this->CREDENTIALS_PATH)) {
            $accessToken = json_decode(file_get_contents($this->CREDENTIALS_PATH), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            /*header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
            if (isset($_GET['code'])) {
                $this->AUTH_CODE = $_GET['code'];
            }

            $authCode = trim(file_get_contents($authUrl));*/

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($this->AUTH_CODE);

            // Store the credentials to disk.
            if (!file_exists(dirname($this->CREDENTIALS_PATH))) {
                mkdir(dirname($this->CREDENTIALS_PATH), 0700, true);
            }
            file_put_contents($this->CREDENTIALS_PATH, json_encode($accessToken));
        }
        $client->setAccessToken($accessToken);
        if (!file_exists(dirname($this->REFRESH_TOKEN_PATH))) {
            mkdir(dirname($this->REFRESH_TOKEN_PATH), 0700, true);
        }
        if ($client->getRefreshToken()) {
            file_put_contents($this->REFRESH_TOKEN_PATH, $client->getRefreshToken());
        }

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $client->getRefreshToken();
            if (!$refreshToken || trim($refreshToken) == "") {
                $refreshToken = file_get_contents($this->REFRESH_TOKEN_PATH);
            }
            $client->fetchAccessTokenWithRefreshToken($refreshToken);
            file_put_contents($this->CREDENTIALS_PATH, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
     * @throws \Google_Exception
     * @throws \Exception
     */
    public function getCalendar()
    {
        if (!$this->config) {
            throw new \Exception("Calendar Config not set.");
        }
        $now = new \DateTime();
        if (!$this->timeMin) {
            $this->timeMin = new \DateTime($now->format('Y-m-01'));
            $this->timeMin->setTime(0, 0);
        }
        if (!$this->timeMax) {
            $this->timeMax = new \DateTime($this->timeMin->format('Y-m-01'));
            $this->timeMax->setTime(0, 0);
            $this->timeMax->add(new \DateInterval('P1M'));
            $this->timeMax->sub(new \DateInterval('P1D'));
            $this->timeMax->setTime(23, 59);
        }

        // Get the API client and construct the service object.
        $client = $this->getClient();
        $service = new Google_Service_Calendar($client);

        $optParams = array(
            'maxResults' => 100000,
            'orderBy' => 'startTime',
            'singleEvents' => TRUE,
            'timeMin' => $this->timeMin->format('c'),
            'timeMax' => $this->timeMax->format('c'),
        );
        $results = $service->events->listEvents($this->config->getIdentifiant(), $optParams);

        $listes = [];

        /** @var Google_Service_Calendar_Event $event */
        foreach ($results->getItems() as $event) {
            if (strtolower(substr($event->getSummary(), 0, 2)) != 's-') {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                $start = new \DateTime($start);

                $listes[] = [
                    'id' => $event->getId(),
                    'title' => trim($event->getSummary()),
                    'start' => $start->format('Y-m-d'),
                    'color' => $this->config->getColor(),
                    'textColor' => $this->config->getTextColor(),
                    'type' => 'gcal',
                ];
            }
        }

        return $listes;

    }

    /**
     * @param $title
     * @param $description
     * @param \DateTime $date
     * @return Google_Service_Calendar_Event
     * @throws \Exception
     */
    public function createEvent($title, $description, \DateTime $date)
    {
        if (!$this->config) {
            throw new \Exception("Calendar Config not set.");
        }

        /*if (!$this->config->isSendToGoogle() && $this->fromScriptura) {
            return null;
        }*/
        try {
            $client = $this->getClient();
            $service = new Google_Service_Calendar($client);
            $gc_event = new Google_Service_Calendar_Event();
            $event_date = new \Google_Service_Calendar_EventDateTime();
            $event_date->setDate($date->format('Y-m-d'));
            $event_date->setTimeZone($date->getTimezone());
            $gc_event->setStart($event_date);
            $gc_event->setEnd($event_date);
            $gc_event->setSummary($title);
            $gc_event->setDescription($description);
            $gc_event->setColorId(9);
            $options = [
                'sendNotifications' => true
            ];
            $createdEvent = $service->events->insert(
                $this->config->getIdentifiant(),
                $gc_event,
                $options
            );

            return $createdEvent;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @param $id
     * @param $title
     * @param $description
     * @return Google_Service_Calendar_Event|null
     * @throws \Exception
     */
    public function updateEvent($id, $title, $description)
    {
        if (!$this->config) {
            throw new \Exception("Calendar Config not set.");
        }
        /*if (!$this->config->isSendToGoogle() && $this->fromScriptura) {
            return null;
        }*/
        try {
            $client = $this->getClient();
            $service = new Google_Service_Calendar($client);
            $gc_event = new Google_Service_Calendar_Event();
            $gc_event->setSummary($title);
            $gc_event->setDescription($description);
            $gc_event->setColorId(9);
            $options = [
                'sendNotifications' => true
            ];
            $updatedEvent = $service->events->patch(
                $this->config->getIdentifiant(),
                $id,
                $gc_event,
                $options
            );

            return $updatedEvent;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @param $id
     * @param $date
     * @return Google_Service_Calendar_Event|null
     * @throws \Exception
     */
    public function updateDateEvent($id, \DateTime $date)
    {
        if (!$this->config) {
            throw new \Exception("Calendar Config not set.");
        }
        /*if (!$this->config->isSendToGoogle() && $this->fromScriptura) {
            return null;
        }*/
        try {
            $client = $this->getClient();
            $service = new Google_Service_Calendar($client);
            $gc_event = $service->events->get($this->config->getIdentifiant(), $id);

            $title = $gc_event->getSummary();
            $description = $gc_event->getDescription();

            if ($this->removeEvent($id)) {
                return $this->createEvent($title, $description, $date);
            } else {
                return null;
            }
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @param $eventId
     * @return bool
     * @throws \Exception
     */
    public function removeEvent($eventId) {
        if (!$this->config) {
            throw new \Exception("Calendar Config not set.");
        }
        if (!$this->config->isSendToGoogle() && $this->fromScriptura) {
            return null;
        }
        try {
            $client = $this->getClient();
            $service = new Google_Service_Calendar($client);
            $service->events->delete(
                $this->config->getIdentifiant(),
                $eventId
            );

            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}