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
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

 namespace Plumrocket\Estimateddelivery\Setup;

 use Magento\Eav\Setup\EavSetupFactory;
 use Magento\Framework\Setup\ModuleContextInterface;
 use Magento\Framework\Setup\ModuleDataSetupInterface;
 use Magento\Framework\Setup\UpgradeDataInterface;

 class UpgradeData implements UpgradeDataInterface
 {
     /**
      * @var \Magento\Eav\Setup\EavSetupFactory
      */
     private $_eavSetupFactory;

     /**
      * Constructor
      */
     public function __construct(
         EavSetupFactory $eavSetupFactory
     ) {
         $this->_eavSetupFactory = $eavSetupFactory;
     }

     /**
      * {@inheritdoc}
      * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
      */
     public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
     {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.1', '<')) {

            /** @var EavSetup $eavSetup */
            $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

            $attributeNames = [
                'estimated_%s_enable',
                'estimated_%s_days_from',
                'estimated_%s_days_to',
                'estimated_%s_date_from',
                'estimated_%s_date_to',
                'estimated_%s_text'
            ];

            $sortOrder = 10;

            foreach (['shipping', 'delivery'] as $type) {
                if ("delivery" == $type) {
                    $sortOrder = 110;
                }
                foreach ($attributeNames as $attributeName) {
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        sprintf($attributeName, $type),
                        [''],
                        null,
                        $sortOrder
                    );
                    $sortOrder += 10;
                }
            }
        }

        $setup->endSetup();
     }
 }
