<?php

/**
 * This file contains QUI\Registration\Plivo\Control
 */

namespace QUI\Registration\Plivo;

use QUI;

/**
 * Class Control
 *
 * @package QUI\Registration\Plivo
 */
class Control extends QUI\Control
{
    /**
     * Control constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // is this control currently 2fa authentication
        // if not, this control shows a username field, too
        $this->setAttribute('is2FA', false);

        $this->addCSSFile(dirname(__FILE__).'/Control.css');
        $this->addCSSClass('quiqqer-authplivo-registrar');
        $this->setJavaScriptControl('package/quiqqer/authplivo/bin/frontend/controls/Registrar');
    }

    /**
     * @return string
     */
    public function getBody()
    {
        try {
            $Engine = QUI::getTemplateManager()->getEngine();
        } catch (QUI\Exception $Exception) {
            return '';
        }

        return $Engine->fetch(dirname(__FILE__).'/Control.html');
    }
}
