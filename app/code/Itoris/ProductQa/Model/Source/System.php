<?php
/**
 * Created by PhpStorm.
 * User: Workstation1
 * Date: 20.01.2017
 * Time: 10:39
 */

namespace Itoris\ProductQa\Model\Source;
use Magento\Framework\Data\Form\Element\AbstractElement;

class System extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_helper;
    protected $_objectManager;
    public function getObjectManager(){
        if($this->_objectManager)
            return $this->_objectManager;
        return $this->_objectManager= \Magento\Framework\App\ObjectManager::getInstance();
    }
    /** @return \Itoris\ProductQa\Helper\Data */
    public function getDataHelper(){
        if(!$this->_helper){
            $this->_helper=$this->getObjectManager()->create('Itoris\ProductQa\Helper\Data');
        }
        return $this->_helper;
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $configBool = $this->getDataHelper()->getScopeConfig()->getValue(\Itoris\ProductQa\Helper\Data::XML_PATH_CAPTCHA_ENABLED_STOREFRONT,\Itoris\ProductQa\Helper\Data::SCOPE_TYPE_STORES,$storeId);
        if(!$configBool)
        $element->setDisabled('disabled');
        return $element->getElementHtml();

    }

}