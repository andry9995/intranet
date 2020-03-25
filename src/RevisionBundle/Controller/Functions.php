<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 14/09/2018
 * Time: 15:37
 */

namespace RevisionBundle\Controller;

use AppBundle\Entity\GoogleCalendarConfig;
use AppBundle\Functions\GoogleCalendar;

class Functions
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $interval
     * @param int $iteration
     * @return array
     */
    public static function datesBetweenWithInterval(\DateTime $start, \DateTime $end, $interval = 'D',$iteration = 1)
    {
        $periodInt = new \DateInterval('P'.$iteration.$interval);
        /** @var \DateTime[] $datePeriodes */
        $datePeriodes = new \DatePeriod($start, $periodInt ,$end);
        $dates = array();
        foreach($datePeriodes as $date)
        {
            array_push($dates,$date->setTime(0,0,0));
        }
        return $dates;
    }

    /**
     * @param null $annee
     * @return object
     */
    public static function getStartEndInAnnee($annee = null)
    {
        if (is_null($annee)) $annee = intval((new \DateTime())->format('Y'));

        return (object)
        [
            'start' => \DateTime::createFromFormat('d-m-Y','01-01-'.$annee),
            'end' => \DateTime::createFromFormat('d-m-Y','31-12-'.$annee)
        ];
    }

    /**
     * @param \DateTime $date
     * @return \DateTime
     */
    public static function getNextOuvrable(\DateTime $date)
    {
        while($date->format('w') == 6 || $date->format('w') == 0)
            $date->add(new \DateInterval('P1D'));

        return $date;
    }

    public static function getJourFerie($client, $date){
        $annee = $date->format('Y');
        $debut = \DateTime::createFromFormat('Y-m-d', $annee.'-01-01');
        $fin = \DateTime::createFromFormat('Y-m-d', $annee.'-12-31');
        $config = new GoogleCalendarConfig();

        $config->setIdentifiant('fr.mg#holiday@group.v.calendar.google.com');
        $config->setClient($client);

        $calendar = new GoogleCalendar();
        $calendar->setConfig($config);
        $calendar->setTimeMin($debut);
        $calendar->setTimeMax($fin);

        return $calendar->getCalendar();
    }
}