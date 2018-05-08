<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FrequencyOptions implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Daily')
            ],
            [
                'value' => 2,
                'label' => __('Weekly')
            ],
            [
                'value' => 3,
                'label' => __('Monthly')
            ],
            [
                'value' => 4,
                'label' => __('Yearly')
            ]
        ];
    }
}
