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
namespace Itoris\ProductQa\Block\Adminhtml\Questions\Questionnew ;
class Form extends  \Magento\Backend\Block\Widget\Form\Generic {

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
	protected function _prepareForm() {
		$form = $this->_formFactory->create(
			['data'=>[
				'id'      => 'edit_form',
				'action'  => $this->getUrl('*/*/add'),
				'method'  => 'post',
				'enctype' => 'multipart/form-data'
			]
			]
		);


        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>__('Question Details')));



		$fieldset->addField('status', 'select', array(
                'name'     => 'status',
                'label'    => __('Status'),
                'title'    => __('Status'),
                'required' => true,
				'values'   => array(
					\Itoris\ProductQa\Model\Questions::STATUS_PENDING => __('Pending'),
					\Itoris\ProductQa\Model\Questions::STATUS_APPROVED => __('Approved'),
					\Itoris\ProductQa\Model\Questions::STATUS_NOT_APPROVED => __('Rejected')
				),
				'style'    => 'width: 400px',
            )
        );

        $fieldset->addField('visible', 'multiselect', array(
                'name'     => 'visible',
                'label'    => __('Question is visible in'),
                'title'    => __('Question is visible in'),
                'required' => true,
				'values'   => $this->getObjectManager()->create('Itoris\ProductQa\Helper\Form')->getStoreSelectOptions(),
				'style'    => 'width: 400px',
            )
        );

		$fieldset->addField('nickname', 'text', array(
				'name'     => 'nickname',
				'label'    => __('Nickname'),
				'title'    => __('Nickname'),
				'required' => true,
				'style'    => 'width: 400px',
		));
		if($this->getRequest()->getParam('id')){
			$fieldset->addfield('productBack', 'hidden', array(
				'name'     => 'productBack',
				'value'    => (int)$this->getRequest()->getParam('id'),
			));
		}
		$fieldset->addField('question', 'text', array(
				'name'     => 'question',
				'label'    => __('Your Question'),
				'title'    => __('Your Question'),
				'required' => true,
		));
		$fieldset->addField('Add Product to Question', 'button', array(
			'value'    => __('Add Product to Question'),
			'label'    => __(''),
			'style'    => 'width: 400px;',
			'class'=>'action-secondary itoris_ajax_grid_product',
		));
		$fieldset->addField('product_empty_add', 'label', array(
			'name'     => 'product_empty_add',
			'value'    => __('Add Protuct to Question'),
		))->setRenderer($this->getObjectManager()->create('Itoris\ProductQa\Block\Adminhtml\Renderer\Element\AddEmpty'));
		$fieldset->addField('product_id', 'hidden', array(
			'name'     => 'product_id',
			'required' => true,
			'class'=>'product_is_required',
			'style'    => 'width: 400px',
		));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
