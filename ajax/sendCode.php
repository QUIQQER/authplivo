<?php

/**
 *
 * @param string $phone
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_authplivo_ajax_sendCode',
    function ($phone) {
        QUI\Plivo\Plivo::sendAuthCode($phone);
    },
    ['phone']
);
