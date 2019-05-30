<?php

/**
 * This file contains QUI\Plivo\Plivo
 */

namespace QUI\Plivo;

use QUI;
use Plivo\RestClient;

/**
 * Class Plivo
 * - main plivo class
 * - this class provides the sending methods
 *
 * @package QUI\Plivo
 */
class Plivo
{
    /**
     * @var null
     */
    protected static $Client = null;

    /**
     * Return the main table
     *
     * @return string
     */
    public static function table()
    {
        return QUI::getDBTableName('quiqqer_auth_plivo');
    }

    /**
     * Return the Plivo client
     *
     * @return RestClient|null
     * @throws QUI\Exception
     */
    public static function getClient()
    {
        if (self::$Client !== null) {
            return self::$Client;
        }

        $Config = QUI::getPackage('quiqqer/authplivo')->getConfig();

        if ($Config->getValue('general', 'useSandbox')) {
            self::$Client = new RestClient(
                $Config->getValue('sandboxSettings', 'authId'),
                $Config->getValue('sandboxSettings', 'authToken')
            );
        } else {
            self::$Client = new RestClient(
                $Config->getValue('apiSettings', 'authId'),
                $Config->getValue('apiSettings', 'authToken')
            );
        }

        return self::$Client;
    }

    /**
     * Return the auth code for the phone number
     *
     * @param string|integer $phoneNumber
     * @return string
     *
     * @throws Exception
     */
    public static function getAuthCode($phoneNumber)
    {
        try {
            $encrypt = QUI\Security\Encryption::encrypt($phoneNumber);

            $result = QUI::getDataBase()->fetch([
                'from'  => self::table(),
                'where' => [
                    'phone' => $encrypt
                ]
            ]);

            if (isset($result[0])) {
                return QUI\Security\Encryption::decrypt($result[0]['code']);
            }
        } catch (QUI\Exception $Exception) {
            throw new Exception([
                'quiqqer/authplivo',
                'exception.something.wrong'
            ]);
        }

        // create new code

        try {
            // @todo length as setting
            $newCode = QUI\Security\Password::generateRandom(6);
            $newCode = \strtoupper($newCode);

            QUI::getDataBase()->insert(self::table(), [
                'phone' => $encrypt,
                'code'  => QUI\Security\Encryption::encrypt($newCode)
            ]);

            return $newCode;
        } catch (QUI\Exception $Exception) {
            throw new Exception([
                'quiqqer/authplivo',
                'exception.something.wrong'
            ]);
        }
    }

    /**
     * Send the auth code to the phone number
     * Generate a new auth code if no code for this phone number exists
     *
     * @param string|int $phoneNumber
     * @throws Exception
     */
    public static function sendAuthCode($phoneNumber)
    {
        $message = QUI::getLocale()->get('quiqqer/authplivo', 'auth.message', [
            'code' => self::getAuthCode($phoneNumber)
        ]);

        self::sendSMS($phoneNumber, $message);
    }

    /**
     * Send a sms
     *
     * @param string|int $phoneNumber
     * @param string $message
     * @return mixed
     *
     * @throws Exception
     */
    public static function sendSMS($phoneNumber, $message)
    {
        try {
            $Config       = QUI::getPackage('quiqqer/authplivo')->getConfig();
            $sourceNumber = $Config->getValue('general', 'mainPhoneNo');
            $sourceNumber = self::cleanupPhoneNumber($sourceNumber);

            $Client = self::getClient();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::addError($Exception->getMessage());

            throw new Exception(
                ['quiqqer/authplivo', 'exception.something.wrong'],
                404
            );
        }

        return $Client->messages->create(
            $sourceNumber,
            [$phoneNumber],
            $message
        );
    }

    /**
     * Return a cleaned phone number
     *
     * @param string $number
     * @return string
     */
    public static function cleanupPhoneNumber($number)
    {
        $number = \str_replace(['+', ' '], '', $number);
        $number = \trim($number);

        return $number;
    }
}
