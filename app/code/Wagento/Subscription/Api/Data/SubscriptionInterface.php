<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SubscriptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const SUBSCRIPTION_ID = 'subscription_id';
    const NAME = 'name';
    const FREQUENCY = 'frequency';
    const FEE = 'fee';
    const DATE_START = 'date_start';
    const DATE_END = 'date_end';
    const HOW_MANY = 'how_many';
    const SHIPPING_TYPE = 'shipping_type';
    const SHOPPING_RULE = 'shopping_rule';
    const DISCOUNT = 'discount';
    /**#@-*/

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
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get frequency
     *
     * @return int|null
     */
    public function getFrequency();

    /**
     * Set frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency);

    /**
     * Get fee
     *
     * @return float|null
     */
    public function getFee();

    /**
     * Set fee
     *
     * @param float $fee
     * @return $this
     */
    public function setFee($fee);

    /**
     * Get Start Date
     *
     * @return date|null
     */
    public function getDateStart();

    /**
     * Set Start Date
     *
     * @param date $dateStart
     * @return $this
     */
    public function setDateStart($dateStart);

    /**
     * Get End Date
     *
     * @return date|null
     */
    public function getDateEnd();

    /**
     * Set End Date
     *
     * @param date $dateEnd
     * @return $this
     */
    public function setDateEnd($dateEnd);

    /**
     * Get How Many
     *
     * @return float|null
     */
    public function getHoWMany();

    /**
     * Set How Many
     *
     * @param float $howMany
     * @return $this
     */
    public function setHowMany($howMany);

    /**
     * Get Shipping Type
     *
     * @return string
     */
    public function getShippingType();

    /**
     * Set Shipping Type
     *
     * @param string $shippingType
     * @return $this
     */
    public function setShippingType($shippingType);

    /**
     * Get Shopping Rule
     *
     * @return string
     */
    public function getShoppingRule();

    /**
     * Set Shopping Rule
     *
     * @param string $shoppingRule
     * @return $this
     */
    public function setShoppingRule($shoppingRule);

    /**
     * Get Tiered Price
     *
     * @return float
     */
    public function getDiscount();

    /**
     * Set Tiered Price
     *
     * @param float $discount
     * @return $this
     */
    public function setDiscount($discount);
}
