<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ShoppingRuleOptions implements OptionSourceInterface
{


    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * ShoppingRuleOptions constructor.
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory
    ) {
    

        $this->ruleFactory = $ruleFactory->create();
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        // TODO: Implement toOptionArray() method.
        $collection = $this->ruleFactory->getCollection();
        $data = [];

        foreach ($collection as $item) {
            $data[] = ['value' => $item->getName(), 'label' => $item->getName()];
        }

        return $data;
    }
}
