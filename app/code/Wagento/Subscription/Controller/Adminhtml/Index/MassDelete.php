<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Wagento\Subscription\Controller\Adminhtml\Index as IndexAction;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends IndexAction
{
    /**
     * @var string
     */
    public $redirectUrl = '*/*/index';

    /**
     * Execute action
     *
     * @return $this|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $subscriptionsDeleted = 0;
            $subscriptionsNotDeleted = 0;
            foreach ($collection->getAllIds() as $subscriptionId) {
                $objSubscritionProducts = $this->productRepository->getBySubscriptionId($subscriptionId);
                $subcriptionIds = array_column($objSubscritionProducts->getData(), 'subscription_id');
                if (empty($subcriptionIds)) {
                    $this->subscriptionRepository->deleteById($subscriptionId);
                    $subscriptionsDeleted++;
                    continue;
                }
                $subscriptionsNotDeleted++;
            }

            if ($subscriptionsNotDeleted) {
                $this->messageManager->addWarningMessage(
                    __('A total of %1 record(s) were not deleted 
                    because of associated products', $subscriptionsNotDeleted)
                );
            }

            if ($subscriptionsDeleted) {
                $this->messageManager->addSuccessMessage(__('A total of %1 
                record(s) were deleted.', $subscriptionsDeleted));
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('subscription/*/index');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
