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

namespace Plumrocket\Estimateddelivery\Model\ResourceModel;

class OrderItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('plumrocket_estimateddelivery_order_item', 'id');
    }

    public function insertOnDuplicate(array $data, array $fields = [])
    {
        $this->_getConnection('write')->insertOnDuplicate($this->getMainTable(), $data, $fields);
        return $this->_getConnection('write')->lastInsertId();
    }
}
