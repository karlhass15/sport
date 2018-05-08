<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Wagento\Subscription\Api\Data\SubscriptionInterface;

class Subscription extends AbstractExtensibleModel implements SubscriptionInterface
{
    /**
     * Initialize subscription model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Wagento\Subscription\Model\ResourceModel\Subscription::class);
    }

    /**
     * Get subscription id
     *
     * @return int|null
     */
    public function getSubscriptionId()
    {
        return $this->_getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Set subscription id
     *
     * @param int $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->_getData(self::NAME);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get frequency
     *
     * @return int|null
     */
    public function getFrequency()
    {
        return $this->_getData(self::FREQUENCY);
    }

    /**
     * Set frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        return $this->setData(self::FREQUENCY, $frequency);
    }

    /**
     * Get fee
     *
     * @return float|null
     */
    public function getFee()
    {
        return $this->_getData(self::FEE);
    }

    /**
     * Set fee
     *
     * @param float $fee
     * @return $this
     */
    public function setFee($fee)
    {
        return $this->setData(self::FEE, $fee);
    }

    /**
     * Get Start Date
     *
     * @return date|null
     */
    public function getDateStart()
    {
        return $this->_getData(self::DATE_START);
    }

    /**
     * Set Start Date
     *
     * @param date $dateStart
     * @return $this
     */
    public function setDateStart($dateStart)
    {
        return $this->setData(self::DATE_START, $dateStart);
    }

    /**
     * Get End Date
     *
     * @return date|null
     */
    public function getDateEnd()
    {
        return $this->_getData(self::DATE_END);
    }

    /**
     * Set End Date
     *
     * @param date $dateStart
     * @return $this
     */
    public function setDateEnd($dateEnd)
    {
        return $this->setData(self::DATE_END, $dateEnd);
    }

    /**
     * Get howMany
     *
     * @return float|null
     */
    public function getHoWMany()
    {
        return $this->_getData(self::HOW_MANY);
    }

    /**
     * Set howMany
     *
     * @param float $howMany
     * @return $this
     */
    public function setHowMany($howMany)
    {
        return $this->setData(self::HOW_MANY, $howMany);
    }

    /**
     * Get Shipping Type
     *
     * @return string
     */
    public function getShippingType()
    {
        return $this->_getData(self::SHIPPING_TYPE);
    }

    /**
     * Set Shipping Type
     *
     * @param string $shippingType
     * @return $this
     */
    public function setShippingType($shippingType)
    {
        return $this->setData(self::SHIPPING_TYPE, $shippingType);
    }

    /**
     * Get Shopping Rule
     *
     * @return string
     */
    public function getShoppingRule()
    {
        return $this->_getData(self::SHOPPING_RULE);
    }

    /**
     * Set Shopping Rule
     *
     * @param string $shoppingRule
     * @return $this
     */
    public function setShoppingRule($shoppingRule)
    {
        return $this->setData(self::SHOPPING_RULE, $shoppingRule);
    }

    /**
     * Get Tiered Price
     *
     * @return float
     */
    public function getDiscount()
    {
        return $this->_getData(self::DISCOUNT);
    }

    /**
     * Set Tiered Price
     *
     * @param float $discount
     * @return $this
     */
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }
}
