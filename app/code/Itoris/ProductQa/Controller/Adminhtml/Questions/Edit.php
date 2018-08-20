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
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * Questions grid
     */
    public function execute() {
        $this->resultPageFactory=$this->_objectManager->create('Magento\Framework\View\Result\PageFactory');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Itoris_ProductQa::productQAAllQ');
        $resultPage->addBreadcrumb(__('Edit Question'), __('Edit Question'));
        $resultPage->addBreadcrumb(__('Edit Question'), __('Edit Question'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Question'));

        $questionId = (int)$this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Itoris\ProductQa\Model\Questions');
        $question = $model->getQuestionInfo($questionId);
        switch ($question['submitter_type']) {
            case \Itoris\ProductQa\Model\Questions::SUBMITTER_CUSTOMER:
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($question['customer_id']);
                $question['user_name'] = $customer->getName();
                $question['user_email'] = $customer->getEmail();
                $question['user_type'] = __('Customer');
                break;
            case \Itoris\ProductQa\Model\Questions::SUBMITTER_ADMIN:
                $question['user_type'] = __('Administrator');
                break;
            case \Itoris\ProductQa\Model\Questions::SUBMITTER_VISITOR:
                $question['user_type'] = __('Guest');
                break;
        }
        $registry = $this->_objectManager->create('Itoris\ProductQa\Helper\Data')->getRegistry();
        $registry->register('question', $question);

        $answerCollection = $this->_objectManager->create('Itoris\ProductQa\Model\ResourceModel\Answers\Collection')->questionAnswers($questionId);

        $registry->register('answerCollection', $answerCollection);
        return $resultPage;

    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_ProductQa::productQA');
    }

}