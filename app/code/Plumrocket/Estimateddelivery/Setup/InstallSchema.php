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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Estimateddelivery\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.1.2', '<')) {
            /**
             * Create table `plumrocket_estimateddelivery_order_item`
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('plumrocket_estimateddelivery_order_item'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    ], 'Id')
                ->addColumn('item_id', Table::TYPE_INTEGER, null, [
                    'unsigned'  => true,
                    'nullable'  => false,
                    ], 'Order Item Id')
                ->addColumn('delivery', Table::TYPE_TEXT, null, [
                    'nullable'  => true,
                    ], 'Estimated Delivery')
                ->addColumn('shipping', Table::TYPE_TEXT, null, [
                    'nullable'  => true,
                    ], 'Estimated Shipping')
                ->addIndex(
                    $installer->getIdxName('plumrocket_estimateddelivery_order_item', ['item_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['item_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $installer->getFkName('plumrocket_estimateddelivery_order_item', 'item_id', 'sales_order_item', 'item_id'),
                    'item_id',
                    $installer->getTable('sales_order_item'),
                    'item_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->setComment('Estimated Delivery Order Item');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
