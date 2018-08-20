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

namespace Plumrocket\Estimateddelivery\Model;

class OrderItem extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Plumrocket\Estimateddelivery\Model\ResourceModel\OrderItem');
    }

    public function save()
    {
        if ($this->isObjectNew()) {
            $data = [
                'item_id' => $this->getItemId(),
                'delivery' => $this->getDelivery(),
                'shipping' => $this->getShipping()
            ];

            $this->_getResource()->insertOnDuplicate($data, ['delivery', 'shipping']);
        } else {
            parent::save();
        }

        return $this;
    }
}
