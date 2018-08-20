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

class DatePeriod extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<input type="hidden" name="'. $element->getName() .'" value="" />';

        $html .= $this->getLayout()->createBlock('Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\DatePeriod\InputTable')
            ->setContainerFieldId($element->getName())
            ->setRowKey('name')
            ->addColumn('date_type', [
                'header'    => __('Period / Date Type'),
                'index'     => 'date_type',
                'type'      => 'select',
                'options'   => [
                    'recurring_date'    => 'Recurring Date',
                    'single_day'        => 'Single Day',
                    'period'            => 'Period (from-to)',
                ],
                'value'     => 2,
                'column_css_class' => 'dateperiod-type',
            ])
            ->addColumn('recurring_date', [
                'header'    => __('Period / Date'),
                'index'     => 'recurring_date',
                'type'      => 'date',
                'renderer'  => 'Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\Renderer\Date',
                'column_css_class' => 'dateperiod-recurring-date',
            ])
            ->addColumn('single_day', [
                'index'     => 'single_day',
                'type'      => 'date',
                'renderer'  => 'Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\Renderer\Date',
                'width'        => '0',
                'header_css_class' => 'dateperiod-hide',
                'column_css_class' => 'dateperiod-single-day',
            ])
            ->addColumn('period', [
                'index'     => 'period',
                'type'      => 'date',
                'renderer'  => 'Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\Renderer\Date',
                'width'        => '0',
                'header_css_class' => 'dateperiod-hide',
                'column_css_class' => 'dateperiod-period',
            ])
            ->addColumn('remove', [
                'header'    => __('Action'),
                'index'     => 'remove',
                'type'      => 'text',
                'renderer'  => 'Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\Renderer\Button',
                'value'     => 1,
                'column_css_class' => 'remove',
            ])
            ->setArray($this->_getValue($element->getValue()))
            ->toHtml();

        $html .= $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->addData([
                'label'   => __('Add Row'),
                'type'    => 'button',
                'class'   => 'add dateperiod-add',
            ])
            ->toHtml();

        return $html;
    }

    protected function _getValue($data = [])
    {
        $rows = [
            '_TMPNAME_' => [],
        ];

        if ($data && is_array($data)) {
            /*if(isset($data['value'])) {
                if(is_array($data['value'])) {
                    $rows = array_merge($rows, $data['value']);
                }
            }else{
                $rows = array_merge($rows, $data);
            }*/
            $rows = array_merge($rows, $data);
        }

        foreach ($rows as $name => &$row) {
            $row = array_merge($row, [
                'name'      => $name,
            ]);
        }

        return $rows;
    }
}
