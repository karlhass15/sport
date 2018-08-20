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

namespace Plumrocket\Estimateddelivery\Model\Attribute\Source;

use Plumrocket\Estimateddelivery\Model\ProductCategory;

class Enable extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                [
                    'label' => __('Inherited'),
                    'value' => ProductCategory::INHERITED
                ], [
                    'label' => __('Disabled (do not show)'),
                    'value' => ProductCategory::DISABLED
                ], [
                    'label' => __('Dynamic Date ("n" days from today\'s date)'),
                    'value' => ProductCategory::DYNAMIC_DATE
                ], [
                    'label' => __('Dynamic Date Range ("n - m" days from today\'s date)'),
                    'value' => ProductCategory::DYNAMIC_RANGE
                ], [
                    'label' => __('Static Date'),
                    'value' => ProductCategory::STATIC_DATE
                ], [
                    'label' => __('Static Date Range'),
                    'value' => ProductCategory::STATIC_RANGE
                ], [
                    'label' => __('Static Text'),
                    'value' => ProductCategory::TEXT
                ],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
