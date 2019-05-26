/**
 * @module package/quiqqer/authplivo/bin/frontend/controls/Registrar
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/authplivo/bin/frontend/controls/Registrar', [

    'qui/controls/Control',
    'qui/controls/loader/Loader',

    'Ajax',
    'Locale',

    'css!package/quiqqer/authplivo/bin/frontend/controls/Registrar.css'

], function (QUIControl, QUILoader, QUIAjax, QUILocale) {
    "use strict";

    var lg = 'quiqqer/authplivo';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/authplivo/bin/frontend/controls/Registrar',

        Binds: [
            '$onImport'
        ],

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {


        }
    });
});