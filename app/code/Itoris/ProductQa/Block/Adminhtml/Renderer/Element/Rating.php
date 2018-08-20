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
namespace Itoris\ProductQa\Block\Adminhtml\Renderer\Element;
class Rating extends \Magento\Backend\Block\Widget\Form\Renderer\Element {

	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
		$value = $element->getValue();
		$html = '<tr><td class="label itoris-formqa-label"><label for="'. $element->getId() .'">'. $element->getLabelHtml() .'</label></td><td class="value">
				<span style="color:blue;">'. $value['good'] .' ' . $value['good_label'] .'</span>,
				<span style="color:orange; margin-right: 20px;">'. $value['bad'] .' '. $value['bad_label'] .'</span>';
		$html .= ($value['inappr'])
				?  '<span id="inappr_text"><span style="color: red;">' . $value['inappr_label']
			  		. ' </span><span style="color: green;text-decoration: underline; cursor: pointer;" class="unmark_question_qa"> ' . $value['remove_flag'] .'</span></span>'
				: '';
		$html .= '<input type="hidden" id="question_inappr" name="inappr" value="'. $value['inappr'] .'"/></td></tr>';
		return $html;
	}
}
