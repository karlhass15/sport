<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Wagento\Subscription\Model\Product;
use Wagento\Subscription\Model\SubscriptionFactory;
use Wagento\Subscription\Model\SubscriptionSalesFactory;

class MassDelete extends \Magento\Catalog\Controller\Adminhtml\Product\MassDelete
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;
    /**
     * @var \Wagento\Subscription\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var SubscriptionSalesFactory
     */
    private $subscriptionSalesFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @param SubscriptionFactory $subscriptionFactory
     * @param Product $productFactory
     * @param SubscriptionSalesFactory $subscriptionSalesFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository = null,
        SubscriptionFactory $subscriptionFactory,
        Product $productFactory,
        SubscriptionSalesFactory $subscriptionSalesFactory
    ) {
    
        parent::__construct($context, $productBuilder, $filter, $collectionFactory, $productRepository);
        $this->productRepository = $productRepository;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->productFactory = $productFactory;
        $this->subscriptionSalesFactory = $subscriptionSalesFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productDeleted = 0;
        $productSuscribes = 0;
        $productSalesSuscribes = 0;
        
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            $productData = $this->productRepository->getById($product->getId());
            $subConfig = $productData->getCustomAttribute('subscription_configurate');
            $subOptions =  $subConfig->getValue();

            if (in_array($product->getId(), $this->getIdProductsSubscription()) == true && $subOptions!='no') {
                if (in_array($product->getId(), $this->getIdProductsSubscriptionSales()) == true) {
                    $productSalesSuscribes++;
                }
                $productSuscribes++;
                continue;
            }

            if (in_array($product->getId(), $this->getIdProductsSubscriptionSales()) == true && $subOptions!='no') {
                $productSalesSuscribes++;
                continue;
            }
            $this->productRepository->delete($product);
            $productDeleted++;
        }


        if ($productSuscribes > 0 && $productSalesSuscribes > 0) {
            $this->messageManager->addNoticeMessage(
                __('%1 products were not deleted, because they have some associated subscription plans and subscription profiles.', $productSuscribes)
            );
        }

        if ($productSuscribes > 0 && $productSalesSuscribes == 0) {
            $this->messageManager->addNoticeMessage(
                __('%1 products were not deleted, because they have some associated subscription plans.', $productSuscribes)
            );
        }

        if ($productSalesSuscribes > 0 && $productSuscribes==0) {
            $this->messageManager->addNoticeMessage(
                __('%1 products were not deleted, because they have some associated subscription profiles.', $productSuscribes)
            );
        }

        if($productDeleted > 0) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $productDeleted)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }

    /**
     * @return array
     */
    private function getIdProductsSubscription()
    {
        return $this->productFactory->getCollection()->getColumnValues('product_id');
    }

    /**
     * @return array
     */
    private function getIdProductsSubscriptionSales()
    {
        $status = [0,1,2]; //get cancel, active and pause status subscription profile
        $collection = $this->subscriptionSalesFactory->create()->getCollection()
            ->addFieldToFilter('status', array('in' => $status));
        return $collection->getColumnValues('sub_product_id');

    }
}
