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
namespace Itoris\ProductQa\Model;
class Notify {

	const TYPE_ANSWER = 1;
	const TYPE_QUESTION = 2;
	const ADMIN = 'Admin';
	const CUSTOMER = 'User';
	const GUEST = 'Guest';

	protected $questionOrAnswer = 'question_or_answer';
	protected $productUrl='product_url';
	protected $questionOrAnswerText = 'question_or_answer_text';
	protected $storeViewName = 'store_view_name';
	protected $userType = 'user_type';
	protected $nickname = 'nickname';
	protected $productName = 'product_name';
	protected $questionOrAnswerDetails = 'question_or_answer_details';
	protected $customerFirstName = 'customer_first_name';
	protected $question = 'question';
	protected $answer = 'answer';
	protected $questionDetailsBackendUrl = 'question_details_backend_url';
	protected $questionId = 'question_id';
	protected $reply = 'reply';
	protected $username = 'username';
	protected $productPage = 'product_page';
	protected $_objectManager;
	protected $_helper;
	public function getObjectManager(){
		return $this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
	}
	protected function _construct() {
		$this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
	}
	/** @return \Itoris\ProductQa\Helper\Data */
	public function getDataHelper(){
		if(!$this->_helper){
			$this->_helper=$this->getObjectManager()->create('Itoris\ProductQa\Helper\Data');
		}
		return $this->_helper;
	}
	public function sendNotification($data, $whom, $settings = null) {
		if($this->getDataHelper()->getState()=='adminhtml'){
			/** @var $emailTemplate \Magento\Email\Model\BackendTemplate */
			$emailTemplate = $this->getObjectManager()->create('Magento\Email\Model\BackendTemplate');
		}else{
			/** @var $emailTemplate \Magento\Email\Model\Template */
			$emailTemplate = $this->getObjectManager()->create('Magento\Email\Model\Template');
		}
		$settings = is_null($settings) ? $this->getDataHelper()->getRegistry()->registry('settings') : $settings;
		$templateId = $settings->__call('getTemplate'. $whom .'Notification', null);
		if(is_numeric($templateId)){
			$templateText = $emailTemplate->load($templateId)->getTemplateText();
		}else{
			$templateText = $emailTemplate->loadDefault($templateId)->getTemplateText();
		}
		if($templateId) {
			$typeText = $this->getDataHelper()->getStoreManager()->getStore()->getName() . '.New .. received';
			switch ($data['type']) {
				case self::TYPE_QUESTION:
					$class = "Itoris\ProductQa\Model\Questions";
					$type = __('question');
					$typeText = 'Question';
					break;
				case self::TYPE_ANSWER:
					$class = "Itoris\ProductQa\Model\Answers";
					$type = __('answer');
					$typeText = 'Answer';
					break;
			}

			if (isset($data['product_page'])) {
				$productUrl = '<a href="' . $data['product_page'] . '">' . $data['product_name'] . '</a>';
			} else {
				$productUrl = $data['product_name'];
			}


			$typeText = $this->getDataHelper()->getStoreManager()->getStore()->getName() . '. '.__('New ' . $typeText . ' received');
			$escaper = $this->getDataHelper()->getEscaper();
			$emailTemplateVariables = array(
				$this->questionOrAnswer => htmlspecialchars_decode($escaper->escapeHtml($type)),
				$this->questionOrAnswerText => $typeText,
				$this->storeViewName => htmlspecialchars_decode($escaper->escapeHtml($data['store_name'])),
				$this->userType => htmlspecialchars_decode($escaper->escapeHtml($this->getDataHelper()->getUserType($class, $data['user_type']))),
				$this->nickname => htmlspecialchars_decode($escaper->escapeHtml($data['nickname'])),
				$this->productName => htmlspecialchars_decode($escaper->escapeHtml($data['product_name'])),
				$this->questionOrAnswerDetails => $escaper->escapeHtml($data['qa_details']),
				$this->productUrl => htmlspecialchars_decode($escaper->escapeHtml($productUrl)),
				$this->questionDetailsBackendUrl => htmlspecialchars_decode($escaper->escapeHtml($this->getDataHelper()->getHtmlLink($data['q_url']))),
                $this->questionId => isset($data['q_id']) ? $data['q_id'] : 0,
				$this->answer => htmlspecialchars_decode($escaper->escapeHtml($data['qa_details'])),
				$this->question => htmlspecialchars_decode($escaper->escapeHtml((isset($data['question_details'])) ? $data['question_details'] : '')),
				$this->customerFirstName => htmlspecialchars_decode($escaper->escapeHtml((isset($data['customer_name'])) ? $data['customer_name'] : '')),
				$this->reply => $escaper->escapeHtml($data['qa_details']),
				$this->username => htmlspecialchars_decode($escaper->escapeHtml(isset($data['username']) ? $data['username'] : '')),
				$this->productPage => htmlspecialchars_decode($escaper->escapeHtml(isset($data['product_page']) ? $data['product_page'] : '')),
			);
			$this->getDataHelper()->startEnvironmentEmulation($this->getDataHelper()->getStoreManager()->getStore()->getId());
			$templateText = $this->prepareTemplate($templateText, $emailTemplateVariables);
			$emailTemplate->setTemplateText(htmlspecialchars_decode($templateText));
			$senderPath = $settings->__call('getSender' . $whom . 'Subject', null);
			$emailTemplate->setSenderName($this->prepareTemplate($this->getDataHelper()->getScopeConfig()->getValue('trans_email/ident_' . $senderPath . '/email'), $emailTemplateVariables));
			$emailTemplate->setSenderEmail($this->getDataHelper()->getScopeConfig()->getValue('trans_email/ident_' . $senderPath . '/name'));
			$emailTo = (isset($data['customer_email'])) ? $data['customer_email'] : $settings->getAdminEmail();
			$this->getDataHelper()->setEmailTo($emailTo);
			$this->getDataHelper()->setName($this->getDataHelper()->setName((isset($data['customer_name'])) ? $data['customer_name'] : ''));
			$this->getDataHelper()->stopEnvironmentEmulation();
			$this->getDataHelper()->sendEmail($templateId, $emailTemplateVariables, $this->getDataHelper()->getStoreManager()->getStore(), $senderPath, $emailTemplate);
		}
	}

