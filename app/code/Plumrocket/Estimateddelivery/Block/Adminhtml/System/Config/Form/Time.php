<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Estimateddelivery
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form;

class Time extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Catalog\Model\Webapi\Product\Option\Type\Date
     */
    protected $typeDate;

    /**
     * Time constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Catalog\Model\Webapi\Product\Option\Type\Date $typeDate
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Webapi\Product\Option\Type\Date $typeDate,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->typeDate = $typeDate;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->addClass('select');

        $value_hrs = 0;
        $value_min = 0;

        if ($value = $element->getValue()) {
            $values = explode(',', $value);
            if (is_array($values) && count($values) == 2) {
                $value_hrs = $values[0];
                $value_min = $values[1];
            }
        }

        $is24h =  $this->typeDate->is24hTimeFormat();

        $hourLabels = [
            '12 a.m.', '01 a.m.', '02 a.m.', '03 a.m.', '04 a.m.', '05 a.m.', '06 a.m.', '07 a.m.', '08 a.m.', '09 a.m.', '10 a.m.', '11 a.m.',
            '12 p.m.', '01 p.m.', '02 p.m.', '03 p.m.', '04 p.m.', '05 p.m.', '06 p.m.', '07 p.m.', '08 p.m.', '09 p.m.', '10 p.m.', '11 p.m.'
        ];

        $html = '<input type="hidden" id="'. $element->getHtmlId() .'" />';
        $html .= '<select name="'. $element->getName() .'" '.$element->serialize($element->getHtmlAttributes()).' style="width:125px">'."\n";

        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'. $hour .'" '. ( ($value_hrs == $i) ? 'selected="selected"' : '' ) .'>'. ($is24h? $hour : $hourLabels[$i]) .'</option>';
        }
        $html .= '</select>'."\n";

        $html .= '&nbsp;:&nbsp;&nbsp;<select name="'. $element->getName() .'" '.$element->serialize($element->getHtmlAttributes()).' style="width:125px">'."\n";
        for ($i = 0; $i < 60; $i += 5) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="'. $hour .'" '. ( ($value_min == $i) ? 'selected="selected"' : '' ) .'>'. $hour .'</option>';
        }
        $html .= '</select>'."\n";

        $html .= $element->getAfterElementHtml();
        return $html;
    }
}
