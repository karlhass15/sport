<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $eavSetup;

    /**
     * UpgradeSchema constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $eavSetup)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavSetup = $eavSetup;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            // Get module table
            $tableName = $setup->getTable('wagento_subscription');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // Declare data
                $columns = [
                    'date_start' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'nullable' => true,
                        'comment' => 'Start Date',
                    ],
                    'date_end' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'nullable' => true,
                        'comment' => 'End Date',
                    ],
                    'cycle' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Cycle',
                    ]
                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavSetup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'subscription_configurate',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Subscription Option',
                    'input' => 'select',
                    'source' => \Wagento\Subscription\Model\Subscription\Attribute\Source\SubcriptionOptions::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => 'simple,downloadable,virtual',
                    'group' => 'Subscription Options'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            if ($setup->getConnection()->isTableExists($setup->getTable('wagento_subscription')) == true) {
                $setup->getConnection()->dropColumn($setup->getTable('wagento_subscription'), 'cycle');
                $setup->getConnection()->dropColumn($setup->getTable('wagento_subscription'), 'date_start');
                $setup->getConnection()->dropColumn($setup->getTable('wagento_subscription'), 'date_end');

                $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavSetup]);
                $eavSetup->removeAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'subscription_attribute_product'
                );

                $columns = [
                    'how_many' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'How Many',
                    ],
                    'shipping_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '',
                        'comment' => 'Shipping Type',
                    ],
                    'shopping_rule' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '',
                        'comment' => 'Shopping Rule',
                    ],
                    'tiered_price' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Tiered Price',
                    ]
                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {
                    $connection->addColumn($setup->getTable('wagento_subscription'), $name, $definition);
                }
            }
        }
        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            $tableName = $setup->getTable('brtee_subscribe_order');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Id'
                    )
                    ->addColumn(
                        'customer_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'default' => false],
                        'Customer Id'
                    )
                    ->addColumn(
                        'subscribe_order_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'default' => false],
                        'Subscriber\'s order id'
                    )
                    ->addColumn(
                        'frequency',
                        Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false, 'default' => false],
                        'Frequency'
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false, 'default' => '1'],
                        'Status'
                    )
                    ->addColumn(
                        'last_renewed',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Last Renewed At'
                    )
                    ->addColumn(
                        'next_renewed',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Next Renewed At'
                    )
                    ->addColumn(
                        'next_renewed',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Next Renewed At'
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Created At'
                    )
                    ->addColumn(
                        'updated_at',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Updated At'
                    )
                    ->setComment('Braintree Subscription Order Table')
                    ->setOption('type', 'InnoDB');
                $setup->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '2.0.5') < 0) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('wagento_subscription'),
                'tiered_price',
                'discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'default' => 0.00,
                    'comment' => 'Discount'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.6') < 0) {
            $eavTable = $setup->getTable('brtee_subscribe_order');

            $columns = [
                'store_id' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '5',
                    'nullable' => false,
                    'comment' => 'Store Id',
                ],
                'sub_start_date' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'length' => '5',
                    'nullable' => false,
                    'comment' => 'Subscription Start Date',
                ],
                'sub_plan_id' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => '11',
                    'nullable' => false,
                    'comment' => 'Subscription Plan/Campignon Id',
                ],

            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($eavTable, $name, $definition);
            }

            $setup->getConnection()->renameTable('brtee_subscribe_order', 'wagento_subscription_order');
        }

        if (version_compare($context->getVersion(), '2.0.7') < 0) {
            /**/
            $quoteItemTable = $setup->getTable('quote_item');

            $columns = [
                'is_subscribed' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'comment' => 'Check product is subscribed or not by customer',
                ],

            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($quoteItemTable, $name, $definition);
            }

            /**/
            $salesOrderItemTable = $setup->getTable('sales_order_item');
            $columns = [
                'is_subscribed' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'comment' => 'Check product is subscribed or not by customer',
                ],

            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($salesOrderItemTable, $name, $definition);
            }

            /*changecolumn name sub_plan_id to sub_order_item_id */
            $setup->getConnection()->changeColumn(
                $setup->getTable('wagento_subscription_order'),
                'sub_plan_id',
                'sub_order_item_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => '11',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Subscription Order Item Id'
                ]
            );

            /* drop column frequency*/
            $subOrderTable = $setup->getTable('wagento_subscription_order');
            $setup->getConnection()->dropColumn($subOrderTable, 'frequency');
        }

        if (version_compare($context->getVersion(), '2.0.8') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavSetup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'subscription_attribute_product',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Subscription Name',
                    'input' => 'select',
                    'source' => \Wagento\Subscription\Model\Subscription\Attribute\Source\SubscriptionList::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => 'simple,downloadable,virtual',
                    'group' => 'Subscription Options'
                ]
            );
        }

        /**
         *  Add Initial Fee Column to quote, sales_order, sales_invoice and  sales_creditmemo
         */

        if (version_compare($context->getVersion(), '2.0.9') < 0) {
            $quoteTable = 'quote';
            $orderTable = 'sales_order';
            $invoiceTable = 'sales_invoice';
            $creditmemoTable = 'sales_creditmemo';

            /*Quote Table*/
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteTable),
                    'initial_fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Wagento Subscription Initial Fee'
                    ]
                );

            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'initial_fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Wagento Subscription Initial Fee'

                    ]
                );

            //Invoice table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($invoiceTable),
                    'initial_fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Wagento Subscription Initial Fee'

                    ]
                );

            //Credit memo table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($creditmemoTable),
                    'initial_fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Wagento Subscription Initial Fee'

                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.1.0') < 0) {
            /*Subscription Order Table*/
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('wagento_subscription_order'),
                    'how_many',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '12',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Wagento Subscription How Many'
                    ]
                );

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('wagento_subscription_order'),
                    'billing_count',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '12',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Wagento subscription number of times billing'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.1.1') < 0) {
            /*Subscription Order Table*/
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('wagento_subscription_order'),
                    'billing_address_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '12',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'If Customer Change Billing Address'
                    ]
                );

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('wagento_subscription_order'),
                    'shipping_address_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '12',
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'If Customer Change Shipping Address'
                    ]
                );

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('wagento_subscription_order'),
                    'public_hash',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'If Customer Change Credit Card Details'
                    ]
                );

            /*Subscription Order Table*/
            $eavTable = $setup->getTable('wagento_subscription_order');
            $columns = [
                'sub_name' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Subscription Plan Name',
                ],
                'sub_frequency' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '5',
                    'nullable' => false,
                    'comment' => 'Subscription Frequency',
                ],
                'sub_fee' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'comment' => 'Subscription Initial Fee',
                ],
                'sub_shipping_type' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'comment' => 'Subscription Shipping Type',
                ],
                'sub_shipping_rule' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'comment' => 'Subscription Shipping Rule',
                ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($eavTable, $name, $definition);
            }

            /*Subscription Order Table*/
            $eavTable = $setup->getTable('wagento_subscription_order');
            $columns = [
                'sub_discount' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'comment' => 'Subscription Discount Amount',
                ],
                'sub_product_id' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => '10',
                    'comment' => 'Subscription Product Id',
                ]
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($eavTable, $name, $definition);
            }
        }
        $setup->endSetup();
    }
}
