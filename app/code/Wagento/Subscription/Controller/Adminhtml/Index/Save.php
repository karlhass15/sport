<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Wagento\Subscription\Controller\Adminhtml\Index;

/**
 * Class Save
 */
class Save extends Index
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Wagento\Subscription\Block\Adminhtml\Subscription\ProductGrid
     */
    protected $productGrid;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wagento\Subscription\Api\SubscriptionRepositoryInterface $subscriptionRepository
     * @param \Wagento\Subscription\Api\Data\SubscriptionInterfaceFactory $subscriptionDataFactory
     * @param \Wagento\Subscription\Api\ProductRepositoryInterface $productRepository
     * @param \Wagento\Subscription\Api\Data\ProductInterfaceFactory $productDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Wagento\Subscription\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Wagento\Subscription\Model\Subscription\Mapper $subscriptionMapper
     * @param \Magento\Catalog\Model\Product $product
     * @param \Wagento\Subscription\Block\Adminhtml\Subscription\ProductGrid $productGrid
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Wagento\Subscription\Api\SubscriptionRepositoryInterface $subscriptionRepository,
        \Wagento\Subscription\Api\Data\SubscriptionInterfaceFactory $subscriptionDataFactory,
        \Wagento\Subscription\Api\ProductRepositoryInterface $productRepository,
        \Wagento\Subscription\Api\Data\ProductInterfaceFactory $productDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Wagento\Subscription\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Wagento\Subscription\Model\Subscription\Mapper $subscriptionMapper,
        \Magento\Catalog\Model\Product $product,
        \Wagento\Subscription\Block\Adminhtml\Subscription\ProductGrid $productGrid
    ) {
    
        parent::__construct($context, $resultPageFactory, $resultForwardFactory, $coreRegistry, $subscriptionRepository, $subscriptionDataFactory, $productRepository, $productDataFactory, $dataObjectHelper, $filter, $collectionFactory, $resultJsonFactory, $logger, $subscriptionMapper);
        $this->product = $product;
        $this->productGrid = $productGrid;
    }

    /**
     * Subscription Management Save
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $returnToEdit = false;
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            try {
                if (empty($data['subscription_id'])) {
                    $data['subscription_id'] = null;
                }

                $subscription = $this->subscriptionDataFactory->create();

                $this->dataObjectHelper->populateWithArray(
                    $subscription,
                    $data,
                    '\Wagento\Subscription\Api\Data\SubscriptionInterface'
                );

                $subscription = $this->subscriptionRepository->save($subscription);
                $subscriptionId = $subscription->getSubscriptionId();

                //save product in subscription
                $this->saveSubscriptionProducts($data, $subscriptionId);

                $this->_getSession()->unsSubscriptionFormData();

                $this->messageManager->addSuccessMessage(__('You saved the subscription.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setSubscriptionFormData($data);
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setSubscriptionFormData($data);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage($exception, __($exception->getMessage()));
                $this->_getSession()->setSubscriptionFormData($data);
                $returnToEdit = true;
            }
        }

        if ($returnToEdit) {
            if ($subscriptionId) {
                $resultRedirect->setPath(
                    'subscription/*/edit',
                    ['id' => $subscriptionId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'subscription/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('subscription/index');
        }
        return $resultRedirect;
    }

    /**
     * @param $data
     * @param $subscriptionId
     * @throws \Wagento\Subscription\Api\CouldNotDeleteException
     */
    private function saveSubscriptionProducts($data, $subscriptionId)
    {
        try {
            if (!isset($data['category_products'])) {
                return;
            }

            $productSaved = array_keys(json_decode($this->productGrid->getProductsJson($subscriptionId), true));
            $productIds = array_keys(json_decode($data['category_products'], true));

            $deletedIds = array_diff($productSaved, $productIds);

            $dataSubscritionProduct = ['subscription_id' => $subscriptionId,
                'customer_id' => null,
                'qty' => 1,
                'customer_address_id' => null
            ];

            $objSubscritionProducts = $this->productRepository->getBySubscriptionId($subscriptionId);


            $subcriptionIds = array_column($objSubscritionProducts->getData(), 'subscription_id');

            if (count($deletedIds)) {
                foreach ($deletedIds as $deletedId) {
                    $_product = $this->product->load($deletedId);
                    $_product->setData('subscription_attribute_product', '');
                    $_product->save();
                }
            }

            if (count($productIds) > 0) {
                foreach ($productIds as $productId) {
                    if (!in_array($productId, $subcriptionIds)) {
                        $_product = $this->product->load($productId);
                        $_product->setData('subscription_attribute_product', $subscriptionId);
                        $_product->save();

                        $subscriptionProduct = $this->productDataFactory->create();

                        $dataSubscritionProduct['product_id'] = $productId;
                        $this->dataObjectHelper->populateWithArray(
                            $subscriptionProduct,
                            $dataSubscritionProduct,
                            '\Wagento\Subscription\Api\Data\ProductInterface'
                        );

                        $this->productRepository->save($subscriptionProduct);
                    }
                }
            }

            if (count($subcriptionIds) > 0) {
                foreach ($objSubscritionProducts as $objSubscritionProduct) {
                    if (!in_array($objSubscritionProduct->getSubcriptionId(), $productIds)) {
                        $this->productRepository->delete($objSubscritionProduct);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __($exception->getMessage()));
            $this->_getSession()->setSubscriptionFormData($data);
            $returnToEdit = true;
        }
    }
}
