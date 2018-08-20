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
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

require([
    'jquery',
    'mage/calendar',
    'domReady!'
], function(pjQuery) {
    // 'use strict';

    // Fix. It disable warning "jQuery.browser is deprecated"
    // pjQuery.migrateMute = true;

    var daysInMonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    var _setDays = function($month) {
        var $days = pjQuery($month).parent().find('.dateperiod-day');
        $days.find('option').show();
        switch(daysInMonth[$month.value - 1]) {
            case 29:
                $days.find('option[value=30]').hide();
                if($days.val() == 30) {
                    $days.val(1);
                }
                // no break
            case 30:
                $days.find('option[value=31]').hide();
                if($days.val() == 31) {
                    $days.val(1);
                }
                // no break
        }
    }

    var _rowDateType = function($row, dateType) {
        $row.find('.dateperiod-recurring-date').toggle(dateType == 'recurring_date');
        $row.find('.dateperiod-single-day').toggle(dateType == 'single_day');
        $row.find('.dateperiod-period').toggle(dateType == 'period');
    }

    /* Delivery Holidays */
    // Add row.
    var $deliveryHolidaysGrid = pjQuery('#row_estimateddelivery_delivery_holidays > td.value .data-grid tbody');
    var deliveryHolidaysTemplate;
    var deliveryHolidaysTemplateInterval = setInterval(function() {
        /*if (!$deliveryHolidaysGrid || $deliveryHolidaysGrid.length == 0) {
            $deliveryHolidaysGrid = pjQuery('#row_estimateddelivery_delivery_holidays > td.value .data-grid tbody');
            return;
        }*/
        if ($deliveryHolidaysGrid.find('*[data-mage-init]').length == 0 && $deliveryHolidaysGrid.find('.ui-datepicker-trigger').length != 0) {
            var $inherit = jQuery('#estimateddelivery_delivery_holidays_inherit:checked');
            if ($inherit.length) {
                $inherit.click();
            }
            deliveryHolidaysTemplateCalendarOptions = $deliveryHolidaysGrid.find('tr:first-child ._has-datepicker').eq(0).datepicker('option', 'all');
            if (deliveryHolidaysTemplateCalendarOptions.length == 0) {
                // Fix for Magento < 2.1.*
                deliveryHolidaysTemplateCalendarOptions = $deliveryHolidaysGrid.find('tr:first-child .hasDatepicker').eq(0).datepicker('option', 'all');
            }
            deliveryHolidaysTemplate = $deliveryHolidaysGrid.find('tr:first-child').html();
            deliveryHolidaysTemplate = deliveryHolidaysTemplate.split('_has-datepicker').join('');
            deliveryHolidaysTemplate = deliveryHolidaysTemplate.split('hasDatepicker').join('');
            // deliveryHolidaysTemplate = deliveryHolidaysTemplate.replace(/<button\stype="button"\sclass="ui-datepicker-trigger.+?<\/button>/g, '');
            $deliveryHolidaysGrid.find('tr:first-child').remove();

            $deliveryHolidaysGrid.addClass('loaded');
            if ($inherit.length) {
                $inherit.click();
            }
            clearInterval(deliveryHolidaysTemplateInterval);
        }
    }, 200);


    pjQuery('#row_estimateddelivery_delivery_holidays > td.value > .dateperiod-add').on('click', function() {
        var name = 'dateperiod-' + Date.now();
        var template = deliveryHolidaysTemplate.split('_TMPNAME_').join(name);
        $deliveryHolidaysGrid.append('<tr>'+ template +'</tr>');

        var $row = $deliveryHolidaysGrid.find('tr:last-child');
        var dateType = $row.find('.dateperiod-type select').val();
        _rowDateType($row, dateType);

        // Add calendar (jQuery.calendarConfig).
        $row.find('.input-text').calendar(deliveryHolidaysTemplateCalendarOptions);
        return false;
    });

    // Change date type.
    $deliveryHolidaysGrid.on('change', '.dateperiod-type select', function() {
        var $row = pjQuery(this).parent().parent();
        _rowDateType($row, this.value);
    })
    .find('.dateperiod-type select').each(function() {
        var $row = pjQuery(this).parent().parent();
        _rowDateType($row, this.value);
    });

    // Change month.
    $deliveryHolidaysGrid.on('change', '.dateperiod-recurring-date .dateperiod-month', function() {
        _setDays(this);
    })
    .find('.dateperiod-recurring-date .dateperiod-month').each(function() {
         _setDays(this);
    });

    // Remove row.
    $deliveryHolidaysGrid.on('click', '.dateperiod-remove', function() {
        pjQuery(this).parent().parent().remove();
    });

    // Scope.
    pjQuery('#estimateddelivery_delivery_holidays_inherit:checked').click().click();

    /* Shipping Holidays */
    // Add row.
    var $shippingHolidaysGrid = pjQuery('#row_estimateddelivery_shipping_holidays > td.value .data-grid tbody');
    var shippingHolidaysTemplate;
    var shippingHolidaysTemplateInterval = setInterval(function() {
        /*if (!$shippingHolidaysGrid || $shippingHolidaysGrid.length == 0) {
            $shippingHolidaysGrid = pjQuery('#row_estimateddelivery_shipping_holidays > td.value .data-grid tbody');
            return;
        }*/
        if ($shippingHolidaysGrid.find('*[data-mage-init]').length == 0 && $shippingHolidaysGrid.find('.ui-datepicker-trigger').length != 0) {
            var $inherit = jQuery('#estimateddelivery_shipping_holidays_inherit:checked');
            if ($inherit.length) {
                $inherit.click();
            }
            shippingHolidaysTemplateCalendarOptions = $shippingHolidaysGrid.find('tr:first-child ._has-datepicker').eq(0).datepicker('option', 'all');
            if (shippingHolidaysTemplateCalendarOptions.length == 0) {
                // Fix for Magento < 2.1.*
                shippingHolidaysTemplateCalendarOptions = $shippingHolidaysGrid.find('tr:first-child .hasDatepicker').eq(0).datepicker('option', 'all');
            }
            shippingHolidaysTemplate = $shippingHolidaysGrid.find('tr:first-child').html();
            shippingHolidaysTemplate = shippingHolidaysTemplate.split('_has-datepicker').join('');
            shippingHolidaysTemplate = shippingHolidaysTemplate.split('hasDatepicker').join('');
            // shippingHolidaysTemplate = shippingHolidaysTemplate.replace(/<button\sclass="ui-datepicker-trigger.+?<\/button>/g, '');
            $shippingHolidaysGrid.find('tr:first-child').remove();
            $shippingHolidaysGrid.addClass('loaded');
            if ($inherit.length) {
                $inherit.click();
            }
            clearInterval(shippingHolidaysTemplateInterval);
        }
    }, 200);


    pjQuery('#row_estimateddelivery_shipping_holidays > td.value > .dateperiod-add').on('click', function() {
        var name = 'dateperiod-' + Date.now();
        var template = shippingHolidaysTemplate.split('_TMPNAME_').join(name);
        $shippingHolidaysGrid.append('<tr>'+ template +'</tr>');

        var $row = $shippingHolidaysGrid.find('tr:last-child');
        var dateType = $row.find('.dateperiod-type select').val();
        _rowDateType($row, dateType);

        // Add calendar (jQuery.calendarConfig).
        $row.find('.input-text').calendar(shippingHolidaysTemplateCalendarOptions);
        return false;
    });

    // Change date type.
    $shippingHolidaysGrid.on('change', '.dateperiod-type select', function() {
        var $row = pjQuery(this).parent().parent();
        _rowDateType($row, this.value);
    })
    .find('.dateperiod-type select').each(function() {
        var $row = pjQuery(this).parent().parent();
        _rowDateType($row, this.value);
    });

    // Change month.
    $shippingHolidaysGrid.on('change', '.dateperiod-recurring-date .dateperiod-month', function() {
        _setDays(this);
    })
    .find('.dateperiod-recurring-date .dateperiod-month').each(function() {
        _setDays(this);
    });

    // Remove row.
    $shippingHolidaysGrid.on('click', '.dateperiod-remove', function() {
        pjQuery(this).parent().parent().remove();
    });

    // Scope.
    pjQuery('#estimateddelivery_shipping_holidays_inherit:checked').click().click();
});