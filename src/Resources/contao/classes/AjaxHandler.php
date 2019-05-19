<?php

/**
 * Resource Booking Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package resource-booking-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/resource-booking-bundle
 */

namespace Markocupic\ResourceBookingBundle;

use Contao\Config;
use Contao\Date;
use Contao\FrontendUser;
use Contao\ResourceBookingModel;
use Contao\ResourceBookingResourceModel;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\JsonResponse;
use Contao\CoreBundle\Exception\RedirectResponseException;

/**
 * Class AjaxHandler
 * @package Markocupic\ResourceBookingBundle
 */
class AjaxHandler
{
    /**
     * @param $objModule
     * @return JsonResponse
     */
    public function fetchDataRequest($objModule)
    {
        $arrJson = array();
        $arrJson['data'] = ResourceBookingHelper::fetchData($objModule);
        $arrJson['status'] = 'success';
        $response = new JsonResponse($arrJson);
        return $response->send();
    }

    /**
     * @param $objModule
     * @return JsonResponse
     */
    public function sendApplyFilterRequest($objModule)
    {
        $arrJson = array();
        $arrJson['data'] = ResourceBookingHelper::fetchData($objModule);
        $arrJson['status'] = 'success';
        $response = new JsonResponse($arrJson);

        return $response->send();
    }

    /**
     * @return JsonResponse
     */
    public function sendBookingRequest($objModule)
    {
        $arrJson = array();
        $arrJson['status'] = 'error';
        $errors = 0;
        $arrBookings = array();
        $intResourceId = Input::post('resourceId');
        $objResource = ResourceBookingResourceModel::findPublishedByPk($intResourceId);
        $arrBookingDateSelection = Input::post('bookingDateSelection');
        $bookingRepeatStopWeekTstamp = Input::post('bookingRepeatStopWeekTstamp');
        $counter = 0;

        if (!FE_USER_LOGGED_IN || $objResource === null || !$bookingRepeatStopWeekTstamp > 0 || !is_array($arrBookingDateSelection))
        {
            $errors++;
            $arrJson['alertError'] = $GLOBALS['TL_LANG']['MSG']['generalBookingError'];
        }

        if (empty($arrBookingDateSelection))
        {
            $errors++;
            $arrJson['alertError'] = $GLOBALS['TL_LANG']['MSG']['selectBookingDatesPlease'];
        }

        if ($errors === 0)
        {
            $objUser = FrontendUser::getInstance();

            // Prepare $arrBookings with the helper method
            $arrBookings = ResourceBookingHelper::prepareBookingSelection($objModule, $objUser, $objResource, $arrBookingDateSelection, $bookingRepeatStopWeekTstamp);

            foreach ($arrBookings as $arrBooking)
            {
                if ($arrBooking['resourceAlreadyBooked'] && $arrBooking['resourceAlreadyBookedByLoggedInUser'] === false)
                {
                    $errors++;
                }
            }

            if ($errors > 0)
            {
                $arrJson['alertError'] = $GLOBALS['TL_LANG']['MSG']['resourceAlreadyBooked'];
            }
            else
            {
                foreach ($arrBookings as $i => $arrBooking)
                {
                    if ($arrBooking['resourceAlreadyBookedByLoggedInUser'] === false)
                    {
                        // Set title
                        $arrBooking['title'] = sprintf('%s : %s %s %s [%s - %s]', $objResource->title, $GLOBALS['TL_LANG']['MSC']['bookingFor'], $objUser->firstname, $objUser->lastname, Date::parse(Config::get('datimFormat'), $arrBooking['startTime']), Date::parse(Config::get('datimFormat'), $arrBooking['endTime']));

                        $objBooking = new ResourceBookingModel();
                        foreach ($arrBooking as $k => $v)
                        {
                            $objBooking->{$k} = $v;
                        }
                        $objBooking->save();
                        $arrBookings[$i]['newEntry'] = true;
                    }
                    $counter++;
                }
                if ($counter === 0)
                {
                    $arrJson['alertError'] = $GLOBALS['TL_LANG']['MSG']['noItemsBooked'];
                }
                else
                {
                    $arrJson['status'] = 'success';
                    $arrJson['alertSuccess'] = sprintf($GLOBALS['TL_LANG']['MSG']['successfullyBookedXItems'], $objResource->title, $counter);
                }
            }
        }
        // Return $arrBookings
        $arrJson['bookingSelection'] = $arrBookings;

        $response = new JsonResponse($arrJson);
        return $response->send();
    }

