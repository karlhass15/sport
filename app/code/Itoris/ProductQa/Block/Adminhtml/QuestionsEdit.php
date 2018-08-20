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
namespace Itoris\ProductQa\Block\Adminhtml;
class QuestionsEdit extends  \Magento\Backend\Block\Widget\Form\Container {

    public function _construct() {
        parent::_construct();
        $this->_blockGroup = 'Itoris_ProductQa';
        $this->_controller = 'adminhtml_questions';
        $this->_objectId = 'page_id';
        $this->_mode = 'FormEdit';
        $this->_headerText =__('Edit Question');
        $this->updateButton('save', 'label', __('Save Question'));

        $this->updateButton('delete', 'label', __('Delete Question'));
        $this->buttonList->update('save','class','save');
        $this->buttonList->update('save', 'url', 'itorisproductQa/questions/save'.$this->getRequest()->getParam('store'));
        $this->updateButton('delete', 'onclick' ,'deleteConfirm(\'' .__('Do you really want to remove the question? All answers will be removed as well') . '\', \'' . $this->getDeleteUrl() . '\')');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => $this->escapeHtml(__('Save and Continue Edit')),
                'class' => 'primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            null
        );
        if($this->getRequest()->getParam('productBack')){
            $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getUrl('catalog/product/edit/id/'.(int)$this->getRequest()->getParam('productBack')) . '\')');
        }

    }
    public function getDeleteUrl() {
        if($this->getRequest()->getParam('productBack')){
            return $result= $this->getUrl('*/*/massDelete/id/'.(int)$this->getRequest()->getParam('id').'/productBack/'.(int)$this->getRequest()->getParam('productBack'));
        }else{
            return $this->getUrl('*/*/massDelete/id/'.(int)$this->getRequest()->getParam('id'));
        }

    }

    protected function _getSaveAndContinueUrl() {
        return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit',
        ));
    }
}