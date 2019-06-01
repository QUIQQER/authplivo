<?php

/**
 * This file contains QUI\Registration\Plivo\Registrar
 */

namespace QUI\Registration\Plivo;

use QUI;
use QUI\FrontendUsers;

/**
 * Class Email\Registrar
 *
 * Registration via e-mail address
 *
 * @package QUI\Registration\Google
 */
class Registrar extends FrontendUsers\AbstractRegistrar
{
    /**
     * Registrar constructor.
     */
    public function __construct()
    {
        $this->setAttribute('icon-css-class', 'plivo-registrar');
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getAttribute('username');
    }

    /**
     * Get all invalid registration form fields
     *
     * @return QUI\FrontendUsers\InvalidFormField[]
     */
    public function getInvalidFields()
    {
        // Registration via Plivo does not use form fields
        return [];
    }

    /**
     * @throws FrontendUsers\Exception
     */
    public function validate()
    {
        $username = $this->getAttribute('username');
        $phone    = $this->getAttribute('phone');
        $code     = $this->getAttribute('code');

        // check username, if no 2FA
        // @todo
        if ($username) {
            if (QUI::getUsers()->usernameExists($username)) {
                throw new FrontendUsers\Exception([
                    'quiqqer/authplivo',
                    'exception.username_already_exists'
                ]);
            }
        }

        if (empty($phone) || empty($code)) {
            throw new FrontendUsers\Exception([
                'quiqqer/authplivo',
                'exception.invalid'
            ]);
        }

        try {
            QUI\Plivo\Plivo::validate($phone, $code);
        } catch (\Exception $Exception) {
            throw new FrontendUsers\Exception([
                'quiqqer/authplivo',
                'exception.invalid'
            ]);
        }
    }

    /**
     * @param QUI\Interfaces\Users\User $User
     * @return void
     *
     * @throws FrontendUsers\Exception
     */
    public function onRegistered(QUI\Interfaces\Users\User $User)
    {
        try {
            $phone = $this->getAttribute('phone');
            $code  = $this->getAttribute('code');

            $phone = QUI\Security\Encryption::encrypt($phone);
            $code  = QUI\Security\Encryption::encrypt($code);

            QUI::getDataBase()->update(
                QUI\Plivo\Plivo::table(),
                [
                    'userId' => $User->getId()
                ],
                [
                    'phone' => $phone,
                    'code'  => $code
                ]
            );
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::addError($Exception->getMessage());

            throw new FrontendUsers\Exception([
                'quiqqer/authplivo',
                'exception.invalid'
            ]);
        }
    }

    /**
     * Get title
     *
     * @param QUI\Locale $Locale (optional) - If omitted use QUI::getLocale()
     * @return string
     */
    public function getTitle($Locale = null)
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authplivo', 'registrar.title');
    }

    /**
     * Get description
     *
     * @param QUI\Locale $Locale (optional) - If omitted use QUI::getLocale()
     * @return string
     */
    public function getDescription($Locale = null)
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/authplivo', 'registrar.description');
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'fa fa-phone';
    }

    /**
     * Check if this Registrar can send passwords
     *
     * @return bool
     */
    public function canSendPassword()
    {
        return false;
    }

    /**
     * @return Control
     */
    public function getControl()
    {
        return new Control();
    }
}
