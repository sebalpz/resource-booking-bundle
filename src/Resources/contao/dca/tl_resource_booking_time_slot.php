<?php

/**
 * Resource Booking Module for Contao CMS
 * Copyright (c) 2008-2020 Marko Cupic
 * @package resource-booking-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2020
 * @link https://github.com/markocupic/resource-booking-bundle
 */

/**
 * Table tl_resource_booking_time_slot
 */
$GLOBALS['TL_DCA']['tl_resource_booking_time_slot'] = [

    // Config
    'config'   => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_resource_booking_time_slot_type',
        'enableVersioning'  => true,
        'sql'               => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ]
        ],
        'ondelete_callback' => [['tl_resource_booking_time_slot', 'removeChildRecords']],
        'onsubmit_callback' => [['tl_resource_booking_time_slot', 'adaptBookingsStartAndEndtime']],

    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['sorting'],
            'panelLayout'           => 'filter;search,limit',
            'headerFields'          => ['title'],
            'child_record_callback' => ['tl_resource_booking_time_slot', 'childRecordCallback']
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ],
            'copy'   => [
                'label'      => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['copy'],
                'href'       => 'act=paste&amp;mode=copy',
                'icon'       => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ],
            'cut'    => [
                'label'      => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label'                => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['toggle'],
                'attributes'           => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'haste_ajax_operation' => [
                    'field'   => 'published',
                    'options' => [
                        [
                            'value' => '',
                            'icon'  => 'invisible.svg'
                        ],
                        [
                            'value' => '1',
                            'icon'  => 'visible.svg'
                        ]
                    ]
                ]
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ]
        ]
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,description;{time_legend},startTime,endTime;{expert_legend:hide},cssID',
    ],

    // Fields
    'fields'   => [
        'id'          => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'         => [
            'foreignKey' => 'tl_resource_booking_time_slot_type.title',
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'      => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'published'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['published'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'flag'      => 2,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'description' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_resource_booking_time_slot']['description'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "mediumtext NULL"
        ],
        'startTime'   => [
            'label'         => &$GLOBALS['TL_LANG']['tl_calendar_events']['startTime'],
            'default'       => time(),
            'exclude'       => true,
            'filter'        => true,
            'sorting'       => true,
            'flag'          => 8,
            'inputType'     => 'text',
            'eval'          => ['rgxp' => 'resourceBookingTime', 'mandatory' => true, 'tl_class' => 'w50'],
            'load_callback' => [
                ['tl_resource_booking_time_slot', 'loadTime']
            ],
            'save_callback' => [
                ['tl_resource_booking_time_slot', 'setCorrectTime']
            ],
            'sql'           => "int(10) unsigned NOT NULL default '0'",
        ],
        'endTime'     => [
            'label'         => &$GLOBALS['TL_LANG']['tl_calendar_events']['endTime'],
            'default'       => time(),
            'exclude'       => true,
            'inputType'     => 'text',
            'eval'          => ['rgxp' => 'resourceBookingTime', 'mandatory' => true, 'tl_class' => 'w50'],
            'load_callback' => [
                ['tl_resource_booking_time_slot', 'loadTime']
            ],
            'save_callback' => [
                ['tl_resource_booking_time_slot', 'setCorrectTime'],
                ['tl_resource_booking_time_slot', 'setCorrectEndTime']
            ],
            'sql'           => "int(10) unsigned NOT NULL default '0'",
        ],
        'cssID'       => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['multiple' => true, 'size' => 2, 'tl_class' => 'w50 clr'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
    ]
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_resource_booking_time_slot extends Contao\Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

    /**
     * @param array $row
     * @return string
     */
    public function childRecordCallback(array $row): string
    {
        return sprintf('<div class="tl_content_left"><span style="color:#999;padding-left:3px">' . $row['title'] . '</span> %s-%s</div>', Markocupic\ResourceBookingBundle\Utc\UtcTimeHelper::parse('H:i', $row['startTime']), Markocupic\ResourceBookingBundle\Utc\UtcTimeHelper::parse('H:i', $row['endTime']));
    }

    /**
     * Load callback for ...
     * tl_resource_booking_time_slot.startTime and
     * tl_resource_booking_time_slot.endTime
     *
     * @param int $timestamp
     * @return string
     */
    public function loadTime(int $timestamp): string
    {
        $strTime = '';
        if ($timestamp >= 0 && is_int($timestamp))
        {
            $strTime = Markocupic\ResourceBookingBundle\Utc\UtcTimeHelper::parse('H:i', $timestamp);
        }

        return $strTime;
    }

    /**
     * Save callback for ...
     * tl_resource_booking_time_slot.startTime and
     * tl_resource_booking_time_slot.endTime
     *
     * Converts formated time f.ex 09:01 into a utc timestamp
     * @param string $strTime
     * @param \Contao\DataContainer $dc
     * @return int
     */
    public function setCorrectTime(string $strTime, Contao\DataContainer $dc): int
    {
        if (preg_match("/^(2[0-3]|[01][0-9]):[0-5][0-9]$/", $strTime))
        {
            $timestamp = Markocupic\ResourceBookingBundle\Utc\UtcTimeHelper::strtotime('1970-01-01 ' . $strTime);
        }
        else
        {
            $timestamp = 0;
        }

        return $timestamp;
    }

    /**
     * Save callback for ...
     * tl_resource_booking_time_slot.endTime
     *
     * Adjust endTime if it is smaller then the startTime
     * @param int $timestamp
     * @param \Contao\DataContainer $dc
     * @return int
     */
    public function setCorrectEndTime(int $timestamp, Contao\DataContainer $dc): int
    {
        // Adjust endTime if it is smaller then the startTime
        if (!empty(Contao\Input::post('startTime')))
        {
            $strStartTime = Contao\Input::post('startTime');
        }
        else
        {
            $strStartTime = $dc->activeRecord->startTime;
        }

        if (!empty($strStartTime))
        {
            $startTime = Markocupic\ResourceBookingBundle\Utc\UtcTimeHelper::strtotime('01-01-1970 ' . $strStartTime);
            if ($startTime !== false)
            {
                if ($timestamp <= $startTime)
                {
                    $timestamp = $startTime + 60;
                }
            }
            else
            {
                $timestamp = 0;
            }
        }
        else
        {
            $timestamp = 0;
        }

        return $timestamp;
    }

    /**
     * ondelete_callback
     * @param \Contao\DataContainer $dc
     */
    public function removeChildRecords(Contao\DataContainer $dc)
    {
        if (!$dc->id)
        {
            return;
        }

        $objBooking = $this->Database->prepare('SELECT id FROM tl_resource_booking WHERE timeSlotId=?')->execute($dc->id);
        if ($objBooking->numRows)
        {
            $arrIdsDel = $objBooking->fetchEach('id');
            // Delete child bookings
            $this->Database->prepare('DELETE FROM tl_resource_booking WHERE timeSlotId=?')->execute($dc->id);
            \Contao\Message::addInfo('Deleted bookings with ids ' . implode(',', $arrIdsDel));
        }
    }

    /**
     * ondelete_callback
     * @param \Contao\DataContainer $dc
     */
    public function adaptBookingsStartAndEndtime($dc)
    {
        $intId = $dc->id;
        if (!$intId)
        {
            return;
        }

        $objSlot = $this->Database->prepare('SELECT * FROM tl_resource_booking_time_slot WHERE id=?')->execute($intId);
        if ($objSlot->numRows)
        {
            $arrAdapted = [];
            $objBooking = $this->Database->prepare('SELECT * FROM tl_resource_booking WHERE timeSlotId=?')->execute($objSlot->id);
            while ($objBooking->next())
            {
                $set = [];
                $arrFields = ['startTime', 'endTime'];
                foreach ($arrFields as $field)
                {
                    $strDateOld = \Contao\Date::parse('Y-m-d H:i', $objBooking->{$field});
                    $arrDateOld = explode(' ', $strDateOld);
                    $strTimeNew = Markocupic\ResourceBookingBundle\Utc\UtcTimeHelper::parse('H:i', $objSlot->{$field});
                    $strDateNew = $arrDateOld[0] . ' ' . $strTimeNew;
                    $set[$field] = strtotime($strDateNew);
                }
                $arrAdapted[] = $objBooking->id;
                $this->Database->prepare('UPDATE tl_resource_booking %s WHERE id=?')
                    ->set($set)
                    ->execute($objBooking->id);
            }
            if (count($arrAdapted))
            {
                \Contao\Message::addInfo('Adapted start- and endtime for booking with ids ' . implode(',', $arrAdapted));
            }
        }
    }

}
