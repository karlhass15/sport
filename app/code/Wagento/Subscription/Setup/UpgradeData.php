<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Subscription\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * EAV setup factory
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var UpgradeWidgetData
     */
    private $upgradeWidgetData;

    /**
     * @var UpgradeWebsiteAttributes
     */
    private $upgradeWebsiteAttributes;

    /**
     * Constructor
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.1.1') < 0) {
            /** @var CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'subscription_configurate',
                'is_required',
                0
            );
            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'subscription_attribute_product',
                'is_required',
                0
            );

            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'subscription_configurate',
                'is_required',
                0
            );

            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'subscription_attribute_product',
                'is_required',
                0
            );
        }
        $setup->endSetup();
    }
}
