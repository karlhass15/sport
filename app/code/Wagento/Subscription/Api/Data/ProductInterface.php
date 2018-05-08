<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ProductInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const SUBSCRIPTION_ID = 'subscription_id';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_ID = 'customer_id';
    const PRODUCT_QTY = 'qty';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    /**#@-*/

    /**
     * get product subscription id
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * set product subscription id
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get subscription id
     *
     * @return int|null
     */
    public function getSubscriptionId();

    /**
     * Set subscription id
     *
     * @param int $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId);

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set product id
     *
     * @param $productId
     * @return mixed
     */
    public function setProductId($productId);

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * Get product qty
     *
     * @return int|null
     */
    public function getProductQty();

    /**
     * Set product qty
     *
     * @param $productQty
     * @return mixed
     */
    public function setProductQty($productQty);

    /**
     * Get customer address id
     *
     * @return $this
     */
    public function getCustomerAddressId();

    /**
     * Set product id
     *
     * @param $customerAddressId
     * @return $this
     */
    public function setCustomerAddressId($customerAddressId);
}
