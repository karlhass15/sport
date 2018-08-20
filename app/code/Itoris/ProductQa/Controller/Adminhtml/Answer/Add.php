<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCTQA
 * @copyright  Copyright (c) 2017 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\ProductQa\Controller\Adminhtml\Answer;


class Add extends \Magento\Backend\App\Action {
    public function execute() {

        $jsonFactory = $this->_objectManager->create('\Magento\Framework\Controller\Result\JsonFactory');
        $qId = (int)$this->getRequest()->getPost('q_id');
        $answer = $this->getRequest()->getPost('content');
        $status = (int)$this->getRequest()->getPost('status');
        /** @var  $userSess \Magento\Backend\Model\Session */
        $userSess = $this->_objectManager->create('Magento\Backend\Model\Auth\Session');
        $data = array(
            'status'         => $status,
            'submitter_type' => (int)\Itoris\ProductQa\Model\Answers::SUBMITTER_ADMIN,
            'nickname'       => $this->getRequest()->getPost('nickname'),
            'content'        => $answer,
            'customer_id'    => $userSess->getUser()->getUserId(),
            'q_id'           => $qId,
        );

        try {
            $this->_objectManager->create('Itoris\ProductQa\Model\Answers')->addAnswer($data);
            /** @var  $messages \Magento\Framework\Message\ManagerInterface */
            $messages = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
            $messages->addSuccessMessage(__('Answer has been saved'));
            $resultJs = [
                'success' => 'success',
            ];
			
			$questionModel = $this->_objectManager->create('Itoris\ProductQa\Model\Questions')->load($data['q_id']);
			$this->_objectManager->create('Magento\Catalog\Model\Product')->load($questionModel->getProductId())->save(); //updating in FPC

        } catch (\Exception $e) {
            $resultJs = [
                'error' => $e->getMessage(),
                ];
            $messages->addErrorMessage(__('Answer has not been saved'));
        }
        if ($status == \Itoris\ProductQa\Model\Answers::STATUS_APPROVED) {
            try {
                $this->_objectManager->create('Itoris\ProductQa\Model\Notify')->prepareAndSendNotification($qId, $answer, \Itoris\ProductQa\Model\Answers::SUBMITTER_ADMIN, $data['nickname']);
            } catch(\Exception $e) {
                $resultJs = [
                    'error' => $e->getMessage(),
                  ];
                $messages->addErrorMessage(__('Answer has not been saved'));
            }
        }
        $result = $jsonFactory->create();
        return $result->setData($resultJs);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_ProductQa::productQA');
    }
}