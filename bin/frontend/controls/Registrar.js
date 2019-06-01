/**
 * @module package/quiqqer/authplivo/bin/frontend/controls/Registrar
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/authplivo/bin/frontend/controls/Registrar', [

    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'package/quiqqer/authplivo/bin/frontend/controls/phoneData',

    'Ajax',
    'Locale',

    'css!package/quiqqer/authplivo/bin/frontend/controls/Registrar.css'

], function (QUIControl, QUILoader, phoneData, QUIAjax, QUILocale) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/authplivo/bin/frontend/controls/Registrar',

        Binds: [
            '$onImport',
            '$countryClick',
            '$countrySelect',
            '$sendAuthCode'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Input    = null;
            this.$Username = null;

            this.$CountrySelect   = null;
            this.$CountryDropDown = null;
            this.$SendSMS         = null;

            this.$SectionGenerate = null;
            this.$SectionAuth     = null;

            this.$current = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event: on import
         */
        $onImport: function () {
            this.$Input    = this.getElm().getElement('[name="phone"]');
            this.$Username = this.getElm().getElement('[name="username"]');

            this.$SectionGenerate = this.getElm().getElement('.quiqqer-authplivo-registrar-form-getCode');
            this.$SectionAuth     = this.getElm().getElement('.quiqqer-authplivo-registrar-form-auth');

            this.$SendSMS = this.getElm().getElement('[name="send-sms"]');
            this.$SendSMS.addEvent('click', this.$sendAuthCode);

            this.$CountrySelect = this.getElm().getElement('.quiqqer-authplivo-registrar-form-country');
            this.$CountrySelect.addEvent('click', this.$countryClick);
        },

        /**
         * event: country select click
         * @param event
         */
        $countryClick: function (event) {
            event.stop();
            this.openCountrySelect();
        },

        /**
         * event: click on country select
         *
         * @param event
         */
        $countrySelect: function (event) {
            event.stop();

            var Target = event.target;

            if (!Target.hasClass('quiqqer-authplivo-registrar-country-list-entry')) {
                Target = Target.getParent('.quiqqer-authplivo-registrar-country-list-entry');
            }

            this.selectCountry(Target.get('data-code'));
        },

        /**
         * Opens the country select
         */
        openCountrySelect: function () {
            var position = this.$CountrySelect.getPosition();

            if (!this.$CountryDropDown) {
                this.$CountryDropDown = new Element('div', {
                    'class' : 'quiqqer-authplivo-registrar-country-list',
                    events  : {
                        blur: function () {
                            this.closeCountrySelect();
                        }.bind(this)
                    },
                    tabIndex: -1
                }).inject(document.body);

                var i, len, entry, code, country;

                for (i = 0, len = phoneData.length; i < len; i++) {
                    entry   = phoneData[i];
                    code    = entry[2];
                    country = QUILocale.get('quiqqer/countries', 'country.' + code.toUpperCase());

                    new Element('div', {
                        'data-code' : code,
                        'data-phone': entry[3],
                        'class'     : 'quiqqer-authplivo-registrar-country-list-entry',
                        html        : '' +
                            '<span class="quiqqer-authplivo-registrar-country-list-entry-flag">' +
                            '   ' + this.$getCountryFlag(code, country) +
                            '</span>' +
                            '<span class="quiqqer-authplivo-registrar-country-list-entry-title">' +
                            '   ' + country + ' (+' + entry[3] + ')' +
                            '</span>',
                        events      : {
                            click: this.$countrySelect
                        }
                    }).inject(this.$CountryDropDown);
                }
            }

            this.$CountryDropDown.setStyles({
                display: null,
                opacity: null,
                left   : position.x + 5,
                top    : position.y + 40
            });

            this.$CountryDropDown.focus();
        },

        /**
         * close the country select
         */
        closeCountrySelect: function () {
            this.$CountryDropDown.setStyles({
                display: 'none',
                opacity: 0
            });
        },

        /**
         * Select a new country
         *
         * @param {String} code
         */
        selectCountry: function (code) {
            var i, len;
            var entry = null;

            for (i = 0, len = phoneData.length; i < len; i++) {
                if (code === phoneData[i][2]) {
                    entry = phoneData[i];
                    break;
                }
            }

            var current      = this.$current;
            var currentValue = this.$Input.value;

            if (current) {
                currentValue = currentValue.replace('\+' + current[3], '');
                currentValue = currentValue.trim();
            }

            this.$current     = entry;
            this.$Input.value = '+' + entry[3] + ' ' + currentValue;
            this.$CountrySelect.set('html', this.$getCountryFlag(this.$current[2]));
        },

        /**
         * Return the country flag
         *
         * @param {String} code - country code
         * @param {String} country - country text
         * @return {string}
         */
        $getCountryFlag: function (code, country) {
            return '<img src="' + URL_BIN_DIR + '16x16/flags/' + code + '.png" alt="' + country + '" />';
        },

        /**
         * event: send auth code
         *
         * @param event
         */
        $sendAuthCode: function (event) {
            if (this.$Input.value === '') {
                if (typeof this.$Input.checkValidity === 'function') {
                    this.$Input.checkValidity();
                }

                return;
            }

            event.stop();

            var self = this;

            this.$SendSMS.set('disabled', true);

            this.send().then(function () {
                moofx(self.$SectionGenerate).animate({
                    left   : -20,
                    opacity: 0
                }, {
                    duration: 300,
                    callback: function () {
                        self.$SendSMS.set('disabled', false);
                        self.$SectionGenerate.setStyles({
                            display: 'none'
                        });


                        self.$SectionAuth.setStyle('left', -20);
                        self.$SectionAuth.setStyle('opacity', 0);
                        self.$SectionAuth.setStyle('display', null);

                        moofx(self.$SectionAuth).animate({
                            left   : 0,
                            opacity: 1
                        }, {
                            duration: 300,
                            callback: function () {

                            }
                        });
                    }
                });
            });
        },

        /**
         *
         * @return {Promise}
         */
        send: function () {
            var self = this;

            return new Promise(function (resolve) {
                QUIAjax.post('package_quiqqer_authplivo_ajax_sendCode', resolve, {
                    'package': 'quiqqer/authplivo',
                    phone    : self.$Input.value
                });
            });
        }
    });
});
