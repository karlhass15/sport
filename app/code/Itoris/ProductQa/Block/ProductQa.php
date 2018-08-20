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
namespace  Itoris\ProductQa\Block;
class ProductQa extends \Magento\Framework\View\Element\Template {
	const SEARCH_QUERY_VAR_NAME = 's';
	protected $questionSortMode = null;
	protected $currentProductId = null;
	protected $searchQuery = null;
    const VISIBLE_ALL = 3;
	const ENABLE_YES = 1;
	const ENABLE_NO = 2;
	const VISIBLE_CUSTOMER = 4;
	const VISITOR_POST_Q_A =5;
	const VISITOR_POST_Q = 6;
	const VISITOR_POST_A = 7;
	const THEME_SHARP_MAGENTO = 8;
	const THEME_SHARP_BLUE = 9;
	const THEME_SHARP_WHITE = 10;
	const THEME_SHARP_GRAY = 11;
	const THEME_SHARP_BLACK = 12;
	const THEME_SMOOTH_MAGENTO = 13;
	const THEME_SMOOTH_BLUE = 14;
	const THEME_SMOOTH_WHITE = 15;
	const THEME_SMOOTH_GRAY = 16;
	const THEME_SMOOTH_BLACK = 17;
	const THEME_SIMPLE = 18;
	const SHOW_ALIKON = 18;
	const SHOW_CAPTCHA = 19;
	const SHOW_SECURIMAGE = 20;
	const NO_CAPTCHA = 0;
	const Q_APPROVAL_MANUAL = 22;
	const Q_APPROVAL_AUTO_CUSTOMER = 23;
	const Q_APPROVAL_AUTO = 24;
	const A_APPROVAL_MANUAL = 25;
	const A_APPROVAL_AUTO_CUSTOMER = 26;
	const A_APPROVAL_AUTO = 27;
	const VISITORS_RATE_ALL = 28;
	const VISITORS_RATE_INAPPR = 29;
	protected $settings;
	protected function _construct() {
		parent::_construct();
		if ($this->getRequest()->getParam(self::SEARCH_QUERY_VAR_NAME)) {
			$this->setSearchQuery($this->getRequest()->getParam(self::SEARCH_QUERY_VAR_NAME));
		}
	}
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
	public function getCoreRegistry(){
		return $this->getObjectManager()->get('Magento\Framework\Registry');
	}
	protected function _prepareLayout() {
		/** @var  $messages \Magento\Framework\Message\ManagerInterface */
		$messages = $this->getObjectManager()->get('Magento\Framework\Message\ManagerInterface');
		if(!$this->getDataHelper()->isEnabled()){
			return;
		}
		if($this->getDataHelper()->getState()=='frontend' && !$this->getDataHelper()->isEnabledCustomer()){
			return;
		}
		try {
			$this->prepareSettings();
		} catch (\Exception $e) {
			$messages->addErrorMessage($e->getMessage());
			return;
		}

		$this->getObjectManager()->get('Magento\Customer\Model\Session')->setBeforeAuthUrl($this->getObjectManager()->get('Magento\Framework\Url')->getCurrentUrl());
		$subscribed = false;
		if ($this->getCustomerId()) {
			if ($this->getRequest()->getParam('question')) {
				$this->setShowQuestionForm(true);
			} else {
				$this->setShowQuestionForm(false);
			}
			$customerEmail = $this->getObjectManager()->get('Magento\Customer\Model\Session')->getCustomer()->getEmail();
			$subscribed = (bool)$this->getObjectManager()->create('Magento\Newsletter\Model\Subscriber')->loadByEmail($customerEmail)->getId();
			if ($this->getRequest()->getParam('page')) {
				$this->getCoreRegistry()->unregister('page');
				$this->getCoreRegistry()->register('page', (int)$this->getRequest()->getParam('page'));
				$this->setShowAnswer((int)$this->getRequest()->getParam('answer'));
				$this->setShowQuestionInfo(true);
			}
			if ($this->getRequest()->getParam('form')) {
				$this->setShowAnswerForm((int)$this->getRequest()->getParam('form'));
			}
		}
		$this->setIsSubscribed($subscribed);
		$settings = $this->getSettings();

		if ($this->getDataHelper()->isEnabled()) {
			if ($this->visibility($settings->getVisible())) {
				$this->setTemplate('Itoris_ProductQa::productqa.phtml');
			}
		}
	}
	public function canSubscribeOnQuestion() {
		return $this->getDataHelper()->getSettings($this->getDataHelper()->getStoreManager()->getStore()->getId())->getAllowSubscribingQuestion();
	}
	/**
	 * Prepare productQ&A settings
	 * Don't allow create productQ&A block if it already exists on the page
	 *
	 * @throws Exception
	 */
	protected function prepareSettings() {
		$this->setCustomerId($this->getDataHelper()->getSession()->getId());

		$product = $this->getDataHelper()->getRegistry()->registry('product');
		if (empty($product)) {
			throw new \Exception('Product Questions/Answers is not allowed on this page!');
		}
		$this->setProductId($product->getId());
		$this->setStoreId($this->getDataHelper()->getStoreManager()->getStore()->getId());
		$this->setWebsiteId($this->getDataHelper()->getStoreManager()->getWebsite()->getId());
		$this->getDataHelper()->getRegistry()->unregister('storeId');
		$this->getDataHelper()->getRegistry()->register('storeId', $this->getStoreId());

		$this->getSettings();
         if(!$this->getDataHelper()->getRegistry()->registry('perPage'))
		$this->getDataHelper()->getRegistry()->register('perPage', $this->getSettings()->getQuestionsPerPage());
		$page = $this->getRequest()->getParam('page');
		$this->getDataHelper()->getRegistry()->unregister('page');
		if(!$this->getDataHelper()->getRegistry()->registry('page'))
		$this->getDataHelper()->getRegistry()->register('page', $page ? $page : 1);
		$this->getDataHelper()->getRegistry()->unregister('settings');
		$this->getDataHelper()->getRegistry()->register('settings', $this->getSettings());
		$sort = $this->getRequest()->getParam('sort');
		if (!$sort) {
			$sort = \Itoris\ProductQa\Model\Questions::SORT_RECENT;
		}
		$this->setQA($this->getProductId(), $sort);
	}