	public function prepareAndSendNotification($questionId, $answerText, $submitterType, $nickname) {
		if(!$this->_objectManager){
			$this->getObjectManager();
		}
		$question = $this->_objectManager->create('Itoris\ProductQa\Model\Questions')->load($questionId);
		/** @var $product \Magento\Catalog\Model\Product */
		$product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($question->getProductId());
		$productName = $product->getName();
		$data = array(
			'type'             => \Itoris\ProductQa\Model\Notify::TYPE_ANSWER,
			'user_type'        => $submitterType,
			'nickname'         => $nickname,
			'product_name'     => $productName,
			'qa_details'       => $answerText,
			'q_url'            => $this->getDataHelper()->getUrl('itorisproductqa/questions/edit/id/'. $questionId),
			'question_details' => $question->getContent(),
			'username'         => $question->getNickname(),
			'product_page'     =>$product->getProductUrl(),
			'customer_email'   => $question->getEmail(),
		);
		if ($question->getCustomerId()) {
			$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($question->getCustomerId());
			$customerName = $customer->getFirstname();
			$customerEmail = $customer->getEmail();
			$data['customer_name'] = $customerName;
			$data['customer_email'] = $customerEmail;
		}
		$storeIds = $this->_objectManager->create('Itoris\ProductQa\Model\Questions')->getQuestionVisibility($questionId);
		foreach ($storeIds as $value) {
			/** @var  $store \Magento\Store\Model\StoreManagerInterface */
			$store = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore($value['store_id']);
			$data['store_name'] = $store->getName();
			$websiteId = $store->getWebsiteId();
			$settings = $this->getDataHelper()->getSettings($value['store_id']);
			if(!$this->getDataHelper()->getRegistry()->registry('settings')){
				$this->getDataHelper()->getRegistry()->register('settings',$settings);
			}
			$question->sendNotifications($data);
			break;
		}
	}

	/**
	 * Insert variables into a template
	 *
	 * @param $template
	 * @param $variables
	 * @return string
	 */
	protected function prepareTemplate($template, $variables) {
		foreach ($variables as $key => $value) {
			$template = str_replace('{{' . $key . '}}', $value, $template);
		}
		return $template;
	}
}