    /**
     * @return JsonResponse
     */
    public function sendBookingFormValidationRequest($objModule)
    {
        $arrJson = array();
        $arrJson['status'] = 'error';
        $arrJson['bookingFormValidation'] = array(
            'noDatesSelected'         => false,
            'resourceIsAlreadyBooked' => false,
            'passedValidation'        => false,
        );

        $errors = 0;
        $counter = 0;
        $blnBookingsPossible = true;
        $arrBookings = array();
        $intResourceId = Input::post('resourceId');
        $objResource = ResourceBookingResourceModel::findPublishedByPk($intResourceId);
        $arrBookingDateSelection = Input::post('bookingDateSelection');
        $bookingRepeatStopWeekTstamp = Input::post('bookingRepeatStopWeekTstamp');

        if (!FE_USER_LOGGED_IN || $objResource === null || !$bookingRepeatStopWeekTstamp > 0)
        {
            $errors++;
            $arrJson['alertError'] = $GLOBALS['TL_LANG']['MSG']['generalBookingError'];
        }

        if ($errors === 0)
        {
            $objUser = FrontendUser::getInstance();

            // Prepare $arrBookings with the helper method
            $arrBookings = ResourceBookingHelper::prepareBookingSelection($objModule, $objUser, $objResource, $arrBookingDateSelection, $bookingRepeatStopWeekTstamp);

            foreach ($arrBookings as $arrBooking)
            {
                if ($arrBooking['resourceAlreadyBooked'] === true && $arrBooking['resourceAlreadyBookedByLoggedInUser'] === false)
                {
                    $blnBookingsPossible = false;
                }
                $counter++;
            }

            if ($counter === 0)
            {
                $arrJson['bookingFormValidation']['passedValidation'] = false;
                $arrJson['bookingFormValidation']['noDatesSelected'] = true;
            }
            elseif (!$blnBookingsPossible)
            {
                $arrJson['bookingFormValidation']['passedValidation'] = false;
                $arrJson['bookingFormValidation']['resourceIsAlreadyBooked'] = true;
            }
            else // All ok!
            {
                $arrJson['bookingFormValidation']['passedValidation'] = true;
            }
        }

        // Return $arrBookings
        $arrJson['bookingFormValidation']['bookingSelection'] = $arrBookings;

        $arrReturn = array('data' => $arrJson['bookingFormValidation'], 'status' => 'success');
        $response = new JsonResponse($arrReturn);
        return $response->send();
    }

    /**
     * @return JsonResponse
     */
    public function sendCancelBookingRequest()
    {
        $arrJson = array();
        $arrJson['status'] = 'error';
        if (FE_USER_LOGGED_IN && Input::post('bookingId') > 0)
        {
            $objUser = FrontendUser::getInstance();
            $bookingId = Input::post('bookingId');
            $objBooking = ResourceBookingModel::findByPk($bookingId);
            if ($objBooking !== null)
            {
                if ($objBooking->member === $objUser->id)
                {
                    $objBooking->delete();
                    $arrJson['status'] = 'success';
                    $arrJson['alertSuccess'] = $GLOBALS['TL_LANG']['MSG']['successfullyCanceledBooking'];
                }
                else
                {
                    $arrJson['alertError'] = $GLOBALS['TL_LANG']['MSG']['notAllowedToCancelBooking'];
                }
            }
            else
            {
                $arrJson['alertError'] = $GLOBALS['TL_LANG']['MSG']['notAllowedToCancelBooking'];
            }
        }

        $response = new JsonResponse($arrJson);
        return $response->send();
    }

    /**
     * @return JsonResponse
     */
    public function sendIsOnlineRequest()
    {
        $arrJson = array();
        $arrJson['status'] = 'success';
        $arrJson['isOnline'] = 'true';
        $response = new JsonResponse($arrJson);
        return $response->send();
    }

    /**
     * Logout user
     */
    public static function sendLogoutRequest()
    {
        // Unset session
        unset($_SESSION['rbb']);

        // Unset cookie
        $cookie_name = 'PHPSESSID';
        unset($_COOKIE[$cookie_name]);
        // Empty value and expiration one hour before
        $res = setcookie($cookie_name, '', time() - 3600);
        // Logout user
        throw new RedirectResponseException(System::getContainer()->get('security.logout_url_generator')->getLogoutUrl());
    }


}