	/**
	 * @return Itoris\ProductQa\ModelSettings
	 */
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

	/**
	 * Set sorted by sort parameter questions and answers for product
	 *
	 * @param $productId
	 * @param int $mode
	 */
	protected function setQA($productId, $mode = \Itoris\ProductQa\Model\Questions::SORT_RECENT, $includeQuestionId = null) {
		$this->questionSortMode = $mode;
		$this->currentProductId = $productId;
		$questions = $this->getDataHelper()->getQuestionModel()->getQuestions($productId, $mode, $includeQuestionId, $this->getSearchQuery());
		$questionsIds = array();
		foreach ($questions as $question) {
			$questionsIds[] = $question['id'];
		}
		if (!empty($questionsIds)) {
			$this->setAnswers($this->getDataHelper()->getAnswerModel()->getAnswers($questionsIds));
			$this->setQuestions($this->attachAnswersToQuestions($questions));
		}
	}

	protected function attachAnswersToQuestions($questions) {
		foreach ($this->getAnswers() as $answer) {
			foreach ($questions as $key => $question) {
				if($question['id'] == $answer['q_id'])
					$questions[$key]['answer'][] = $answer;
			}
		}
		return $questions;
	}

	/**
	 * Is visible productQ&A for the current user
	 *
	 * @param $visibleCode
	 * @return bool
	 */
	private function visibility($visibleCode) {
		if (($visibleCode == self::VISIBLE_ALL)
			|| ($visibleCode == self::VISIBLE_CUSTOMER && $this->getCustomerId())
		) {
			return true;
		}
		return false;
	}



	public function getQuestionsHtml($questions) {
		$questionBlock = $this->getLayout()->createBlock('Itoris\ProductQa\Block\Form\Question');
		$questionBlock->setSortMode($this->questionSortMode)
			->setProductId($this->currentProductId)
			->setQuestions($questions)
			->setTemplate('Itoris_ProductQa::productqaform.phtml');
		return $questionBlock->toHtml();
	}

	protected function getAnswersHtml($answers) {

		$answersBlock = $this->getLayout()->createBlock('Itoris\ProductQa\Block\Form\Answer');

		$answersBlock->setTemplate('Itoris_ProductQa::formAnswer.phtml');
		$answersBlock->setAnswers($answers);
		return $answersBlock->toHtml();
	}

	public function getQuestionStatus() {
		$status = $this->getSettings()->getQuestionApproval();
		if (($status == self::Q_APPROVAL_AUTO)
			|| ($status == self::Q_APPROVAL_AUTO_CUSTOMER && $this->getCustomerId())
		) {
			$status = \Itoris\ProductQa\Model\Questions::STATUS_APPROVED;
		} else {
			$status = \Itoris\ProductQa\Model\Questions::STATUS_PENDING;
		}

		return $status;
	}

