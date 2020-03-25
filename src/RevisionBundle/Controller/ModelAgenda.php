<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 13/09/2018
 * Time: 16:00
 */

namespace RevisionBundle\Controller;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\TachesSynchro;
use AppBundle\Entity\TacheSynchro;

class ModelAgenda
{
    /**
     * @param Dossier $dossier
     * @param TacheSynchro|null $tacheSynchro
     * @param $title
     * @param \DateTime $start
     * @param $type
     * @param string $gcTitle
     * @param string $dossiers
     * @param string $originalTitle
     * @param string $id
     * @param string $color
     * @param string $textColor
     * @param string $client
     * @return array
     */
    public static function Tache(
        Dossier $dossier = null,
        TacheSynchro $tacheSynchro = null,
        $title,\DateTime $start,$type,
        $gcTitle = '',$dossiers = '',$originalTitle = '',
        $id = '', $color = '', $textColor = '', $client = ''
    ){
        $types = ['local','gcal'];
        $result =
        [
            'dossier' => ($dossier) ? $dossier->getId() : 0,
            'tacheSynchro' => ($tacheSynchro) ? $tacheSynchro->getId() : '0',
            'title' => $title,
            'start' => $start->format('Y-m-d'),
            'type' => $types[$type],
        ];

        //tache local
        if ($gcTitle != '') $result['gc_title'] = $gcTitle;
        if ($dossiers != '') $result['dossiers'] = $dossiers;
        if ($originalTitle != '') $result['original_title'] = $originalTitle;

        //tache google agenda
        if ($id != '') $result['id'] = $id;
        if ($color != '') $result['color'] = $color;
        if ($textColor != '') $result['textColor'] = $textColor;
        if ($client != '') $result['client'] = $client;

        return $result;
    }

    public static function Tache3(
        Dossier $dossier = null,
        TachesSynchro $tachesSynchro = null,
        $title,\DateTime $start,$type,
        $gcTitle = '',$dossiers = '',$originalTitle = '',
        $id = '', $color = '', $textColor = '', $client = '', $isDepasser = null
    ){
        $types = ['local','gcal'];
        $result =
            [
                'dossier' => ($dossier) ? $dossier->getId() : 0,
                'tachesSynchro' => ($tachesSynchro) ? $tachesSynchro->getId() : '0',
                'title' => $title,
                'start' => $start->format('Y-m-d'),
                'type' => $types[$type],
            ];

        //tache local
        if ($gcTitle != '') $result['gc_title'] = $gcTitle;
        if ($dossiers != '') $result['dossiers'] = $dossiers;
        if ($originalTitle != '') $result['original_title'] = $originalTitle;

        $iconColor = $color;

        $result['icon_color'] = $iconColor;

        $textColor = '#313131';
        $color = '#FFFFFF';

        //tache google agenda
        if ($id != '') $result['id'] = $id;
        if ($color != '') $result['color'] = $color;
        if ($textColor != '') $result['textColor'] = $textColor;
        if ($client != '') $result['client'] = $client;
        if ($isDepasser != null) $result['depasser'] = $isDepasser;

        return $result;
    }
}