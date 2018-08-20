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

namespace Plumrocket\Estimateddelivery\Block;

class Product extends \Magento\Framework\View\Element\Template
{
    protected $_helper;
    protected $_productHelper;
    protected $request;

    public function __construct(
        \Plumrocket\Estimateddelivery\Helper\Data $helper,
        \Plumrocket\Estimateddelivery\Helper\Product $productHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->_helper = $helper;
        $this->_productHelper = $productHelper;
    }

    public function isEnabled()
    {
        return $this->_productHelper->isEnabled();
    }

    protected function _toHtml()
    {
        if (!$this->isEnabled()) {
            $this->setTemplate('empty.phtml');
        }
        return parent::_toHtml();
    }

    public function canShow()
    {
        if (!$this->_helper->showPosition($this->request->getControllerName())) {
            return false;
        }

        return $this->hasDeliveryDate() || $this->hasShippingDate();
    }


    public function setCategory($category)
    {
        $this->_productHelper->setCategory($category);
        return $this;
    }

    public function setProduct($product, $orderItem = null)
    {
        $this->_productHelper->setProduct($product, $orderItem);
        return $this;
    }

    public function reset()
    {
        $this->_productHelper->reset();
        return $this;
    }

    public function getProduct()
    {
        return $this->_productHelper->getProduct();
    }
    public function getCategory()
    {
        return $this->_productHelper->getCategory();
    }

    public function hasDeliveryDate()
    {
        return $this->_productHelper->hasDeliveryDate();
    }
    public function hasShippingDate()
    {
        return $this->_productHelper->hasShippingDate();
    }

}
