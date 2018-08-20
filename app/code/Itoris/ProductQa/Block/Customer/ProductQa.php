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
namespace Itoris\ProductQa\Block\Customer;
class ProductQa extends   \Magento\Framework\View\Element\Template {
	protected $_helper;
	protected $_objectManager;
	public function getObjectManager(){
		if($this->_objectManager)
			return $this->_objectManager;
		return $this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
	}

	/** @return \Itoris\ProductQa\Helper\Data */
	public function getDataHelper(){
		if(!$this->_helper){
			$this->_helper=$this->getObjectManager()->create('Itoris\ProductQa\Helper\Data');
		}
		return $this->_helper;
	}
    protected function _prepareLayout() {
		if (!$this->getDataHelper()->isEnabled()) {
			return;
		}
		$customerId = $this->getDataHelper()->getSession()->getId();

		$questions = $this->getObjectManager()->create('Itoris\ProductQa\Model\ResourceModel\Questions\Collection')
						->getCustomerQuestions($customerId);

		$this->setQuestions($questions);

		$answers = $this->getObjectManager()->create('Itoris\ProductQa\Model\ResourceModel\Answers\Collection')
						->getCustomerAnswers($customerId);
                        
		$this->setAnswers($answers);
        if($this->getObjectManager()->get('Magento\Framework\App\FrontControllerInterface')){
			$controllerFront= $this->getObjectManager()->get('Magento\Framework\App\FrontControllerInterface');
		}else{
			$controllerFront= $this->getObjectManager()->create('Magento\Framework\App\FrontControllerInterface');
		}

		parent::_prepareLayout();

		$questionsPager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'questions.pager')->setPageVarName('questions_pager')
            ->setCollection($this->getQuestions());
        $this->setChild('questions_pager', $questionsPager);
        $this->getQuestions()->load();
		$answersPager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'answers.pager')->setPageVarName('answers_pager')
				->setCollection($this->getAnswers());
		$this->setChild('answers_pager', $answersPager);
		$this->getAnswers()->load();
		if($this->getRequest()->getParam('answers_pager')){

		}


        return $this;
    }

	/**
	 * Get question status html
	 *
	 * @param $item
	 * @return string
	 */
	public function getHtmlStatusQ($item) {
		switch($item->getStatus()){
			case \Itoris\ProductQa\Model\Questions::STATUS_PENDING:
				return __('Pending');
				break;
			case \Itoris\ProductQa\Model\Questions::STATUS_APPROVED:
				$html = '<span style="color: green">('. $item->getAnswers() .' '. __('answers') .')</span><br/>
						<a href="'. $this->getProductUrlInStore() .'">
						'. __('View Details') .'</a>';
				return $html;
				break;
		}
	}

	/**
	 * Get answer status html
	 *
	 * @param $item
	 * @return string
	 */
	public function getHtmlStatusA($item) {
		switch($item->getStatus()){
			case \Itoris\ProductQa\Model\Answers::STATUS_PENDING:
				return __('Pending');
				break;
			case \Itoris\ProductQa\Model\Answers::STATUS_APPROVED:
				$html = '<a href="'. $this->getProductUrlInStore() .'">
						'. __('View Details') .'</a>';
				return $html;
				break;
		}
	}

	/**
	 * @param $id
	 * @param $storeId
	 */
	public function prepareProductUrl($id, $storeId) {
		$urls = $this->getDataHelper()->getProductUrl($id, $storeId);
		$this->setProductUrl($urls['url']);
		$this->setProductUrlInStore($urls['url_in_store']);
	}
}
