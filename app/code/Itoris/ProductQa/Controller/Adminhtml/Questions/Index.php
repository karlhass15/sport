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
Class Index  extends \Magento\Backend\App\Action {

	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;
	/**
	 * Questions grid
	 */
	public function execute() {
		$this->_objectManager->get('Magento\Framework\Registry')->register('questionsPage', (int)$this->getRequest()->getParam('questionsPage'));
		$this->resultPageFactory=$this->_objectManager->create('Magento\Framework\View\Result\PageFactory');
		if($this->_objectManager->create('Itoris\ProductQa\Helper\Data')->isEnabled()) {
			try {
				$collection = $this->_objectManager->create('Itoris\ProductQa\Model\ResourceModel\Questions\Collection');
			} catch (\Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			$this->_objectManager->get('Magento\Framework\Registry')->register('questions', $collection);

			$resultPage = $this->resultPageFactory->create();
			$resultPage->setActiveMenu('Itoris_ProductQa::productQAAllQ');
			$resultPage->addBreadcrumb(__('All Questions'), __('All Questions'));
			$resultPage->addBreadcrumb(__('Manage All Questions'), __('Manage All Questions'));
			$resultPage->getConfig()->getTitle()->prepend(__('All Questions'));
			return $resultPage;
		}else{
			$resultPage = $this->resultPageFactory->create();
			$resultPage->setActiveMenu('Itoris_ProductQa::productQAAllQ');
			$resultPage->addBreadcrumb(__('All Questions'), __('All Questions'));
			$resultPage->addBreadcrumb(__('Manage All Questions'), __('Manage All Questions'));
			$resultPage->getConfig()->getTitle()->prepend(__('All Questions'));
			return $resultPage;
		}
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Itoris_ProductQa::productQA');
	}

}
