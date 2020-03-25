<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 04/09/2018
 * Time: 11:25
 */

namespace TacheBundle\Controller;

class colModel
{
    /**
     * @param $name
     * @param bool $editable
     * @param int $width
     * @param string $classes
     * @param string $formatter
     * @param string $align
     * @param bool $disabled
     * @param bool $sortable
     * @return object
     */
    public static function getModel($name, $editable = false, $width = 50, $classes = '', $formatter = '',$align = 'center', $disabled = true, $sortable = false)
    {
        return (object)
        [
            'name' => $name,
            'index' => $name,
            'editable' => $editable,
            'width' => $width,
            'classes' => $classes,
            'formatter' => $formatter,
            'align' => $align,
            'formatoptions'=>(object)['disabled'=>$disabled],
            'sortable' => $sortable
        ];
    }

    /**
     * @param $startColumnName
     * @param int $count
     * @param string $titleText
     * @param string $align
     * @return object
     */
    public static function getGroupModel($startColumnName, $count = 1, $titleText = '', $align = 'center')
    {
        return (object)
        [
            'startColumnName' => $startColumnName,
            'numberOfColumns' => $count,
            'titleText' => '<strong>'.$titleText.'</strong>',
            'align' => $align
        ];
    }
}