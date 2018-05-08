<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Wagento\Subscription\Api\SalesSubscriptionRepositoryInterface;

class SubscriptionSalesRepository implements SalesSubscriptionRepositoryInterface
{
    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionSalesFactory;
    /**
     * @var ResourceModel\Subscription
     */
    protected $subscriptionSalesResource;

    /**
     * SubscriptionSalesRepository constructor.
     * @param SubscriptionSalesFactory $subscriptionSalesFactory
     * @param ResourceModel\SubscriptionSales $subscriptionSalesResource
     */
    public function __construct(
        \Wagento\Subscription\Model\SubscriptionSalesFactory $subscriptionSalesFactory,
        \Wagento\Subscription\Model\ResourceModel\SubscriptionSales $subscriptionSalesResource
    ) {
    
        $this->subscriptionSalesFactory = $subscriptionSalesFactory;
        $this->subscriptionSalesResource = $subscriptionSalesResource;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Wagento\Subscription\Api\Data\SalesSubscriptionInterface $subscription)
    {
        try {
            $this->subscriptionSalesResource->save($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the subscription: %1', $exception->getMessage()),
                $exception
            );
        }

        return $subscription;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($subscriptionId)
    {
        $subscription = $this->subscriptionSalesFactory->create();
        $this->subscriptionSalesResource->load($subscription, $subscriptionId);
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $subscriptionId));
        }
        return $subscription;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement getList() method.
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($subscriptionSalesId)
    {
        return $this->delete($this->getById($subscriptionSalesId));
    }

    /**
     * Delete Page
     *
     * @param \Wagento\Subscription\Api\Data\SalesSubscriptionInterface $subscription
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Wagento\Subscription\Api\Data\SalesSubscriptionInterface $subscriptionSales)
    {
        try {
            $this->subscriptionSalesResource->delete($subscriptionSales);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the subscription: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
}
