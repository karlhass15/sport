<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Sales\Subscription\View;

class Products extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    public $_template = 'sales/subscription/product.phtml';

    /**
     * @var \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\Grid\Collection
     */
    public $collection;

    /**
     * Products constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\Grid\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\Grid\CollectionFactory $collectionFactory,
        array $data = []
    ) {
    
        $this->collection = $collectionFactory->create();
        parent::__construct($context, $data);
    }

    public function getSubscribedProduct()
    {
        $id = $this->getRequest()->getParam('id');
        $this->collection->addFieldToFilter('id', ['eq' => $id])->getSelect()->joinLeft(
            'customer_entity',
            "main_table.customer_id = customer_entity.entity_id",
            ['firstname', 'lastname', 'email']
        );

        $this->collection->addExpressionFieldToSelect(
            'customer_name',
            'CONCAT(customer_entity.firstname, \' \', customer_entity.lastname)',
            []
        );

        return $this->collection->getFirstItem();
    }

    public function getColumns()
    {
        $columns = array_key_exists('columns', $this->_data) ? $this->_data['columns'] : [];
        return $columns;
    }

    public function getItemsCollection1()
    {
        return $this->getOrder()->getItemsCollection();
    }

    public function getItemsCollection()
    {
        $id = $this->getRequest()->getParam('id');

        $customerTable = $this->getTable('customer_grid_flat');
        $salesOrderItemTable = $this->getTable('sales_order_item');
        $wagentoSubProductTable = $this->getTable('wagento_subscription_products');
        $wagentoSubTable = $this->getTable('wagento_subscription');

        $this->collection->addFieldToFilter('id', ['eq' => $id])->getSelect()->join(
            'sales_order_item as soi',
            "main_table.sub_order_item_id = soi.item_id && soi.is_subscribed = 1",
            ['*', 'created_at as order_created_at', 'updated_at as order_updated_at']
        );

        $this->collection->getSelect()->join(
            'customer_grid_flat as customer',
            'main_table.customer_id = customer.entity_id',
            ['customer.name as customer_name']
        );

        $this->collection->getSelect()->join(
            'wagento_subscription_products as wsp',
            "soi.product_id = wsp.product_id",
            ['subscription_id']
        );

        $this->collection->getSelect()->join(
            'wagento_subscription as ws',
            "wsp.subscription_id = ws.subscription_id",
            ['name as subscription_name', 'frequency', 'fee', 'discount']
        );

        $this->collection->addFilterToMap('id', 'main_table.id')
            ->addFilterToMap('customer_name', 'customer.name')
            ->addFilterToMap('subscription_name', 'wagento_subscription.name')
            ->addFilterToMap('created_at', 'main_table.created_at')
            ->addFilterToMap('store_id', 'main_table.store_id');

        return $this->collection->getFirstItem();
    }
}
