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


class Unsubscribe extends \Magento\Framework\App\Action\Action
{
    protected $_helper;
    public function execute()
    {
        $result = array('ok' => false);
        $questionId = (int)$this->getRequest()->getParam('question_id');
        if ($questionId) {
            $question = $this->_objectManager->get('Itoris\ProductQa\Model\Questions')->load($questionId);
            if ($questionId) {
                $customerId = null;
                $email = null;
                if ($this->getCustomerSession()->isLoggedIn()) {
                    $customerId = $this->getCustomerSession()->getCustomer()->getId();
                } else {
                    $email = $this->getRequest()->getParam('email');
                    if (!$email) {
                        $result['error'] = __('Email address should not be left blank');
                    }
                }
                try {
                    if ($question->getNotify() && (($customerId && $question->getCustomerId() == $customerId) || ($email && $question->getEmail() == $email))) {
                        $question->setNotify(false)
                            ->save();
                        $result['ok'] = true;
                        $result['message'] = __('You have been unsubscribed');
                    } else if (!isset($result['error'])) {
                        $subscriber =$this->_objectManager->create('Itoris\ProductQa\Model\Question\Subscriber');
                        $collection = $subscriber->getCollection()
                            ->addFieldToFilter('question_id', array('eq' => $questionId));
                        if ($customerId) {
                            $collection->addFieldToFilter('customer_id', array('eq' => $customerId));
                        }
                        if ($email) {
                            $collection->addFieldToFilter('email', array('eq' => $email));
                        }
                        if (count($collection)) {
                            foreach ($collection as $item) {
                                $item->delete();
                            }
                            $result['ok'] = true;
                            $result['message'] = __('You have been unsubscribed');
                        }
                    }
                    $result['is_customer'] = (bool)$customerId;
                } catch (\Exception $e) {
                    $result['error'] = __('There was a problem with the subscription.');
                }
            }
        }
        $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
    protected function getCustomerSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }
    public function getDataHelper(){
        if(!$this->_helper){
            $this->_helper=$this->_objectManager->create('Itoris\ProductQa\Helper\Data');
        }
        return $this->_helper;
    }
    protected function getSettings() {

        return $this->getDataHelper()->getSettings($this->getDataHelper()->getStoreManager()->getStore()->getId());
    }
}