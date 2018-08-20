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


class Mode  extends \Magento\Framework\App\Action\Action
{
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
    {$storeId = (int)$this->getRequest()->getParam('store_id');
        $this->getDataHelper()->getRegistry()->register('storeId', $storeId);
        $productId = (int)$this->getRequest()->getParam('product_id');
        $mode = (int)$this->getRequest()->getParam('mode');
        if(!$this->getDataHelper()->getRegistry()->registry('page')){
            $this->getDataHelper()->getRegistry()->register('page', (int)$this->getRequest()->getParam('page'));
        }
        if(!$this->getDataHelper()->getRegistry()->registry('per_page')) {
            $this->getDataHelper()->getRegistry()->register('perPage', (int)$this->getRequest()->getParam('per_page'));
        }
        if ($this->getDataHelper()->getRegistry()->registry('page') != 1 ) {
            if(!$this->getDataHelper()->getRegistry()->registry('pages')) {
                $this->getDataHelper()->getRegistry()->unregister('pages');
                $this->getDataHelper()->getRegistry()->register('pages', (int)$this->getRequest()->getParam('pages'));
            }
        }
        $this->_view->loadLayout('itorisproductQa_question_mode');
        $this->getResponse()->setBody($this->getQuestions($productId, $mode));

    }

    protected function getCustomerSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }

    public function getQuestions($productId, $mode = \Itoris\ProductQa\Model\Questions::SORT_RECENT) {
        try {
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