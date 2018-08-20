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
namespace Itoris\ProductQa\Block\Adminhtml\Answers;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {
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
	protected function _prepareCollection() {
		$collection = $this->getDataHelper()->getRegistry()->registry('answers');
		$this->setCollection($collection);
		$this->setDefaultSort('datetime');
       	$this->setDefaultDir('desc');
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$page = $this->getDataHelper()->getRegistry()->registry('answersPage');

		$this->addColumn('id',
			array(
				'header'=>__('ID'),
				'width' => '30px',
				'index' => 'main_table.id',
				'getter' => 'getId'
		));

		if ($page != \Itoris\ProductQa\Block\Adminhtml\Answers::PAGE_INAPPR) {
			$this->addColumn('inappr',
				array(
					'header'=> __('Inappr'),
					'width' => '26px',
					'index' => 'main_table.inappr',
					'type'  => 'options',
					'options' => array(null => __('Any'), 0 => __('No'), 1 => __('Yes')),
					'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Edit\Inappr',
			));
		}

		$this->addColumn('datetime',
			array(
				'header' => __('Created On'),
				'width' => '100px',
				'index' => 'main_table.created_datetime',
				'type' => 'datetime',
				'getter' => 'getCreatedDatetime',
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Datetime',
		));

		if($page != \Itoris\ProductQa\Block\Adminhtml\Answers::PAGE_PENDING) {
			$this->addColumn('status',
				array(
					'header'=> __('Status'),
					'width' => '70px',
					'index' => 'main_table.status',
					'type'  => 'options',
					'options' => array(
						\Itoris\ProductQa\Model\Answers::STATUS_PENDING => __('Pending'),
						\Itoris\ProductQa\Model\Answers::STATUS_APPROVED => __('Approved'),
						\Itoris\ProductQa\Model\Answers::STATUS_NOT_APPROVED => __('Rejected'),
					),
					'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Status',
				)
			);
		}

		$this->addColumn('nickname',
			array(
				'header'=> __('Nickname'),
				'width' => '100px',
				'index' => 'main_table.nickname',
				'getter' => 'getNickname'
		));

		$this->addColumn('question',
			array(
				'header'=> __('Question'),
				'width' => '150px',
				'getter' => 'getQuestion',
				'index' => 'q.content',
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\QuestionInappr',
		));

		$this->addColumn('answer',
			array(
				'header'=> __('Answer'),
				'width' => '100px',
				'index' => 'main_table.content',
				'getter' => 'getContent',
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Text',
		));

		$this->addColumn('type',
			array(
				'header'=> __('Type'),
				'width' => '70px',
				'type'  => 'options',
				'options' => array(
					\Itoris\ProductQa\Model\Questions::SUBMITTER_ADMIN => __('Administrator'),
					\Itoris\ProductQa\Model\Questions::SUBMITTER_CUSTOMER => __('Customer'),
					\Itoris\ProductQa\Model\Questions::SUBMITTER_VISITOR => __('Guest'),
				),
				'index' => 'main_table.submitter_type',
				'renderer' => 'Itoris\ProductQa\Block\Adminhtml\Renderer\Submitter'
			)
		);

		$this->addColumn('productName',
			array(
				'header'=> __('Product Name'),
				'width' => '100px',
				'index' => 'value'
			)
		);

		$this->addColumn('action',
			array(
				'header'    => __('Action'),
				'width'     => '50px',
				'type'      => 'action',
				'getter'     => 'getQuestionId',
				'actions'   => array(
					array(
						'caption' => __('Edit'),
						'url'     => array(
							'base'=>'itorisproductQa/questions/edit',
						),
						'field'   => 'id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
			)
		);

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField('main_table.id');

		$this->getMassactionBlock()->setFormFieldName('answers');

		$this->getMassactionBlock()->addItem('delete', array(
			 	'label'=> __('Delete'),
			 	'url'  => $this->getUrl('*/*/massDelete'),
				'confirm' => __('Are you sure?')
			)
		);

		$this->getMassactionBlock()->addItem('status', array(
			 'label'=> __('Change status'),
			 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			 'additional' => array(
					'visibility' => array(
						 'name' => 'status',
						 'type' => 'select',
						 'class' => 'required-entry',
						 'label' => __('Status'),
						 'values' => array(
							\Itoris\ProductQa\Model\Answers::STATUS_PENDING => __('Pending'),
							 \Itoris\ProductQa\Model\Answers::STATUS_APPROVED => __('Approved'),
							 \Itoris\ProductQa\Model\Answers::STATUS_NOT_APPROVED => __('Rejected')
						)
					)
			)
		));

		return $this;
	}

	public function getRowUrl($answer) {
		return $this->getUrl('itorisproductQa/questions/edit', array('id' => $answer->getQuestionId()));
	}
}
