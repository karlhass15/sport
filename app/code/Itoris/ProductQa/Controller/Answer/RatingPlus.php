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


class RatingPlus extends \Magento\Framework\App\Action\Action
{
    protected $remoteAddr;
    protected $_helper;
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        parent::__construct($context);
        $this->remoteAddr = $this->_objectManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress')->getRemoteAddress();
    }
    public function execute()
    {
        $jsonFactory = $this->_objectManager->create('Magento\Framework\Controller\Result\JsonFactory');
        $result = $jsonFactory->create();
        $answerId = $this->getRequest()->getParam('id');
        try {

            if (!$this->getCustomerSession()->isLoggedIn() && !$this->canVisitorRate()) {
                $url = $this->getDataHelper()->getUrl('customer/account/login');
                return $result->setData(['success' => true,'url'=>$url]);
            }
            $remoteIp = $this->getCustomerSession()->isLoggedIn() ? null : $this->remoteAddr;
            $answer = $this->_objectManager->get('Itoris\ProductQa\Model\Answers');
            if (!$answer) {
                $answer = $this->_objectManager->create('Itoris\ProductQa\Model\Answers');
            }
            return $result->setData(['success' => true,'count'=>$answer->addRating($answerId, $this->getCustomerSession()->getCustomerId(), '1', $remoteIp)]);

        } catch (\Exception $e) {
            return $result->setData(['error' => true]);
        }
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
        return $this->getSettings()->getVisitorCanRate() == \Itoris\ProductQa\Block\ProductQa::VISITORS_RATE_ALL;
    }
}