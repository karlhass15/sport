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
 * @package     Plumrocket_Affiliate
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\SocialLoginPro\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.4.0', '<')) {
            $tableName = $setup->getTable('plumrocket_sociallogin_account');
            $connection = $setup->getConnection();
            if ($connection->isTableExists($tableName)
                && !$connection->tableColumnExists($tableName, 'additional')
            ) {
                $connection->addColumn(
                    $tableName,
                    'additional',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment'  => 'Additional info from social network',
                    ]
                );
            }

        }

        $setup->endSetup();
    }
}
