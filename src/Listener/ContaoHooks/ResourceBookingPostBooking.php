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

use Contao\Date;
use Contao\FrontendUser;
use Markocupic\ResourceBookingBundle\Ajax\AjaxHandler;
use Model\Collection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResourceBookingPostBooking
 * @package Markocupic\ResourceBookingBundle\Listener\ContaoHooks
 */
class ResourceBookingPostBooking
{

    /**
     * @param Collection $objBookingCollection
     * @param Request $request
     * @param FrontendUser|null $objUser
     * @param AjaxHandler $objAjaxHandler
     */
    public function onPostBooking(Collection $objBookingCollection, Request $request, ?FrontendUser $objUser, AjaxHandler $objAjaxHandler): void
    {
        // For demo usage only
        return;

        /**
        while ($objBookingCollection->next())
        {
            if ($objUser !== null)
            {
                // Send notifications, manipulate database
                // or do some other insane stuff
                $strMessage = sprintf(
                    'Dear %s %s' ."\n". 'You have successfully booked %s on %s from %s to %s.',
                    $objUser->firstname,
                    $objUser->lastname,
                    $objBookingCollection->getRelated('pid')->title,
                    Date::parse('d.m.Y', $objBookingCollection->startTime),
                    Date::parse('H:i', $objBookingCollection->startTime),
                    Date::parse('H:i', $objBookingCollection->endTime)
                );
                mail(
                    $objUser->email,
                    utf8_decode((string) $objBookingCollection->title),
                    utf8_decode((string) $strMessage)
                );
            }
        }
         **/
    }
}
