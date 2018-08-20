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

namespace Itoris\ProductQa\Observer;
use Magento\Framework\Event\ObserverInterface;

class LoadLayout implements ObserverInterface{
    protected $_objectManager;
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $objectManager =$this->_objectManager= \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Itoris\ProductQa\Helper\Data');
        $handles = $layout->getUpdate()->getHandles();
        if($helper->isEnabled() && (in_array('itorisproductqa_question_add',$handles))) {
            $layout->getUpdate()->addHandle('itorisproductqa_question_mode');
        }

    }
}