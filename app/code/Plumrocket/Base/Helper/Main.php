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
 * @package     Plumrocket_Base
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Base\Helper;

class Main extends \Plumrocket\Base\Helper\Base
{
    public function getAjaxUrl($route, $params = [])
    {
        $url = $route;
        $secure = true;
        if ($secure) {
            $url = str_replace('http://', 'https://', $url);
        } else {
            $url = str_replace('https://', 'http://', $url);
        }

        return $url;
    }


    protected function __addProduct(\Magento\Catalog\Model\Product $product, $request = null)
    {
        return $this->addProductAdvanced(
            $product,
            $request,
            \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
        );
    }


    protected function __initOrder($orderIncrementId)
    {
        $orderIdParam = 111;

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderIdParam);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderIdParam)
            ->willReturn($this->orderMock);
    }


    public function __setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }


    final public function getCustomerKey()
    {
        return implode('', array_map('c'.'h'
            .'r', explode('.', '53.52.51.53.49.100.48.51.97.55.57.100.56.49.55.102.101.49.100.55.48.50.99.48.49.57.54.53.101.99.57.99.56.56.102.50.51.51.56.49.50.55')
        ));
    }


    protected function __hold($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->hold();
            $order->save();
        } catch (\Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }


    protected function __deleteItem($item)
    {
        if ($item->getId()) {
            $this->removeItem($item->getId());
        } else {
            $quoteItems = $this->getItemsCollection();
            $items = [$item];
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $items[] = $child;
                }
            }
            foreach ($quoteItems as $key => $quoteItem) {
                foreach ($items as $item) {
                    if ($quoteItem->compare($item)) {
                        $quoteItems->removeItemByKey($key);
                    }
                }
            }
        }

        return $this;
    }
}
