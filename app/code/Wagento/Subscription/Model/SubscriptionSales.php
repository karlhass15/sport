<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Wagento\Subscription\Api\Data\SalesSubscriptionInterface;

class SubscriptionSales extends \Magento\Framework\Model\AbstractModel implements SalesSubscriptionInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wagento_salessubscription_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'wagento_salessubscription_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wagento_salessubscription_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Wagento\Subscription\Model\ResourceModel\SubscriptionSales');
    }

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set EntityId.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }
}
