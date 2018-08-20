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


class Add extends \Magento\Backend\App\Action {


    /**
     * Questions grid
     */
    public function execute() {
        $productsIds = $this->getRequest()->getParam('product');
        $data = array(
            'status'         => (int)$this->getRequest()->getParam('status'),
            'submitter_type' => \Itoris\ProductQa\Model\Questions::SUBMITTER_ADMIN,
            'nickname'       => htmlspecialchars($this->getRequest()->getParam('nickname')),
            'content'        => htmlspecialchars($this->getRequest()->getParam('question')),
            'customer_id'    => $this->_objectManager->create('Magento\Backend\Model\Auth\Session')->getUser()->getId(),
            'notify'         => 0,
            'store_id'       => $this->getRequest()->getParam('visible'),
        );
        $messages = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
        try {
            /** @var  $model \Itoris\ProductQa\Model\Questions */
            $model = $this->_objectManager->create('Itoris\ProductQa\Model\Questions');
            for ($i = 0; $i < count($productsIds); $i++) {
                $data['product_id'] = (int)$productsIds[$i];
                $model->addQuestion($data);
				$this->_objectManager->create('Magento\Catalog\Model\Product')->load((int)$productsIds[$i])->save(); //updating in FPC
            }
            $messages->addSuccessMessage(__('Question has been added'));
        } catch(\Exception $e) {
            $messages->addErrorMessage($this->__('Question has not been added'));
        }

        if ($this->getRequest()->getParam('back')) {
            if($this->getRequest()->getParam('productBack')){
                $this->_redirect('*/*/edit', array('id' => $this->_objectManager->get('Magento\Framework\Registry')->registry('q_id'),'productBack'=>$this->getRequest()->getParam('productBack')));
            }else{
                $this->_redirect('*/*/edit', array('id' => $this->_objectManager->get('Magento\Framework\Registry')->registry('q_id')));
                  }

        } else {
            if($this->getRequest()->getParam('productBack')){
                $this->_redirect('catalog/product/edit', array('id' => $this->getRequest()->getParam('productBack')));
            }else{
                $this->_redirect('*/*');
            }
        }

    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_ProductQa::productQA');
    }
}