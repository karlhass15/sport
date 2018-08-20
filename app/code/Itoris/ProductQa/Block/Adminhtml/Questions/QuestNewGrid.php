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


class QuestNewGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    public function __construct( \Magento\Backend\Block\Template\Context $context,
                                 \Magento\Backend\Helper\Data $backendHelper,
                                 array $data = [])
    {
        parent::__construct($context,$backendHelper,$data);
        $this->setId('itorisQaGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);

    }
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
    public function getCoreRegistry(){
        return $this->getObjectManager()->get('Magento\Framework\Registry');
    }
    protected function _prepareCollection()
    {
        $collection = $this->getObjectManager()->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSort('name', 'ASC');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns(){
        $this->addColumn('entity_id', array(
            'header'    => __('ID'),
            'index'     => 'entity_id',
            'type'  => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => __('Name'),
            'index'     => 'name',
            'align'     => 'center',
            'width'     => '200'
        ));

        $this->addColumn('sku', array(
            'header'    => __('Sku'),
            'width'     => '150',
            'index'     => 'sku',
            'align'     => 'center',
        ));
        $this->addColumn('sku', array(
            'header'    => __('Sku'),
            'width'     => '150',
            'index'     => 'sku',
            'align'     => 'center',
        ));
        $this->addColumn('action',
            array(
                'header'  => __('Action'),
                'width'   => '50px',
                'class'=>'itoris_product_add',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Add'),
                        'url'     => array(
                            'base'=>'#',
                        ),
                        'field' => 'id'
                    )
                ),
                'filter'   => false,
                'sortable' => false,
                'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\AddProduct'
            )
        );
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridProductQA', array('_current'=> true));
    }
    protected function _prepareMassaction(){
        $this->getMassactionBlock()->addItem(
            'enable',
            [
                'label' => $this->escapeHtml(__('Enable')),
                'url' => $this->getUrl('*/*/massEnable'),
                'confirm' => $this->escapeHtml(__('Are you sure want to enable the tab(s)?'))
            ]
        );
        return $this;
    }

    public function getRowUrl($row) {
        return null;
    }
}