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
namespace Itoris\ProductQa\Block\Adminhtml\Questions;
class ProductGridQuestion extends  \Magento\Backend\Block\Widget\Grid\Extended {
    protected $_helper;
    protected $_objectManager;
    protected $_template = 'Itoris_ProductQa::product/grid.phtml';
    protected function _construct(){
        parent::_construct();
        $this->setId('itoris_productqa');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultLimit(20);
        $this->objectManager=\Magento\Framework\App\ObjectManager::getInstance();

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
    protected function _prepareCollection() {
        $collection = $this->getObjectManager()->create('Itoris\ProductQa\Model\ResourceModel\Questions\Collection');
        $collection->getSelect()->where('main_table.product_id='.$this->getDataHelper()->getRequest()->getParam('id'));
        $this->setCollection($collection);
        $this->setDefaultSort('datetime');
        $this->setDefaultDir('desc');
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id',
            array(
                'header' =>__('ID'),
                'width'  => '30px',
                'index'  => 'main_table.id',
                'getter' => 'getId',
                'filter'=>false,
                'sortable'  => false
            )
        );

        $this->addColumn('status',
            array(
                'header'  => __('Status'),
                'width'   => '70px',
                'index'   => 'main_table.status',
                'type'    => 'options',

                'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Status',
                'filter'=>false,
                'sortable'  => false
            )
        );


        $this->addColumn('nickname',
            array(
                'header' => __('Nickname'),
                'width'  => '100px',
                'index'  => 'main_table.nickname',
                'getter' => 'getNickname',
                'filter'=>false,
                'sortable'  => false
            )
        );

        $this->addColumn('question',
            array(
                'header'   => __('Question'),
                'width'    => '150px',
                'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Question',
                'index'    => 'main_table.content',
                'filter'=>false,
                'sortable'  => false
            )
        );

        $this->addColumn('action',
            array(
                'header'  => __('Action'),
                'width'   => '50px',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Edit'),
                        'url'     => array(
                            'base'=>'itorisproductQa/questions/edit/productBack/'.(int)$this->getRequest()->getParam('id'),
                        ),
                        'field' => 'id'

                    )
                ),
                'filter'   => false,
                'sortable' => false,

            )
        );


        return parent::_prepareColumns();
    }
    protected function _prepareFilterButtons()
    {

        $this->unsetChild('reset_filter_button');
        $this->unsetChild('search_button');
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Add a Question'),
                    'onclick' => 'window.location.href=\''.$this->getUrl('itorisproductQa/questions/newquestion',array('id' => (int)$this->getRequest()->getParam('id')))."'",
                    'class' => 'action-secondary',
                ]
            )->setDataAttribute(['action' => 'itoris-product-qa'])
        );
    }
    public function getGridUrl()
    {
        return $this->getUrl('itorisproductQa/questions/questionAjax', array('id' => (int)$this->getRequest()->getParam('id'))); // TODO: Change the autogenerated stub
    }

    public function getRowUrl($question) {
        return $this->getUrl('itorisproductQa/questions/edit', array('id' => $question->getId(),'productBack'=>$this->getRequest()->getParam('id')));
    }
}