	public function getAnswerStatus() {
		$status = $this->getSettings()->getAnswerApproval();
		if (($status == self::A_APPROVAL_AUTO)
			|| ($status == self::A_APPROVAL_AUTO_CUSTOMER && $this->getCustomerId())
		) {
			$status = \Itoris\ProductQa\Model\Answers::STATUS_APPROVED;
		} else {
			$status = \Itoris\ProductQa\Model\Answers::STATUS_PENDING;
		}
		return $status;
	}
	public function getModeSortValues() {

		return  array(
			array(
				'value' => \Itoris\ProductQa\Model\Questions::SORT_RECENT,
				'label' =>  $this->getEscaper()->escapeHtml(__('Most Recent Questions')),
			),
			array(
				'value' => \Itoris\ProductQa\Model\Questions::SORT_OLDEST,
				'label' =>  $this->getEscaper()->escapeHtml(__('Oldest Questions')),
			),
			array(
				'value' => \Itoris\ProductQa\Model\Questions::SORT_HELPFUL_ANSWERS,
				'label' => $this->getEscaper()->escapeHtml(__('Questions With The Most Helpful Answers')),
			),
			array(
				'value' => \Itoris\ProductQa\Model\Questions::SORT_RECENT_ANSWERS,
				'label' =>  $this->getEscaper()->escapeHtml(__('Questions With Most Recent Answers')),
			),
			array(
				'value' => \Itoris\ProductQa\Model\Questions::SORT_OLDEST_ANSWERS,
				'label' =>  $this->getEscaper()->escapeHtml(__('Questions With  Oldest Answers')),
			),
			array(
				'value' => \Itoris\ProductQa\Model\Questions::SORT_MOST_ANSWERS,
				'label' => $this->getEscaper()->escapeHtml(__('Questions With Most Answers')),
			),
		);
	}

	/**
	 * Get input html element with a value equal to question status
	 *
	 * @deprecated
	 * @return string
	 */
	protected function getQuestionStatusHtml() {

		return '<input type="hidden" id="itoris_question_status" name="status" value="' . $this->getQuestionStatus() . '"/>';
	}

	/**
	 * Get input html element with a value equal to answer status
	 *
	 * @deprecated
	 * @return string
	 */
	public function getAnswerStatusHtml() {
		return '<input type="hidden" id="itoris_answer_status" name="status" value="' . $this->getAnswerStatus() . '"/>';
	}

	/**
	 * Get input html element with a value equal to a product id
	 *
	 * @deprecated
	 * @return string
	 */
	public function getProductIdHtml() {
		return '<input type="hidden" name="product_id" value="' . $this->getProductId() . '"/>';
	}

	/**
	 * Get input html element with a value equal to a store id
	 *
	 * @deprecated
	 * @return string
	 */
	protected function getStoreIdHtml() {
		return '<input type="hidden" name="store_id" value="' . $this->getStoreId() . '"/>';
	}

	/**
	 * Config for ProductQa js object
	 *
	 * @return string
	 */
	public function getConfigJson() {
		$config = array(
			'allowRateGuestAll'      => $this->canVisitorRate(),
			'allowRateGuestInappr'   => $this->canVisitorRateInappr(),
			'search_query_var'       => self::SEARCH_QUERY_VAR_NAME,
			'default_search_message' =>__('Search phrase'),
		);
		return \Zend_Json::encode($config);
	}
	public function canVisitorRate() {
		return $this->getSettings()->getVisitorCanRate() == self::VISITORS_RATE_ALL;
	}

	public function canVisitorRateInappr() {
		return  $this->getSettings()->getVisitorCanRate() ||  $this->getSettings()->getVisitorCanRate() == self::VISITORS_RATE_INAPPR;
	}
	public function getCurrentSortModeLabel() {
		foreach ($this->getModeValues() as $mode) {
			if ($mode['value'] == $this->questionSortMode) {
				return $mode['label'];
			}
		}
		return '';
	}

	public function setSearchQuery($query) {
		$this->searchQuery = (string)$query;
		return $this;
	}

	public function getSearchQuery() {
		return $this->searchQuery;
	}
/** @return \Magento\Framework\Escaper */
	protected function getEscaper(){
		if(!$this->_escaper){
			$this->_escaper = $this->getObjectManager()->get('Magento\Framework\Escaper');
		}
		if(!$this->_escaper){
			$this->_escaper = $this->getObjectManager()->create('Magento\Framework\Escaper');
		}
		return $this->_escaper;
	}
}
