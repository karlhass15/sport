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

namespace Plumrocket\Estimateddelivery\Plugin;

use Plumrocket\Estimateddelivery\Model\Config\Source\Position;

class QuotePlugin
{
    protected $_helper;
    protected $_productHelper;
    protected $_request;
    protected $_layout;
    protected $_productModel;

    public function __construct(
        \Plumrocket\Estimateddelivery\Helper\Data $helper,
        \Plumrocket\Estimateddelivery\Helper\Product $productHelper,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Catalog\Model\ProductFactory $productModel
    ) {
        $this->_helper = $helper;
        $this->_productHelper = $productHelper;
        $this->_request = $httpRequest;
        $this->_layout = $layout;
        $this->_productModel = $productModel;
    }

    public function afterGetAllItems($subject, $items)
    {
        if ($this->_helper->moduleEnabled()) {
            switch (true) {
                case $this->_helper->showPosition(Position::CHECKOUT)
                    && false !== strpos($this->_request->getModuleName(), 'checkout')
                    && $this->_request->getControllerName()    == 'index'
                    && $this->_request->getActionName()        != 'success':
                case $this->_helper->showPosition(Position::CHECKOUT)
                    && $this->_request->getModuleName() == 'onestepcheckout':
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if ($options = $this->_getEstimatedDeliveryOptions($item)) {
                                if (! $item->getPrEdOption()) {
                                    $item->setPrEdOption(1);
                                    $item->addOption([
                                        'code' => 'additional_options',
                                        'value' => $this->_helper->serialize($options)
                                    ]);
                                }
                            }
                        }
                    }
                    break;
            }
        }

        return $items;
    }

    protected function _getEstimatedDeliveryOptions($item)
    {
        $options = [];

        if (!$product = $item->getProduct()) {
            $product = $this->_productModel->create()->load($item->getProductId());
        }

        $options = $this->_productHelper->getOptions($item, false);

        return array_values($options);
    }
}
