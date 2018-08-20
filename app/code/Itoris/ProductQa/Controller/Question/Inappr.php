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


class Inappr extends \Magento\Framework\App\Action\Action
{
    protected $_helper;
    public function execute()
    {
        $questionId = $this->getRequest()->getParam('id');
        $jsonFactory = $this->_objectManager->create('Magento\Framework\Controller\Result\JsonFactory');
        $result = $jsonFactory->create();
        try {
            if (!$this->getCustomerSession()->isLoggedIn() && !$this->canVisitorRateInappr()) {
                $url = $this->getDataHelper()->getUrl('customer/account/login');
               return $this->getResponse()->setBody(\Zend_Json::encode(['success' => true,'url'=>$url]));
            }
            $questionsModel = $this->_objectManager->get('Itoris\ProductQa\Model\Questions');
            if(!$questionsModel){
                $questionsModel = $this->_objectManager->create('Itoris\ProductQa\Model\Questions');
            }
            $questionsModel->setInappr($questionId);
            return $result->setData(array('message' => __('Thank you for your report! Our moderator will review it shortly.')));
        } catch (\Exception $e) { }

    }

    protected function getCustomerSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }
    /** @return \Itoris\ProductQa\Helper\Data */
    public function getDataHelper(){
        if(!$this->_helper){
            $this->_helper=$this->_objectManager->create('Itoris\ProductQa\Helper\Data');
        }
        return $this->_helper;
    }
    protected function getSettings() {

        return $this->getDataHelper()->getSettings($this->getDataHelper()->getStoreManager()->getStore()->getId());
    }
    public function canVisitorRate() {
        return $this->getSettings()->getVisitorCanRate() ==\Itoris\ProductQa\Block\ProductQa::VISITORS_RATE_ALL;
    }
    public function canVisitorRateInappr() {
        return $this->canVisitorRate() || $this->getSettings()->getVisitorCanRate() == \Itoris\ProductQa\Block\ProductQa::VISITORS_RATE_INAPPR;
    }

}