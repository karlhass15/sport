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
namespace Itoris\ProductQa\Block\Adminhtml\Questions\FormEdit;
class Form extends \Magento\Backend\Block\Widget\Form\Generic {

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
		$question = $this->getDataHelper()->getRegistry()->registry('question');
		$form = $this->_formFactory->create(
			['data'=>[
				'id'      => 'edit_form',
				'action'  => $this->getUrl('*/*/save'),
				'method'  => 'post',
				'enctype' => 'multipart/form-data'
				]
			]
		);


		$questionFieldset = $form->addFieldset('question_fieldset', array('legend'=>__('Question Details')));

		$questionFieldset->addField('id', 'hidden', array(
                'name'  => 'id',
				'value' => $question['id'],
            )
        );

		$questionFieldset->addField('product', 'label', array(
                'name'  => 'product',
                'label' => __('Product'),
                'title' => __('Product'),
				'value' => array('name' => $question['product_name'],
								 'url' => $this->getDataHelper()->getBackendHelperData()->getUrl('catalog/product/edit/id/' . $question['product_id'])
							),
            )
        )->setRenderer($this->getObjectManager()->create('Itoris\ProductQa\Block\Adminhtml\Renderer\Element\Link'));

		$questionFieldset->addField('posted_by', 'label', array(
                'name'  => 'posted_by',
                'label' => __('Posted By'),
                'title' => __('Posted By'),
				'value' => array(
					'user_name'       => (isset($question['user_name'])) ? $question['user_name'] : '',
					'user_url'        => $this->getDataHelper()->getBackendHelperData()->getUrl('customer/index/edit/id/'. $question['customer_id'] .'/'),
					'user_email'      => (isset($question['user_email'])) ? $question['user_email'] : '',
					'user_type'       => $question['user_type'],
					'posted_on_label' => __('Posted On'),
					'posted_on_date'  => $question['created_datetime'],
				),

            )
        )->setRenderer($this->getObjectManager()->create('Itoris\ProductQa\Block\Adminhtml\Renderer\Element\PostedBy'));

		$questionFieldset->addField('rating', 'text', array(
                'name'  => 'rating',
                'label' => __('Rating'),
                'title' => __('Rating'),
				'value' => array(
					'good'         => $question['good'],
					'bad'          => $question['bad'],
					'inappr'       => $question['inappr'],
					'good_label'   => __('helpful'),
					'bad_label'    => __('not helpful'),
					'inappr_label' => __('Rated as Inappropriate!'),
					'remove_flag'  => __('remove flag'),
				),
            )
        )->setRenderer($this->getObjectManager()->create('Itoris\ProductQa\Block\Adminhtml\Renderer\Element\Rating'));

		$questionFieldset->addField('status', 'select', array(
                'name'     => 'status',
                'label'    => __('Status'),
                'title'    => __('Status'),
				'value'    => $question['status'],
                'required' => true,
				'values'   => array(
					\Itoris\ProductQa\Model\Questions::STATUS_PENDING      => __('Pending'),
					\Itoris\ProductQa\Model\Questions::STATUS_APPROVED     => __('Approved'),
					\Itoris\ProductQa\Model\Questions::STATUS_NOT_APPROVED => __('Rejected')
				),
            ),
			'posted_by'
        );

        $questionFieldset->addField('visible', 'multiselect', array(
                'name'     => 'visible',
                'label'    => __('Question is visible in'),
                'title'    => __('Question is visible in'),
                'required' => true,
				'size'=>'5',
				'value'    => explode(',', $question['visible']),
				'values'   => $this->getObjectManager()->create('Itoris\ProductQa\Helper\Form')->getStoreSelectOptions(),
				'style'=>'width:240px; height:auto'
            )
        )->setAfterElementHtml("
         <script>
         	document.getElementById('visible').setAttribute('size', '5');
         </script>
         ");
		if($this->getRequest()->getParam('productBack')){
			$questionFieldset->addField('productBack', 'hidden', array(
				'name'     => 'productBack',
				'value'    => (int)$this->getRequest()->getParam('productBack'),
			));
		}
		$questionFieldset->addField('nickname', 'text', array(
				'name'     => 'nickname',
				'label'    => __('Nickname'),
				'title'    => __('Nickname'),
				'required' => true,
				'value'    => htmlspecialchars_decode($question['nickname']),
		));

		$questionFieldset->addField('question', 'textarea', array(
				'name'     => 'question',
				'label'    => __('Your Question'),
				'title'    => __('Your Question'),
				'required' => true,
				'value'    => htmlspecialchars_decode($question['content']),
				'style'    => 'height:auto;'
 		))->setRows(4);
		$block = $this->getLayout()->createBlock('Itoris\ProductQa\Block\Adminhtml\Questions\Edit\Grid','itoris_grid_answer');
		$answersFieldset = $form->addFieldset(
			'answers_fieldset',
			array('legend'=>__('Answers'))
		);
		$answersFieldset->getRenderer()->setTemplate('Itoris_ProductQa::renderer/fieldset.phtml');
        $form->setUseContainer(true);
		$form->getElements();
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
