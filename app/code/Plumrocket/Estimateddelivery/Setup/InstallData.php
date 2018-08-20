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

namespace Plumrocket\Estimateddelivery\Setup;

use Magento\Framework\App\State;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * @var \Plumrocket\Estimateddelivery\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $_eavSetup;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $_attributeSet;

    /**
     * @var string
     */
    protected $_modelName;

    /**
     * @var string
     */
    protected $_type;

    /**
     * InstallData constructor.
     *
     * @param \Plumrocket\Estimateddelivery\Helper\DataFactory $helperFactory
     * @param \Magento\Eav\Setup\EavSetupFactory               $eavSetupFactory
     * @param \Magento\Eav\Model\Entity\Attribute\Set          $attributeSet
     * @param State                                            $state
     */
    public function __construct(
        \Plumrocket\Estimateddelivery\Helper\DataFactory $helperFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Entity\Attribute\Set $attributeSet,
        \Magento\Framework\App\State $state
    ) {
        try {
            $state->setAreaCode('adminhtml');
        } catch (\Exception $e) {}

        $this->_helper = $helperFactory->create();
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_attributeSet = $attributeSet;
    }

    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $this->_eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        $this->init(\Magento\Catalog\Model\Category::ENTITY, 'delivery')
            ->addAll()
            ->setType('shipping')
            ->addAll()
            ->orderGroup(40);

        $this->init(\Magento\Catalog\Model\Product::ENTITY, 'delivery')
            ->addAll()
            ->setType('shipping')
            ->addAll()
            ->orderGroup(500);
    }

    public function init($model, $type)
    {
        $this->_modelName = $model;
        $this->_type  = $type;
        return $this;
    }

    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function name($string, $firstToUpper = false, $past = false)
    {
        $pastArray = [
            'delivery' => 'delivered',
            'shipping' => 'shipped'
        ];

        $result = $this->_type;
        if ($past) {
            $result = $pastArray[$this->_type];
        }
        if ($firstToUpper) {
            $result = ucfirst($result);
        }
        return sprintf($string, $result);
    }

    public function getGroup()
    {
        return $this->_helper->getGroupName();
    }

    protected function _appendInfo($data, $sortOrder, $required = false)
    {
        if ($this->_type == 'shipping') {
            $sortOrder += 100;
        }

        return array_merge([
            'global'    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE, // \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            'visible'   => true,
            'required'  => $required,
            'group'     => $this->getGroup(),
            'sort_order'=> $sortOrder,

            'backend'   => '',
            'frontend'  => '',
            'class'     => '',
            'user_defined' => 0,
            'default'   => '',
            'searchable'=> false,
            'filterable'=> false,
            'comparable'=> false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'unique'    => false,
            'apply_to'  => '',
        ], $data);
    }

    protected function _addAttribute($name, $data, $sort_order, $required = false)
    {
        $this->_eavSetup->addAttribute(
            $this->_modelName,
            $this->name($name),
            $this->_appendInfo($data, $sort_order, $required)
        );
    }

    public function addAll()
    {
        $this->_addAttribute('estimated_%s_enable', [
            'type'      => 'int',
            'label'     => $this->name('%s Date(s)', true),
            'input'     => 'select',
            'source'    => 'Plumrocket\Estimateddelivery\Model\Attribute\Source\Enable',
            'default'   => 0,
        ], 10, 1);

        $this->_addAttribute('estimated_%s_days_from', [
            'type'      => 'int',
            'label'     => $this->name('Business Days For %s', true),
            'input'     => 'text',
            'note'      => $this->name("Number of business days (excluding weekends and holidays) from today's date required for the product to be %s.", false, true),
        ], 20);

        $this->_addAttribute('estimated_%s_days_to', [
            'type'      => 'int',
            'label'     => '',
            'input'     => 'text',
        ], 30);

        $this->_addAttribute('estimated_%s_date_from', [
            'type'      => 'datetime',
            'label'     => $this->name('Estimated %s Date', true),
            'input'     => 'date',
            'backend'   => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
        ], 40);

        $this->_addAttribute('estimated_%s_date_to', [
            'type'      => 'datetime',
            'label'     => '',
            'input'     => 'date',
            'backend'   => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
        ], 50);

        $this->_addAttribute('estimated_%s_text', [
            'type'      => 'text',
            'label'     => $this->name('Estimated %s Text', true),
            'input'     => 'textarea',
            'wysiwyg_enabled'           => 1,
            'is_html_allowed_on_front'  => 1,
        ], 60);

        return $this;
    }

    public function orderGroup($order)
    {
        $entityTypeId = $this->_eavSetup->getEntityTypeId($this->_modelName);

        $sets = $this->_attributeSet
            ->getResourceCollection()
            ->addFilter('entity_type_id', $entityTypeId);

        foreach ($sets as $set) {
            $this->_eavSetup->addAttributeGroup($entityTypeId, $set->getData('attribute_set_id'), $this->getGroup(), $order);
        }
        return $this;
    }
}
