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
class NewQuestion extends \Magento\Backend\Block\Widget\Form\Container {

	public function _construct() {
		$this->_mode = 'questionnew';
        $this->_blockGroup = 'Itoris_ProductQa';
        $this->_controller = 'adminhtml_questions';
		$this->_headerText = __('Add New Question');
		parent::_construct();
        $this->updateButton('save', 'label', __('Save Question'));
		$this->buttonList->update('save','class','save');
		if($this->getRequest()->getParam('id')){
			$this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getUrl('catalog/product/edit/id/'.(int)$this->getRequest()->getParam('id')) . '\')');
		}
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
    }
	protected function _getSaveAndContinueUrl() {
		return $this->getUrl('*/*/add', array(
			'_current'  => true,
			'back'      => 'edit',
		));
	}
}
