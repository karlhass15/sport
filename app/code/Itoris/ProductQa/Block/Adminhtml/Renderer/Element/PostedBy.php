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
class PostedBy extends \Magento\Backend\Block\Widget\Form\Renderer\Element {

	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
		$value = $element->getValue();
		//Magento\Framework\View\Element\AbstractBlock
		$date = $this->formatDate($value['posted_on_date'],\IntlDateFormatter::MEDIUM , true);
		$html = '<tr><td class="label itoris-formqa-label"><label for="'. $element->getId() .'">'. $element->getLabelHtml() .'</label></td><td class="value"> ';
		if ($value['user_name']) {
    		$html .= '<a href="'. $value['user_url'] .'">' . $value['user_name'] . ' </a>
    				<a href="mailto:' . $value['user_email'] .'"> ('. $value['user_email'] .') </a>';
		}
		$html .= $value['user_type'] . ' <span >'. $value['posted_on_label'] .'</span> ' . $date . '</td></tr>';
		return $html;
	}
}
