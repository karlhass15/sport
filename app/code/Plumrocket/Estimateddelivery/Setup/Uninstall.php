<?php
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

namespace Plumrocket\Estimateddelivery\Setup;

class Uninstall extends \Plumrocket\Base\Setup\AbstractUninstall
{
    protected $_attributes = [
        \Magento\Catalog\Model\Product::ENTITY => [
            'estimated_delivery_enable',
            'estimated_delivery_days_from',
            'estimated_delivery_days_to',
            'estimated_delivery_date_from',
            'estimated_delivery_date_to',
            'estimated_delivery_text',

            'estimated_shipping_enable',
            'estimated_shipping_days_from',
            'estimated_shipping_days_to',
            'estimated_shipping_date_from',
            'estimated_shipping_date_to',
            'estimated_shipping_text',
        ],

        \Magento\Catalog\Model\Category::ENTITY => [
            'estimated_delivery_enable',
            'estimated_delivery_days_from',
            'estimated_delivery_days_to',
            'estimated_delivery_date_from',
            'estimated_delivery_date_to',
            'estimated_delivery_text',

            'estimated_shipping_enable',
            'estimated_shipping_days_from',
            'estimated_shipping_days_to',
            'estimated_shipping_date_from',
            'estimated_shipping_date_to',
            'estimated_shipping_text',
        ]
    ];
    protected $_configSectionId = 'estimateddelivery';
    protected $_pathes = ['/app/code/Plumrocket/Estimateddelivery'];
    protected $_tables = ['plumrocket_estimateddelivery_order_item'];
}
