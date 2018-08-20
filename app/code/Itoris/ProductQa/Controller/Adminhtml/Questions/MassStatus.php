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

namespace Itoris\ProductQa\Controller\Adminhtml\Questions;


class MassStatus extends  \Magento\Backend\App\Action {
    /**
     * Questions grid
     */
    public function execute() {
        $questionsIds = $this->getRequest()->getParam('question');
		$status = (int)$this->getRequest()->getParam('status');
        $messages = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
        if (empty($questionsIds)) {
            $messages->addErrorMessage(__('Please select question(s).'));
        } else {
            try {
				if (!trim(implode('', $questionsIds))){
					$collection = $this->_objectManager->create('Itoris\ProductQa\Model\Questions')->getCollection();
					foreach($collection as $model) {
						$model->setStatus($status)->save();
						$this->_objectManager->create('Magento\Catalog\Model\Product')->load($model->getProductId())->save(); //updating in FPC
					}
					$count = count($collection);
				} else {
					foreach ($questionsIds as $id) {
						$model =$this->_objectManager->create('Itoris\ProductQa\Model\Questions');
						$model->load($id)->setStatus($status)->save();
						$this->_objectManager->create('Magento\Catalog\Model\Product')->load($model->getProductId())->save(); //updating in FPC
						$count =count($questionsIds);
					}
				}
                $this->messageManager->addSuccessMessage(__('A total of %1 question(s) have been changed status.', $count));
            } catch(\Exception $e) {
                $messages->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('itorisproductQa/questions/');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_ProductQa::productQA');
    }
}