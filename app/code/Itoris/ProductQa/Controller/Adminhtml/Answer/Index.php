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
Class Index  extends \Magento\Backend\App\Action {

	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;
	/**
	 * Questions grid
	 */
	public function execute() {
		$this->resultPageFactory = $this->_objectManager->create('Magento\Framework\View\Result\PageFactory');
		if($this->_objectManager->create('Itoris\ProductQa\Helper\Data')->isEnabled()) {
			$this->_objectManager->get('Magento\Framework\Registry')->register('answersPage', (int)$this->getRequest()->getParam('answersPage'));
			try {
				$collection = $this->_objectManager->create('Itoris\ProductQa\Model\ResourceModel\Answers\Collection');
			} catch (\Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			$this->_objectManager->get('Magento\Framework\Registry')->register('answers', $collection);

			$resultPage = $this->resultPageFactory->create();
			$resultPage->setActiveMenu('Itoris_ProductQa::productQAAllA');
			$resultPage->addBreadcrumb(__('Edit Questions'), __('All Answers'));
			$resultPage->addBreadcrumb(__('Edit Questions'), __('All Answers'));
			$resultPage->getConfig()->getTitle()->prepend(__('All Answers'));
			return $resultPage;
		}else{
			$resultPage = $this->resultPageFactory->create();
			$resultPage->setActiveMenu('Itoris_ProductQa::productQAAllA');
			$resultPage->addBreadcrumb(__('Edit Questions'), __('All Answers'));
			$resultPage->addBreadcrumb(__('Edit Questions'), __('All Answers'));
			$resultPage->getConfig()->getTitle()->prepend(__('All Answers'));
			return $resultPage;

		}

	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Itoris_ProductQa::productQA');
	}


}
