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

class ActivityModule implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;
    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     */
    public function __construct(
        \Itoris\ProductQa\Helper\Data $helper
    )
    {
        $this->_helper = $helper;

    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getLayout();
        $linkBlock = $layout->getBlock('product.info.review');
        if($this->_helper->isEnabled() && $linkBlock){
            $linkBlock->setTemplate('Itoris_ProductQa::review.phtml');
        }else if($layout->getBlock('productqa.tab') && $layout->getBlock('productqa.tab')->getType()=='Itoris\ProductQa\Block\View' && !$this->_helper->isEnabled()){
            $layout->unsetElement('productqa.tab');
        }
    }
}