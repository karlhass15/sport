<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\ResourceModel\SubscriptionSales\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'ID';

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('Wagento\Subscription\Model\SubscriptionSales', 'Wagento\Subscription\Model\ResourceModel\SubscriptionSales');
    }
}
