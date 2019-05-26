<?php

/**
 * This file contains QUI\Auth\Plivo\Auth
 */

namespace QUI\Auth\Plivo;

use QUI;
use QUI\Users\AbstractAuthenticator;
use QUI\Users\User;

/**
 * Class Auth
 *
 * Authentication handler for Plivo authentication
 *
 * @package QUI\Auth\Plivo
 */
class Auth extends AbstractAuthenticator
{
    /**
     * User that is to be authenticated
     *
     * @var User
     */
    protected $User = null;

    /**
     * Auth Constructor.
     *
     * @param string|array|integer $user - name of the user, or user id
     */
    public function __construct($user = '')
    {
    }

    /**
     * @param null|QUI\Locale $Locale
     * @return string
     */
    public function getTitle($Locale = null)
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authplivo', 'authplivo.title');
    }

    /**
     * @param null|QUI\Locale $Locale
     * @return string
     */
    public function getDescription($Locale = null)
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authplivo', 'authplivo.description');
    }

    /**
     * Authenticate the user
     *
     * @param string|array|integer $authData
     *
     * @throws Exception
     */
    public function auth($authData)
    {
    }

    /**
     * Return the user object
     *
     * @return \QUI\Interfaces\Users\User
     */
    public function getUser()
    {
        return $this->User;
    }
}
