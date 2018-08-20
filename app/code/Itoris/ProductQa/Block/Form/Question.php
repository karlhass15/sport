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

/**
 * @method setSortMode()
 * @method getSortMode()
 */
namespace Itoris\ProductQa\Block\Form;
class Question extends \Magento\Framework\View\Element\Template {

	protected $questions = array();
	protected $activeAnswer = null;
	protected $isActiveQuestionInfo = false;
	protected $activeQuestions = array();
	protected $currentProductUrl = '';
	protected $captcha;
	protected $refreshUrl='';
	protected function _construct() {
		$this->setCustomerId($this->getDataHelper()->getSession()->getId());
		if (!is_null($this->getRequest()->getParam('answer', null))) {
			$this->activeAnswer = (int)$this->getRequest()->getParam('answer');
		}
		$activeQuestons = $this->getRequest()->getParam('question_id', array());
		if (!is_array($activeQuestons)) {
			$activeQuestons = array($activeQuestons);
		}

		$this->activeQuestions = $activeQuestons;
		$this->isActiveQuestionInfo = (bool)$this->getRequest()->getParam('page');
	}

	public function getActiveAnswer() {
		return $this->activeAnswer;
	}

	public function getIsActiveQuestionInfo() {
		return $this->isActiveQuestionInfo;
	}

	public function canShowQuestionInfo($answerNum, $questionId) {
		return $this->isQuestionActive($questionId) || ($this->getIsActiveQuestionInfo() && $this->getActiveAnswer() === $answerNum);
	}

	public function isQuestionActive($num) {
		return in_array($num, $this->activeQuestions);
	}

	public function setQuestions($questions) {
		if (is_array($questions)) {
			$this->questions = $questions;
		}

		return $this;
	}

	public function getQuestions() {
		return $this->questions;
	}

	public function getAnswersHtml($answers) {
		$answersBlock = $this->getLayout()->createBlock('Itoris\ProductQa\Block\Form\Answer');
		$answersBlock->setAnswers($answers);
		return $answersBlock->toHtml();
	}

	public function canSubscribeOnQuestion() {
		return $this->getDataHelper()->getSettings($this->getDataHelper()->getStoreManager()->getStore()->getId())->getAllowSubscribingQuestion();
	}

	public function isSubscribedToQuestion($questionId) {
		if (!$this->isGuest()) {
			$question = $this->getDataHelper()->getQuestionModel()->load($questionId);
			$customerId = $this->getDataHelper()->getSession()->getCustomer()->getId();
			if ($question->getId() && $question->getNotify() && $question->getCustomerId() == $customerId) {
				return true;
			}
			return $this->getObjectManager()->create('Itoris\ProductQa\Model\Question\Subscriber')->isSubscribed($questionId, $customerId);
		}
		return false;
	}

	public function isGuest() {
		return !$this->getDataHelper()->getSession()->isLoggedIn();
	}

	public function preparePageUrl($page) {
		$currentUrl = $this->currentProductUrl;
		$currentUrl .= strpos($currentUrl, '?') === false ? '?' : '&';
		$currentUrl .= 'sort=' . $this->getSortMode();
		$currentUrl .= '&page=' . $page;
		return $currentUrl;
	}

	public function setProductId($productId) {
		$this->setData('product_id', $productId);
		if ($this->getDataHelper()->getRegistry()->registry('current_product') && $this->getDataHelper()->getRegistry()->registry('current_product')->getId() == $productId) {
			$this->currentProductUrl = $this->getDataHelper()->getRegistry()->registry('current_product')->getProductUrl();
		} else {
			$this->currentProductUrl = $this->getObjectManager()->create('Magento\Catalog\Model\Product')->load($productId)->getProductUrl();
		}
		return $this;
	}

	public function isSearchRequest() {
		return $this->getRequest()->getParam(\Itoris\ProductQa\Block\ProductQa::SEARCH_QUERY_VAR_NAME);
	}
	public function getSettings() {
		if (is_null($this->settings)) {
			$this->settings =
				$this->getDataHelper()
					->getSettings($this->getDataHelper()
						->getStoreManager()
						->getStore()
						->getId()
					);
		}

		return $this->settings;
	}
	protected $settings;
	protected $_helper;
	protected $_objectManager;
	public function getObjectManager(){
		if($this->_objectManager)
			return $this->_objectManager;
		return $this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
	}
	public function getAnswerStatus() {
		$status = $this->getSettings()->getAnswerApproval();
		if (($status == \Itoris\ProductQa\Block\ProductQa::A_APPROVAL_AUTO)
			|| ($status == \Itoris\ProductQa\Block\ProductQa::A_APPROVAL_AUTO_CUSTOMER && $this->getCustomerId())
		) {
			$status = \Itoris\ProductQa\Model\Answers::STATUS_APPROVED;
		} else {
			$status = \Itoris\ProductQa\Model\Answers::STATUS_PENDING;
		}
		return $status;
	}
	/** @return \Itoris\ProductQa\Helper\Data */
	public function getDataHelper(){
		if(!$this->_helper){
			$this->_helper=$this->getObjectManager()->create('Itoris\ProductQa\Helper\Data');
		}
		return $this->_helper;
	}
	public function getCaptcha(){
		if(!$this->captcha){

			$this->captcha = $this->getLayout()->getBlock('captchaAnswer')->toHtml();
		}
		return   $this->captcha;
	}
	public function getRefreshUrl()
	{
		$store = $this->getDataHelper()->getStoreManager()->getStore();
		return $store->getUrl('captcha/refresh', ['_secure' => $store->isCurrentlySecure()]);
	}
	public function getRequestRefresh()
	{
		return $this->refreshUrl; // TODO: Change the autogenerated stub
	}
}
