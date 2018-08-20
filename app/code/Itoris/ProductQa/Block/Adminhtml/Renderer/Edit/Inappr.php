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
namespace Itoris\ProductQa\Block\Adminhtml\Renderer\Edit;
class Inappr extends   \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text {

	public function render(\Magento\Framework\DataObject $row )
	{

		if ($this->getRequest()->getFullActionName()=='itorisproductQa_questions_edit') {
			return ($row->getInappr())
				? '<input type="hidden" id="answer_inappr_value_' . $row->getId() . '" name="answer[' . $row->getId() . '][inappr]" value="' . $row->getInappr() . '"/>
									<div style="text-align: center;" id="answer_inappr_' . $row->getId() . '">
										<div title="Inappropriate" class="itorisqa_inappr" style="margin: 0 auto;"></div>
										<span class="innapr_mask_remove" style="color: green;text-decoration: underline; cursor: pointer;"
										  data-answer-id="'.$row->getId().'">' . __('unmark') . '</span>
									  </div>'
				: '';
		}else{
			return ($row->getInappr())
				?  '<span>'.__('unmark') .'</span>'
				: '';
		}
	}
}
