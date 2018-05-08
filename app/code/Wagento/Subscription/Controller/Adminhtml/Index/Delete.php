<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Wagento\Subscription\Controller\Adminhtml\Index;

class Delete extends Index
{
    /**
     * Delete subscription action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $subscriptionId = $this->initCurrentSubscription();
        if (!empty($subscriptionId)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            try {
                $objSubscritionProducts = $this->productRepository->getBySubscriptionId($subscriptionId);
                $subcriptionIds = array_column($objSubscritionProducts->getData(), 'subscription_id');

                if (empty($subcriptionIds)) {
                    $this->messageManager->addErrorMessage(__('The subscription can\'t 
                    be deleted because of associated products'));

                    return $resultRedirect->setPath(
                        'subscription/index/edit',
                        ['id' => $subscriptionId]
                    );
                }

                $this->subscriptionRepository->deleteById($subscriptionId);

//                $this->deleteSubscriptionProducts($subscriptionId);

                $this->messageManager->addSuccessMessage(__('You deleted the subscription.'));
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        return $resultRedirect->setPath('subscription/index');
    }

    /**
     * @param $subscriptionId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Wagento\Subscription\Api\CouldNotDeleteException
     */
    private function deleteSubscriptionProducts($subscriptionId)
    {
        $objSubscritionProducts = $this->productRepository->getBySubscriptionId($subscriptionId);
        $subcriptionIds = array_column($objSubscritionProducts->getData(), 'subscription_id');

        if (count($subcriptionIds) > 0) {
            foreach ($objSubscritionProducts as $objSubscritionProduct) {
                if (!in_array($objSubscritionProduct->getSubcriptionId(), $productIds)) {
                    $this->productRepository->delete($objSubscritionProduct);
                }
            }
        }
    }
}
