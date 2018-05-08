<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Frontend\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionFactory;

class Edit extends Generic
{

    /**
     * @var ProductFactory
     */
    public $productFactory;
    /**
     * @var SubscriptionFactory
     */
    public $subscriptionFactory;
    /**
     * @var \Wagento\Subscription\Helper\Subscription
     */
    public $subscription;

    /**
     * Edit constructor.
     * @param Template\Context $context
     * @param array $data
     * @param ProductFactory $productFactory
     * @param SubscriptionFactory $subscriptionFactory
     * @param \Wagento\Subscription\Helper\Subscription $subscription
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        \Wagento\Subscription\Model\ProductFactory $productFactory,
        \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory,
        \Wagento\Subscription\Helper\Subscription $subscription
    ) {
    
        parent::__construct($context, $data);

        $this->productFactory = $productFactory->create();
        $this->subscriptionFactory = $subscriptionFactory->create();
        $this->subscription = $subscription;
    }

    /**
     * @return null|string
     */
    public function getSubscription()
    {
        $data = $this->getSubscriptionData();

        return $data;
    }

    /**
     * @return string
     */
    public function getSubscriptionFrequency()
    {
        $frequency = $this->subscriptionFactory->getFrequency();
        if ($frequency == 1) {
            return "Daily";
        } elseif ($frequency == 2) {
            return "Weekly";
        } elseif ($frequency == 3) {
            "Monthly";
        } elseif ($frequency == 4) {
            "Yearly";
        }
    }

    /**
     * @return mixed
     */
    public function fetchView($fileName)
    {
        return parent::fetchView($fileName); // TODO: Change the autogenerated stub
    }

    function getSubscriptionData()
    {
        return $this->subscription->getSubscriptionData($this->getItem()->getProduct()->getId());
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getItem()->getProduct()->getId();
    }
}
