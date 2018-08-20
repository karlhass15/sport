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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Estimateddelivery\Observer;

class SalesOrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Plumrocket\Estimateddelivery\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Plumrocket\Estimateddelivery\Helper\Product
     */
    protected $productHelper;

    /**
     * SalesOrderPlaceBefore constructor.
     *
     * @param \Plumrocket\Estimateddelivery\Helper\Data    $helper
     * @param \Plumrocket\Estimateddelivery\Helper\Product $productHelper
     */
    public function __construct(
        \Plumrocket\Estimateddelivery\Helper\Data $helper,
        \Plumrocket\Estimateddelivery\Helper\Product $productHelper
    ) {
        $this->_helper = $helper;
        $this->productHelper = $productHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->moduleEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllVisibleItems() as $item) {
            $options = $this->productHelper->getOptions($item, true);
            $this->_helper->saveOptions($item, $options);
        }
    }
}
