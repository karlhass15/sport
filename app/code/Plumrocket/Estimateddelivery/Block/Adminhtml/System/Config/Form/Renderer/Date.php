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

namespace Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\Renderer;

class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_form;
    protected $_helper;
    // protected $_elementDate;
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Data\Form\Element\SelectFactory
     */
    protected $selectFactory;

    /**
     * @var \Magento\Framework\Data\Form\Element\DateFactory
     */
    protected $dateFactory;

    /**
     * Date constructor.
     *
     * @param \Magento\Framework\Data\Form                       $form
     * @param \Plumrocket\Estimateddelivery\Helper\Data          $helper
     * @param \Magento\Framework\Locale\Resolver                 $localeResolver
     * @param \Magento\Backend\Block\Context                     $context
     * @param \Magento\Framework\Data\Form\Element\SelectFactory $selectFactory
     * @param \Magento\Framework\Data\Form\Element\DateFactory   $dateFactory
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\Data\Form $form,
        \Plumrocket\Estimateddelivery\Helper\Data $helper,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\Form\Element\SelectFactory $selectFactory,
        \Magento\Framework\Data\Form\Element\DateFactory $dateFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_form = $form;
        $this->_helper = $helper;
        $this->_localeResolver = $localeResolver;
        $this->selectFactory = $selectFactory;
        $this->dateFactory = $dateFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $params = [
            'name'      => $this->getColumn()->getName(),
            'html_id'   => $this->getColumn()->getId(),
            'value'     => $row->getData($this->getColumn()->getIndex()),
        ];

        $dateParams = array_merge($params, [
            'date_format'    => $this->_helper->getDateTimeFormat(),
            'time'      => false,
        ]);

        switch ($this->getColumn()->getData('id')) {
            case 'recurring_date':
                $months = \Zend_Locale_Data::getList($this->_localeResolver->getLocale(), 'months');

                $monthsSelect = $this->selectFactory->create(['data' => array_merge($params, [
                    'name'      => $params['name'] .'[month]',
                    'html_id'   => $params['html_id'] .'[month]',
                    'class'     => 'dateperiod-month',
                    'values'    => $months['format']['wide'],
                    'value'     => !empty($params['value']['month'])? $params['value']['month'] : 1,
                ])]);

                $html .= $monthsSelect
                    ->setForm($this->_form)
                    ->getElementHtml();

                $html .= ' <span class="' . $params['html_id'] . '-text">the</span> ';

                $daysSelect = $this->selectFactory->create(['data' => array_merge($params, [
                    'name'      => $params['name'] .'[day]',
                    'html_id'   => $params['html_id'] .'[day]',
                    'class'     => 'dateperiod-day',
                    'values'    => $this->_getDayOptions(),
                    'value'     => !empty($params['value']['day'])? $params['value']['day'] : 1,
                ])]);

                $html .= $daysSelect
                    ->setForm($this->_form)
                    ->getElementHtml();

                break;

            case 'period':
                $date = $this->dateFactory->create(['data' => array_merge($dateParams, [
                    'name'      => $dateParams['name'] .'[start]',
                    'html_id'   => $dateParams['html_id'] .'[start]',
                    'value'     => !empty($dateParams['value']['start'])? $dateParams['value']['start'] : '',
                ])]);
                $html .= $date
                    ->setForm($this->_form)
                    ->getElementHtml();

                $html .= ' - ';

                $dateParams = array_merge($dateParams, [
                    'name'      => $dateParams['name'] .'[end]',
                    'html_id'   => $dateParams['html_id'] .'[end]',
                    'value'     => !empty($dateParams['value']['end'])? $dateParams['value']['end'] : '',
                ]);

                // no break

            case 'single_day':
            default:
                $date = $this->dateFactory->create(['data' => $dateParams]);

                $html .= $date
                    ->setForm($this->_form)
                    ->getElementHtml();

                break;
        }

        return $html;
    }

    protected function _getDayOptions()
    {
        $dayOptions = [];
        foreach (range(1, 31) as $num) {
            switch ($num) {
                case 1:
                case 21:
                case 31:
                    $sfx = 'st';
                    break;
                case 2:
                case 22:
                    $sfx = 'nd';
                    break;
                case 3:
                case 23:
                    $sfx = 'rd';
                    break;
                default:
                    $sfx = 'th';
            }

            $dayOptions[$num] = $num . $sfx;
        }

        /*$sfx = ' (or last day)';
        $dayOptions[29] .= $sfx;
        $dayOptions[30] .= $sfx;
        $dayOptions[31] .= $sfx;*/

        return $dayOptions;
    }
}
