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


class Save extends \Magento\Backend\App\Action
{

    /**
     * Questions grid
     */
    public function execute()
    {
        $data = array(
            'q_id'     => (int)$this->getRequest()->getParam('id'),
            'status'   => (int)$this->getRequest()->getParam('status'),
            'nickname' => htmlspecialchars($this->getRequest()->getParam('nickname')),
            'content'  => htmlspecialchars($this->getRequest()->getParam('question')),
            'inappr'   => ($this->getRequest()->getParam('inappr')) ? 1 : 0,
        );
        $answers = $this->getRequest()->getParam('answer');
        $questionModel =$this->_objectManager->create('Itoris\ProductQa\Model\Questions');
        $messages = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
        try {
            $questionModel->load($data['q_id'])->setStatus($data['status'])
                ->setNickname($data['nickname'])
                ->setContent($data['content'])
                ->setData('inappr', $data['inappr'])
                ->save();
				//print_r($questionModel->getData()); exit;
            $questionModel->updateVisibility($data['q_id'], $this->getRequest()->getParam('visible'));
            if (!empty($answers)) {
                $answerModel =$this->_objectManager->create('Itoris\ProductQa\Model\Answers');
                foreach ($answers as $key => $value) {
                    if (isset($value['delete'])) {
                        /** @var \Itoris\ProductQa\Helper\Data $helper */
                        $helper = $this->_objectManager->create('Itoris\ProductQa\Helper\Data');
                        $resorce = $helper->getResourceConnection();
                        $tablePrefix = $resorce->getTableName('itoris_productqa_answers');
                        $resorce->getConnection()->query("Delete FROM {$tablePrefix} WHERE id={$key} ");
                        //$answerModel->load($key)->delete($key);
                    } else {
                        $inappr = (isset($value['inappr'])) ? (int)$value['inappr'] : 0;
                        $answerModel->load($key)->setStatus((int)$value['status'])
                            ->setNickname($value['nickname'])
                            ->setContent($value['answer'])
                            ->setData('inappr', $inappr)
                            ->save();
                        if ($value['status'] == \Itoris\ProductQa\Model\Answers::STATUS_APPROVED && $value['status'] != $value['status_before']) {
                            $submitter = $answerModel->load($key)->getSubmitterType();
                            $this->_objectManager->create('Itoris\ProductQa\Model\Notify')->prepareAndSendNotification($data['q_id'], $value['answer'], $submitter, $value['nickname']);
                        }
                    }
                }
            }
            $messages->addSuccessMessage(__('Question has been saved'));
			$this->_objectManager->create('Magento\Catalog\Model\Product')->load($questionModel->getProductId())->save(); //updating in FPC
        } catch(\Exception $e) {
            $messages->addErrorMessage($e->getMessage());
        }
        if ($this->getRequest()->getParam('back')) {
            if($this->getRequest()->getParam('productBack')){
                $this->_redirect('*/*/edit', array('id' => $questionModel->getId(), '_current'=>true,'productBack' => $this->getRequest()->getParam('productBack')));
            }else{
                $this->_redirect('*/*/edit', array('id' => $questionModel->getId(), '_current'=>true));
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