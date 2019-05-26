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
            $sourceNumber = $Config->getValue('generall', 'sourceNumber');

            $Client = self::getClient();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::addError($Exception->getMessage());

            throw new Exception(
                'Could not send message.',
                404
            );
        }

        return $Client->messages->create(
            $sourceNumber,
            [$phoneNumber],
            $message
        );
    }
}
