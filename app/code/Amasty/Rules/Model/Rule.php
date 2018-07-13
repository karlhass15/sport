<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

class Rule extends \Magento\Framework\Model\AbstractModel
{
    const RULE_NAME = 'amrules_rule';

    /**
     * @var \Amasty\Rules\Model\CheckEnterprise
     */
    private $checkEnterprise;

    /**
     * @var \Amasty\Rules\Model\ResourceModel\Rule
     */
    public $resource;

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->checkEnterprise = $this->getData('isEnterprise');
        $this->resource = $this->getData('resource');
        parent::_construct();
        $this->_init('Amasty\Rules\Model\ResourceModel\Rule');
        $this->setIdFieldName('entity_id');
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     *
     * @return mixed
     */
    public function loadBySalesrule(\Magento\Rule\Model\AbstractModel $rule)
    {
        if ($amrulesRule = $rule->getData(self::RULE_NAME)) {
            return $amrulesRule;
        }

        $ruleId = $this->checkEnterprise->getRuleId($rule);
        $this->resource->load($this, $ruleId, 'salesrule_id');
        $rule->setData(self::RULE_NAME, $this);

        return $this;
    }

    /**
     * @return CheckEnterprise
     */
    public function getCheckEnterprise()
    {
        return $this->checkEnterprise;
    }
}
