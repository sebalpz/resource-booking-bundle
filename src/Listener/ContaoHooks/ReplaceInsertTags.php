<?php

declare(strict_types=1);

/**
 * Resource Booking Module for Contao CMS
 * Copyright (c) 2008-2020 Marko Cupic
 * @package resource-booking-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2020
 * @link https://github.com/markocupic/resource-booking-bundle
 */

namespace Markocupic\ResourceBookingBundle\Listener\ContaoHooks;

use Contao\Controller;

/**
 * Class ReplaceInsertTags
 * @package Markocupic\ResourceBookingBundle\Listener\ContaoHooks
 */
class ReplaceInsertTags
{
    /**
     * @param string $strTag
     * @return bool
     */
    public function onReplaceInsertTags(string $strTag)
    {
        Controller::loadLanguageFile('default');
        if (strpos($strTag, 'rbb_lang::') !== false)
        {
            $arrChunk = explode('::', $strTag);
            if (isset($arrChunk[1]))
            {
                if (isset($GLOBALS['TL_LANG']['RBB'][$arrChunk[1]]))
                {
                    return $GLOBALS['TL_LANG']['RBB'][$arrChunk[1]];
                }
                // Search in the default lang file
                if (isset($GLOBALS['TL_LANG']['MSC'][$arrChunk[1]]))
                {
                    return $GLOBALS['TL_LANG']['MSC'][$arrChunk[1]];
                }
            }
        }

        return false;
    }
}
