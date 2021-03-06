<?php

declare(strict_types=1);

/**
 * Resource Booking Module for Contao CMS
 * Copyright (c) 2008-2020 Marko Cupic
 * @package resource-booking-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2020
 * @link https://github.com/markocupic/resource-booking-bundle
 */

namespace Markocupic\ResourceBookingBundle\Session\Attribute;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\FrontendUser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ArrayAttributeBag
 * @package Markocupic\ResourceBookingBundle\Session\Attribute
 */
class ArrayAttributeBag extends AttributeBag implements \ArrayAccess
{
    /** @var ContaoFramework */
    private $framework;

    /** @var RequestStack */
    private $requestStack;

    /** @var SessionInterface */
    private $session;

    /** @var Security */
    private $security;

    /**
     * ArrayAttributeBag constructor.
     * @param ContaoFramework $framework
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     * @param Security $security
     * @param string $storageKey
     */
    public function __construct(ContaoFramework $framework, RequestStack $requestStack, SessionInterface $session, Security $security, string $storageKey = '_sf2_attributes')
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->security = $security;

        parent::__construct($storageKey);
    }

    /**
     * @param mixed $key
     * @return bool
     * @throws \Exception
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function &offsetGet($key)
    {
        return $this->attributes[$key];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * @param mixed $key
     * @throws \Exception
     */
    public function offsetUnset($key): void
    {
        $this->remove($key);
    }

    /**
     * @param $key
     * @return bool
     * @throws \Exception
     */
    public function has($key)
    {
        $sessKey = $this->getSessionBagSubkey();
        $arrSession = parent::get($sessKey, []);
        return isset($arrSession[$key]) ? true : false;
    }

    /**
     * @param $key
     * @param null $mixed
     * @return mixed|null
     * @throws \Exception
     */
    public function get($key, $mixed = null)
    {
        $sessKey = $this->getSessionBagSubkey();
        $arrSession = parent::get($sessKey, []);
        return isset($arrSession[$key]) ? $arrSession[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     * @throws \Exception
     */
    public function set($key, $value)
    {
        $sessKey = $this->getSessionBagSubkey();
        $arrSession = parent::get($sessKey, []);
        $arrSession[$key] = $value;

        return parent::set($sessKey, $arrSession);
    }

    /**
     * @param array $arrAttributes
     * @throws \Exception
     */
    public function replace(array $arrAttributes)
    {
        $sessKey = $this->getSessionBagSubkey();
        $arrSession = parent::get($sessKey, []);
        $arrNew = array_merge($arrSession, $arrAttributes);
        parent::set($sessKey, $arrNew);
    }

    /**
     * @param $key
     * @return mixed|null|void
     * @throws \Exception
     */
    public function remove($key)
    {
        $sessKey = $this->getSessionBagSubkey();
        $arrSession = parent::get($sessKey, []);
        if (isset($arrSession[$key]))
        {
            unset($arrSession[$key]);
            parent::set($sessKey, $arrSession);
        }
    }

    /**
     * @return array|mixed|void
     * @throws \Exception
     */
    public function clear()
    {
        $sessKey = $this->getSessionBagSubkey();
        $arrSessionAll = parent::all();

        if (isset($arrSessionAll[$sessKey]))
        {
            unset($arrSessionAll[$sessKey]);
            foreach ($arrSessionAll as $k => $v)
            {
                parent::set($k, $v);
            }
        }
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function count()
    {
        $sessKey = $this->getSessionBagSubkey();
        $arrSessionAll = parent::all();

        if (isset($arrSessionAll[$sessKey]) && is_array($arrSessionAll))
        {
            return count($arrSessionAll[$sessKey]);
        }
        return 0;
    }

    /**
     * @return string
     */
    private function getSessionBagSubkey()
    {
        /** @var Environment $environmentAdapter */
        $environmentAdapter = $this->framework->getAdapter(Environment::class);

        // Add session id to url

        /**
         * The module key is necessary to run several rbb applications on the same page
         * and is sent as a post parameter in every xhr request
         *
         * The module key (#moduleId_#moduleIndex f.ex. 33_2) contains the module id and the module index
         * The module index is 1, if the current module is the first rbb module on the current page
         * The module index is 2, if the current module is the first rbb module on the current page, etc.
         *
         */
        $moduleKey = '';
        $sessionId = '';
        $userId = '';
        if ($this->session->isStarted())
        {
            $sessionId = $this->session->getId();
        }

        if ($this->security->getUser() instanceof FrontendUser)
        {
            /** @var FrontendUser $objUser */
            $objUser = $this->security->getUser();
            if ($objUser->id > 0)
            {
                $userId = $objUser->id;
            }
        }

        if ($environmentAdapter->get('isAjaxRequest'))
        {
            $moduleKey = $this->requestStack->getCurrentRequest()->request->get('moduleKey');
        }
        else
        {
            if (isset($GLOBALS['rbb_moduleKey']))
            {
                $moduleKey = $GLOBALS['rbb_moduleKey'];
            }
        }

        return sha1($sessionId . '_' . $userId . '_' . $moduleKey);
    }

}
