/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Estimateddelivery
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

require([
    'jquery',
    'jquery/ui',
    'mage/adminhtml/events',
    'mage/backend/tabs',
    'domReady!'
], function($) {
    'use strict';

    var types = {
        'delivery': 1,
        'shipping': 1
    };

    var fields = {
        'enable': 1,
        'days_from': 1,
        'days_to': 1,
        'date_from': 1,
        'date_to': 1,
        'text': 1
    };

    window.initedRefreshEstimatedData = false;

    $('#category-edit-container #category_info_tabs, #product_info_tabs').on('tabscreate tabsactivate', function() {
        if (window.initedRefreshEstimatedData) {
            return;
        }
        window.initedRefreshEstimatedData = true;

        (function refreshEstimatedData()
        {
            var obj = function(_type, param) {
                return $('[id$="estimated_' + _type + '_' + param+'"]');
            }

            var tr = function(_type, param)
            {
                return obj(_type, param).parents('.control').parent();
            }

            for (var t in types) {
                obj(t, 'enable')
                    .data('est_type', t)
                    .change(function() {
                        var $this = $(this);
                        var _type = $this.data('est_type');

                        for (var k in fields) {
                            tr(_type, k).hide(0);

                            tr(_type, k).find('.ui-datepicker-trigger').each(function() {
                                var $btn = $(this);
                                $btn.attr('id', $btn.prev('.hasDatepicker').attr('id') + '_datepicker');
                            });
                        }
                        tr(_type, 'enable').show(0);
                        $('.'+ _type +'_est_del').remove();

                        switch (parseInt($this.val(), 10)) {
                            case 0:
                            case 1:
                                // nothing to do
                                break;
                            case 2:
                                obj(_type, 'days_from').removeAttr('style');
                                obj(_type, 'days_to').removeAttr('style');
                                var $master_tr = tr(_type, 'days_from').show(0);

                                var $master = $master_tr.next().find('.control:first');
                                obj(_type, 'days_to').appendTo($master);
                                break;
                            case 3:
                                tr(_type, 'days_from').show(0);

                                var $master = obj(_type, 'days_from').attr('style', 'width: 45% !important;');
                                obj(_type, 'days_to').insertAfter($master).attr('style', 'width: 45% !important;');
                                $('<span class="est_del '+ _type +'_est_del"> — </span>').insertAfter($master);
                                break;

                            case 4:
                                var $master_tr = tr(_type, 'date_from').show(0);

                                var $master = $master_tr.next().find('.control:first');
                                obj(_type, 'date_to').appendTo($master);
                                obj(_type, 'date_to_datepicker').appendTo($master);
                                break;

                            case 5:
                                tr(_type, 'date_from').show(0);

                                var $master = obj(_type, 'date_from').parent();
                                $('<span class="est_del '+ _type +'_est_del" style="margin-left: 40px; margin-right: 5px;"> — </span>').appendTo($master);
                                obj(_type, 'date_to').appendTo($master);
                                obj(_type, 'date_to_datepicker').appendTo($master);
                                break;

                            case 6:
                                tr(_type, 'text').show(0);
                                break;
                        }
                    })
                    .change();
            }
        })();
    });



    $('body.catalog-category-edit .entry-edit, body.catalog-product-edit .entry-edit, body.catalog-category-add .entry-edit, body.catalog-product-new .entry-edit').on('click', '.fieldset-wrapper .fieldset-wrapper-title', function() {
        if (window.initedRefreshEstimatedData) {
            return;
        }
        window.initedRefreshEstimatedData = true;

        function refreshEstimatedData()
        {
            var obj = function(_type, param) {
                var obj = $('[name="estimated_' + _type + '_' + param+'"]');
                if (!obj.length) {
                    obj = $('[name="product[estimated_' + _type + '_' + param+']"]');
                }
                return obj;
            }


            var tr = function(_type, param)
            {
                return obj(_type, param).parents('.admin__field');
            }

            for (var t in types) {
                obj(t, 'enable')
                    .data('est_type', t)
                    .change(function() {
                        var $this = $(this);
                        var _type = $this.data('est_type');
                        for (var k in fields) {
                            tr(_type, k).hide(0);
                        }
                        tr(_type, 'enable').show(0);
                        $('.'+ _type +'_est_del').remove();

                        switch (parseInt($this.val(), 10)) {
                            case 0:
                            case 1:
                                // nothing to do
                                break;
                            case 2:
                                tr(_type, 'days_from').show(0);
                                break;
                            case 3:
                                tr(_type, 'days_from').show(0);
                                tr(_type, 'days_to').show(0);

                                var $master = obj(_type, 'days_from');
                                $('<span class="est_del '+ _type +'_est_del"> — </span>').insertAfter($master);
                                break;

                            case 4:
                                var $master_tr = tr(_type, 'date_from').show(0);
                                break;

                            case 5:
                                tr(_type, 'date_from').show(0);
                                tr(_type, 'date_to').show(0);

                                var $master = obj(_type, 'date_from');
                                $('<span class="est_del '+ _type +'_est_del"> — </span>').insertAfter($master.find('+ button.ui-datepicker-trigger'));
                                break;

                            case 6:
                                tr(_type, 'text').show(0);
                                break;
                        }
                    })
                    .change();
            }
        }

        /*Fix for category and product page, hide delivery types*/
        var prTimeout = setInterval(function(){
            var categoryProduct = $('[name*="estimated_delivery_enable"]');
            var date = $('[name*="estimated_delivery_date_from"]');
            if(categoryProduct.length && date.length){
                clearInterval(prTimeout);
                refreshEstimatedData();
            }
        }, 100);

    });
});
