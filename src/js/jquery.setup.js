/**
 * Part of 'alexbusu/php-js' package
 *
 * @link https://github.com/alexbusu/php-js
 * @author Alexandru Busuioc (busuioc.alexandru@gmail.com)
 *
 * @licence MIT
 */
(function ($) {

    'use strict';

    /**
     * Method that fires at every received 200 OK Response from server
     * @param {*} data
     */
    function ajax200(data) {
        // `this` is the ajax (options) object
        switch (this['dataType']) {
            case 'json' :
                if (data['_'] && data['_'].length) {
                    triggers(data['_']);
                }
                break;
        }

    }

    /**
     * A function that performs minimal functionality of event list parsing and triggering
     * @param data
     * @returns {*}
     */
    function triggers(data) {

        var $scope = this.length ? this : $(document);

        for (var _call in data) {
            if (data.hasOwnProperty(_call)) {
                var evtName, evtScope, evtData;
                if (/^\d+$/.test(_call)) {
                    // the array key is numeric, it means we should have the trigger details in 'data' parameter
                    evtName = data[_call]['trigger'];
                    evtScope = $(data[_call]['selector'] || document);
                    evtData = data[_call]['data'];
                } else {
                    // the array key is the name of the event to trigger; in this case '$scope' is the trigger scope
                    evtName = _call;
                    evtScope = $scope;
                    evtData = data[_call];
                }
                evtScope.trigger(evtName, evtData.hasOwnProperty('length') ? [evtData] : evtData);
            }
        }

        return $scope;

    }

    $.ajaxSetup({ // http://api.jquery.com/jQuery.ajax/
        statusCode: {
            200: ajax200
        }
    });

    // basic client-side functionality
    $(function () { // on DOC ready
        $(document)
            .on('winrd.triggers', function (e, data) {
                setTimeout(function () {
                    window.location = data['url'];
                }, data['timeout'] * 1000);
            })
            .on('winreload.triggers', function (e, timeout) {
                timeout = timeout || 0;
                setTimeout(function () {
                    window.location.reload();
                }, timeout * 1000);
            })
            .on('warn.console.triggers table.console.triggers info.console.triggers log.console.triggers error.console.triggers', function (e, data) {
                switch (e.type) {
                    case 'error' :
                        if ('message' in data) {
                            console.error(data['message']);
                            console.warn(data['trace']);
                        } else {
                            console.error(data);
                        }
                        break;
                    case 'warn' :
                        console.warn(data);
                        break;
                    case 'table' :
                        console.table(data);
                        break;
                    case 'info' :
                        console.info(data);
                        break;
                    case 'log' :
                        console.log(data);
                        break;
                    default:
                        console.error(e, data);
                }
            })
        ;
    });

})(jQuery);
