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
namespace Itoris\ProductQa\Block\Adminhtml\Questions\Edit;
class Grid extends  \Magento\Backend\Block\Widget\Grid\Extended {
    protected $_helper;
	protected $_objectManager;
	public function _construct() {
		parent::_construct();
		$this->setData('id','itoris_grid_quest_answer');
		$this->setTemplate('Itoris_ProductQa::grid.phtml');
        $this->setRowClickCallback(false);
        $this->_emptyText = __('No records found.');
		$this->_defaultLimit = 1000;
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
			$collection = $this->getDataHelper()->getRegistry()->registry('answerCollection');
			$this->setCollection($collection);
			return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$this->addColumn('del',
			array(
				'header'   => __('Del'),
				'width'    => '10px',
				'type'     => 'checkbox',
				'sortable' => false,
				'filter'   => false,
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Edit\Delete',
			)
		);

		$this->addColumn('datetime',
			array(
				'header'   => __('Posted On'),
				'width'    => '100px',
				'index'    => 'main_table.created_datetime',
				'type'     => 'datetime',
				'getter'   => 'getCreatedDatetime',
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Datetime',
				'sortable' => false,
				'filter'   => false,
			)
		);

		$this->addColumn('posted_by',
			array(
				'header'   => __('Posted By'),
				'width'    => '50px',
				'type'     => 'text',
				'sortable' => false,
				'filter'   => false,
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Edit\PostedBy',
			)
		);

		$this->addColumn('inappr',
			array(
				'header'   => __('Inappr'),
				'width'    => '30px',
				'type'     => 'options',
				'sortable' => false,
				'filter'   => false,
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Edit\Inappr',
			)
		);

		$this->addColumn('status',
			array(
				'header'   => __('Status'),
				'width'    => '70px',
				'type'     => 'options',
				'sortable' => false,
				'filter'   => false,
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Edit\Status',
			)
		);

		$this->addColumn('nickname',
			array(
				'header'   => __('Nickname'),
				'width'    => '100px',
				'getter'   => 'getNickname',
				'sortable' => false,
				'filter'   => false,
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Edit\Input',
			)
		);

		$this->addColumn('answer',
			array(
				'header'   => __('Answer') . ' (' . __('HTML Tags allowed') . ')',
				'width'    => '450px',
				'getter'   => 'getContent',
				'sortable' => false,
				'filter'   => false,
				'clickable'=>false,
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Edit\Textarea',
			)
		);
		return parent::_prepareColumns();
	}

	public function getRowUrl($item) {
		return false;
	}
}
