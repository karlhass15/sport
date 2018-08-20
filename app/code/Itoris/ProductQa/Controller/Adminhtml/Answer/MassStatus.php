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


class MassStatus extends  \Magento\Backend\App\Action {
    /**
     * Answers grid
     */
    public function execute() {
        $answerIds = $this->getRequest()->getParam('answers');
		$status = (int)$this->getRequest()->getParam('status');
        $messages = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
        if (empty($answerIds)) {
            $messages->addErrorMessage(__('Please select answers(s).'));
        } else {
            try {
				if (!trim(implode('', $answerIds))){
					$collection = $this->_objectManager->create('Itoris\ProductQa\Model\Answers')->getCollection();
					foreach($collection as $model) {
						$model->setStatus($status)->save();
						$questionModel = $this->_objectManager->create('Itoris\ProductQa\Model\Questions')->load($model->getQId());
						$this->_objectManager->create('Magento\Catalog\Model\Product')->load($questionModel->getProductId())->save(); //updating in FPC
					}
					$count = count($collection);
				} else {
					foreach ($answerIds as $id) {
						$model =$this->_objectManager->create('Itoris\ProductQa\Model\Answers');
						$model->load($id)->setStatus($status)->save();
						$questionModel = $this->_objectManager->create('Itoris\ProductQa\Model\Questions')->load($model->getQId());
						$this->_objectManager->create('Magento\Catalog\Model\Product')->load($questionModel->getProductId())->save(); //updating in FPC
						$count =count($answerIds);
					}
				}
                $this->messageManager->addSuccessMessage(__('A total of %1 answers(s) have changed status.', $count));
            } catch(\Exception $e) {
                $messages->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('itorisproductQa/answer/index');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_ProductQa::productQA');
    }
}