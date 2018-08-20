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
namespace Itoris\ProductQa\Controller\Question;
class Add extends \Magento\Framework\App\Action\Action
{
    protected $captchaStringResolver; //Magento\Captcha\Observer
    protected $_helper;
    protected $_objectManager;
    protected $logger;
    public function logger(){
        if($this->logger)
            return $this->logger;
        return $this->logger=$this->getObjectManager()->create('Cm\RedisSession\Handler\LoggerInterface');
    }
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
      $output = null;
      $customerId =$this->getDataHelper()->getSession()->getCustomer()->getId();
      $submitter_type = ($customerId) ? \Itoris\ProductQa\Model\Questions::SUBMITTER_CUSTOMER
          : \Itoris\ProductQa\Model\Questions::SUBMITTER_VISITOR;
      $productId = (int)$this->getRequest()->getParam('product_id');
      $mode = (int)$this->getRequest()->getParam('mode');
      $storeId = $this->getDataHelper()->getStoreManager()->getStore()->getId();
      $settings = $this->getDataHelper()->getSettings($storeId);
      $backendConfig = $this->getObjectManager()->get('Magento\Backend\App\ConfigInterface');
        $storeIds = [];
        foreach($this->getObjectManager()->create('Magento\Store\Model\System\Store')->getStoreCollection() as $store) $storeIds[]=$store->getId();
      $data = array(
          'status'           => $this->getRequest()->getParam('status'),
          'submitter_type'   => $submitter_type,
          'product_id'       => $productId,
          'nickname'         => htmlspecialchars($this->getRequest()->getParam('nickname_question')),
          'content'          => htmlspecialchars($this->getRequest()->getParam('question')),
          'customer_id'      => $customerId,
          'notify'           => ($this->getRequest()->getParam('notify')) ? 1 : 0,
          'notify_email'     => $this->getRequest()->getParam('notify_email', null),
          'store_id'         => $backendConfig->getValue('itoris_productqa/general/question_all_storeviews', 0) ? $storeIds : $storeId,
          'newsletter'       => ($this->getRequest()->getParam('newsletter')) ? 1 : 0,
          'newsletter_email' => $this->getRequest()->getParam('newsletter_email', null),
      );
      $this->getDataHelper()->getRegistry()->register('storeId', $storeId);
      try {
          $questionsModel = $this->getObjectManager()->get('Itoris\ProductQa\Model\Questions');
          if(!$questionsModel){
              $questionsModel = $this->getObjectManager()->create('Itoris\ProductQa\Model\Questions');
          }
          $output['subscribe'] = $questionsModel->addQuestion($data);
      } catch (\Exception $e) {
          /** @var $logger \Cm\RedisSession\Handler\LoggerInterface */
          $logger = $this->logger()->logException($e);
      }
      if ($settings->getTemplateAdminNotification()) {
          if(!$this->getDataHelper()->getRegistry()->registry('settings')){
              $this->getDataHelper()->getRegistry()->register('settings', $settings);
          }

          $product = $this->getObjectManager()->create('Magento\Catalog\Model\Product')->load($data['product_id']);
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
              'q_id'        => $this->getDataHelper()->getRegistry()->registry('q_id'),
              'q_url'        => $this->getObjectManager()->create('Magento\Backend\Helper\Data')->getUrl('itorisproductQa/questions/edit', array('id' => $this->getDataHelper()->getRegistry()->registry('q_id'))),
              'qa_details'   => $data['content'],
              'product_page' => $url,
              'type'         => \Itoris\ProductQa\Model\Notify::TYPE_QUESTION,
          );
          try {
              $this->getObjectManager()->create('Itoris\ProductQa\Model\Notify')->sendNotification($notification, \Itoris\ProductQa\Model\Notify::ADMIN);
          } catch(\Exception $e) {
              $this->logger()->logException($e);
          }
      }

      if ($this->getRequest()->getParam('status') == \Itoris\ProductQa\Model\Questions::STATUS_APPROVED) {

          $this->getDataHelper()->getRegistry()->register('page', 1);
          $this->getDataHelper()->getRegistry()->register('perPage', (int)$this->getRequest()->getParam('per_page'));
          $output['html'] = $this->getQuestions($productId, $mode);
          $output['messagessnomoder'] = $this->getDataHelper()->getEscaper()->escapeHtml( __('Thank you for submitting your question!'));

      } else {
          $output['messagess'] = __('Thank you for submitting your question! It will appear after moderation.');
      }
      $this->getResponse()->setBody(\Zend_Json::encode($output));

  }
    public function getQuestions($productId, $mode = \Itoris\ProductQa\Model\Questions::SORT_RECENT) {
        try {
            $this->_view->loadLayout('itorisproductQa_question_mode');
            /** @var \Itoris\ProductQa\Block\ProductQaAjax $ajax */
            $ajax =$this->_objectManager->get('Itoris\ProductQa\Block\ProductQaAjax');
            if(!$ajax){
                $ajax =$this->_objectManager->create('Itoris\ProductQa\Block\ProductQaAjax');
            }
            return $ajax->getHtmlForQuestions($productId, $mode);
        } catch (\Exception $e) {
            $this->logger()->logException($e);
        }
        return null;
    }
}