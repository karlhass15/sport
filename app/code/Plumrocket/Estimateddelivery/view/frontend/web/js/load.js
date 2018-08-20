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

define(
    [
        'jquery',
        'uiComponent',
        'mage/storage',
        'ko',
        'uiRegistry',
        'moment',
        'mage/translate'
    ],
    function (
        $,
        Component,
        storage,
        ko,
        reg,
        moment
    ) {
        'use strict';

        var INHERITED = '0';
        var DO_NOT_SHOW = '1';
        var DYNAMIC_DATE = '2';
        var DYNAMIC_DATE_RANGE = '3';
        var STATIC_DATE = '4';
        var STATIC_DATE_RANGE = '5';
        var STATIC_TEXT = '6';
        var EMPTY = '7';

        var self = null;
        var deliveryDates = new Array();
        var shippingDates = new Array();
        var calcServerTime = null;

        return function(options, element) {

            return Component.extend({
                defaults: options,
                estimatedDeliveryLabel: ko.observable(
                    $.mage.__('Estimated Delivery Date')
                ),
                estimatedShippingLabel: ko.observable(
                    $.mage.__('Estimated Shipping Date')
                ),
                estimatedDelivery: ko.observable(''),
                estimatedShipping: ko.observable(''),
                initialize: function() {
                    self = this;
                    this._super();
                    self.initDataForRender();
                    return this;
                },

                initDataForRender: function() {
                    var differentTime = self.getFromLocalStorage('differentTime');
                    var timeCheckedAt = self.getFromLocalStorage('timeCheckedAt');
                    calcServerTime = self.getFromLocalStorage('calcServerTime');
                    var browserTime = new Date().getTime();
                    if (
                      (typeof differentTime == 'undefined') ||
                      (typeof calcServerTime == 'undefined') ||
                      (typeof timeCheckedAt == 'undefined') ||
                      ((timeCheckedAt + 3600000) <  browserTime)
                    ) {
                        storage.post(
                            options.url,
                            ''
                        ).done(function(response) {
                              differentTime = browserTime - new Date(response).getTime();
                              //differentTime = Math.ceil(differentTime / 60000 / 60);
                              calcServerTime = Math.abs(differentTime - browserTime);
                              self.saveToLocalStorage('differentTime', differentTime);
                              self.saveToLocalStorage('timeCheckedAt', browserTime);
                              self.saveToLocalStorage('calcServerTime', calcServerTime);
                              self.renderEstimatedDate();
                        }).fail(function() {
                        });
                    } else {
                        self.renderEstimatedDate();
                    }
                },

                saveToLocalStorage: function(key, data) {
                    reg.get('localStorage').set(key, data);
                },

                getFromLocalStorage: function(key) {
                    return reg.get('localStorage').get(key);
                },

                renderEstimatedDate: function() {
                    var estimatedData = JSON.parse($("#estimated_hiden_box").text());
                    var parentProductId = $("#product_addtocart_form input[name='product']").val();
                    self.getEstimatedData(estimatedData);
                    if (parentProductId) {
                        if (deliveryDates[parentProductId] == INHERITED) {
                            self.estimatedDelivery("");
                        } else {
                            self.estimatedDelivery(deliveryDates[parentProductId]);
                        }
                        if (shippingDates[parentProductId] == INHERITED) {
                            self.estimatedShipping("");
                        } else {
                            self.estimatedShipping(shippingDates[parentProductId]);
                        }

                        $('[data-role=swatch-options]').on('change', function() {
                            if (! $.mage.SwatchRenderer) {
                                return;
                            }
                            var productId = $(this).SwatchRenderer('getProduct');

                            if (deliveryDates[productId]) {
                                console.log(deliveryDates[productId]);
                                if (deliveryDates[productId] == INHERITED) {
                                    self.estimatedDelivery("");
                                } else {
                                    self.estimatedDelivery(deliveryDates[productId]);
                                }
                            } else if (deliveryDates[parentProductId]) {
                                if (deliveryDates[parentProductId] == INHERITED) {
                                    self.estimatedDelivery("");
                                } else {
                                    self.estimatedDelivery(deliveryDates[parentProductId]);
                                }
                            }

                            if (shippingDates[productId]) {
                                if (shippingDates[productId] == INHERITED) {
                                    self.estimatedShipping("");
                                } else {
                                    self.estimatedShipping(shippingDates[productId]);
                                }
                            } else if (shippingDates[parentProductId]) {
                                if (shippingDates[parentProductId] == INHERITED) {
                                    self.estimatedShipping("");
                                } else {
                                    self.estimatedShipping(shippingDates[parentProductId]);
                                }
                            }
                        });

                    } else {
                        if (getCategoryValue(deliveryDates) != INHERITED) {
                            self.estimatedDelivery(getCategoryValue(deliveryDates));
                        }
                        if (getCategoryValue(shippingDates) != INHERITED) {
                            self.estimatedShipping(getCategoryValue(shippingDates));
                        }
                    }
                    function getCategoryValue(deliveryDates) {
                        return deliveryDates.filter(function(e){ return e === 0 || e }).slice(0,1)[0];
                    }
                },

                getEstimatedData: function(estimatedData) {
                    var dateFormat;
                    if (estimatedData.dateFormat) {
                        dateFormat = estimatedData.dateFormat.toUpperCase();
                    }
                    self.calculationEstimatedData(estimatedData, 'delivery', dateFormat);
                    self.calculationEstimatedData(estimatedData, 'shipping', dateFormat);
                },

                getFormatDate: function(date, dateFormat) {
                    return moment(new Date(date)).format(dateFormat);
                },

                getDays: function(value) {
                    var number = parseInt(value);
                    return isNaN(number) ? 0 : number;
                },

                calculationEstimatedData: function(estimatedData, type, dateFormat) {
                    var middleEstimatedVariable = new Array();
                    Date.prototype.addDays = function(days) {
                        this.setDate(this.getDate() + days);
                        return this;
                    };

                    var cutOfTime;
                    if (type == 'delivery') {
                        cutOfTime = estimatedData.delivery['cutoftime'];
                    } else if (type == 'shipping') {
                        cutOfTime = estimatedData.shipping['cutoftime'];
                    }

                    for (var entityId in estimatedData[type]) {
                        var calcDate = new Date(calcServerTime);
                        var itemEstimateData = estimatedData[type][entityId];

                        var bank = self.getDays(estimatedData[type][entityId + "_bank"]);
                        var bankRange = self.getDays(estimatedData[type][entityId + "_bank_range"]);
                        var enable;

                        if (itemEstimateData == null) {
                           continue;
                        }
                        if (itemEstimateData == false) {
                           enable = EMPTY;
                        } else if (itemEstimateData.enable) {
                           enable = itemEstimateData.enable;
                        } else if (itemEstimateData.text) {
                           enable = STATIC_TEXT;
                        }

                        switch(enable) {
                            case DYNAMIC_DATE: {
                                calcDate.addDays(bank);
                                middleEstimatedVariable[entityId] = self.getFormatDate(calcDate, dateFormat);
                                break;
                            }
                            case DYNAMIC_DATE_RANGE: {
                                middleEstimatedVariable[entityId] = self.getFormatDate(calcDate.addDays(bank), dateFormat) +
                                    ' - ' + self.getFormatDate(calcDate.addDays(bankRange - bank), dateFormat);
                                break;
                            }
                            case STATIC_DATE: {
                                middleEstimatedVariable[entityId] = self.getFormatDate(itemEstimateData.from, dateFormat);
                                break;
                            }
                            case STATIC_DATE_RANGE: {
                                middleEstimatedVariable[entityId] = self.getFormatDate(itemEstimateData.from, dateFormat) + ' - '
                                    + self.getFormatDate(itemEstimateData.to, dateFormat);
                                break;
                            }
                            case STATIC_TEXT: {
                                if (typeof itemEstimateData.text !== 'undefined') {
                                    middleEstimatedVariable[entityId] = decodeURIComponent(escape(atob(itemEstimateData.text)));
                                } else {
                                    middleEstimatedVariable[entityId] = INHERITED;
                                }
                                break;
                            }
                            case EMPTY: {
                                middleEstimatedVariable[entityId] = INHERITED;
                                break;
                            }
                            default:
                                break;
                        }
                    }
                    if (type == "delivery") {
                        deliveryDates = middleEstimatedVariable;
                    } else if (type == "shipping") {
                        shippingDates = middleEstimatedVariable;
                    }
                },
            });
        };
    }
);
