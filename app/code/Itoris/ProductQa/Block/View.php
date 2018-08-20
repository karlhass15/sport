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

namespace Itoris\ProductQa\Block;


class View extends \Magento\Framework\View\Element\Template
{
    protected $_template='Itoris_ProductQa::productqa_tab.phtml';

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
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);
        if($this->getDataHelper()->isEnabled())
        $this->setTabTitle();
    }
    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $title =__('Product Q/A %1', '<span class="counter itoris_counter_tab_count" onclick="return false">' . $this->getCollectionSize() . '</span>');
        $this->setTitle($title);

    }
    /**
     * Get size of reviews collection
     *
     * @return int
     */
    public function getCollectionSize()
    {
       return $this->getDataHelper()->countQuestion();

    }

}