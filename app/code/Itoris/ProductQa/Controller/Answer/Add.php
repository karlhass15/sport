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
namespace Itoris\ProductQa\Controller\Answer;
class Add extends \Magento\Framework\App\Action\Action
{
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
  public function execute()
  {
      $output = array();
      $customerId =$this->getDataHelper()->getSession()->getId();
      $submitter_type = ($customerId) ? \Itoris\ProductQa\Model\Answers::SUBMITTER_CUSTOMER
          : \Itoris\ProductQa\Model\Answers::SUBMITTER_VISITOR;

      $storeId = $this->getDataHelper()->getStoreManager()->getStore()->getId();
      $settings = $this->getDataHelper()->getSettings($storeId);
      $messages = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
      $questionId = (int)$this->getRequest()->getParam('question_id');
      $question =$this->getDataHelper()->getQuestionModel()->load($questionId);
      $productId = $question->getProductId();
      $data = array(
          'status' => $this->getRequest()->getParam('status'),
          'submitter_type' => $submitter_type,
          'q_id' => $questionId,
          'nickname' => $this->getRequest()->getParam('nickname_answer'),
          'content' => htmlspecialchars($this->getRequest()->getParam('answer')),
          'customer_id' => $customerId,
          'newsletter' => ($this->getRequest()->getParam('newsletter')) ? 1 : 0,
          'newsletter_email' => $this->getRequest()->getParam('newsletter_email', null),
          'product_id' => $productId,
      );
      try {
          $answer = $this->_objectManager->get('Itoris\ProductQa\Model\Answers');
          if (!$answer) {
              $answer = $this->getObjectManager()->create('Itoris\ProductQa\Model\Answers');
          }
          $output['subscribe'] = $answer->addAnswer($data);
          if(!$this->getDataHelper()->getRegistry()->registry('settings')){
              $this->getDataHelper()->getRegistry()->register('settings', $settings);
          }

          /** @var $product \Magento\Catalog\Model\Product */
          $product = $this->getObjectManager()->create('Magento\Catalog\Model\Product')->load($productId);
          $url = $this->getDataHelper()->getProductUrl($productId,$storeId,$product);
          $url = $url['url_in_store'];
          if(!$url){
              $url = $product->getName();
          }
          $notification = array(
              'store_name'   => $this->getDataHelper()->getStoreManager()->getStore($storeId)->getName(),
              'user_type'    => $data['submitter_type'],
              'nickname'     => $data['nickname'],
              'product_name' => $product->getName(),
              'q_id'        => (int)$data['q_id'],
              'q_url'        => $this->getObjectManager()->create('Magento\Backend\Helper\Data')->getUrl('itorisproductQa/questions/edit', array('id' => (int)$data['q_id'])),
              'qa_details'   => $data['content'],
              'type'         => \Itoris\ProductQa\Model\Notify::TYPE_ANSWER,
              'username'     => $question->getNickname(),
              'product_page' => $url,
          );
          if ($settings->getTemplateAdminNotification()) {
             $this->getObjectManager()->create('Itoris\ProductQa\Model\Notify')->sendNotification($notification, \Itoris\ProductQa\Model\Notify::ADMIN);
          }
          if ($settings->getAnswerApproval() == \Itoris\ProductQa\Block\ProductQa::A_APPROVAL_AUTO
              || ($customerId && $settings->getAnswerApproval() == \Itoris\ProductQa\Block\ProductQa::A_APPROVAL_AUTO_CUSTOMER)
          ) {
              $question->sendNotifications($notification);
          }
          if ($this->getRequest()->getParam('status') == \Itoris\ProductQa\Model\Answers::STATUS_APPROVED) {
              $ajax = $this->getObjectManager()->create('Itoris\ProductQa\Block\ProductQaAjax');
              $output['html'] = $ajax->getHtmlForAnswers($questionId);
              $output['messagess'] = __('Thank you for submitting your answer!');
          } else {
              $output['messagess'] = __('Thank you for submitting your answer! It will appear after moderation.');
          }
          $this->getResponse()->setBody(\Zend_Json::encode($output));
      } catch (\Exception $e) {
          $messages->addErrorMessage($e->getMessage());
          $output['error'] = true;
          $this->getResponse()->setBody(\Zend_Json::encode($output));
      }
  }
}