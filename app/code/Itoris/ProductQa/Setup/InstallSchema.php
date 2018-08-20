<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCTQA
 * @copyright  Copyright (c) 2017 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
namespace Itoris\ProductQa\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public $magentoConfigTable = 'core_config_data';

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Itoris\ProductQa\Helper\Data $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\ProductQa\Helper\Data');
        $setup->startSetup();
        if(!$setup->tableExists($setup->getTable('itoris_productqa_questions'))) {
            $setup->run("CREATE TABLE  {$setup->getTable('itoris_productqa_questions')} (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `inappr` BOOLEAN NOT NULL ,
                        `created_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `status` INT UNSIGNED NOT NULL ,
                        `submitter_type` INT UNSIGNED NOT NULL ,
                        `product_id` INT UNSIGNED NOT NULL ,
                        `nickname` VARCHAR( 30 ) NOT NULL ,
                        `content` TEXT NOT NULL ,
                        `customer_id` INT UNSIGNED NULL ,
                        `notify` BOOLEAN NOT NULL ,
                        `email` VARCHAR( 255 ) NULL,
                         FOREIGN KEY (`product_id`)
                         REFERENCES {$setup->getTable('catalog_product_entity')} (`entity_id`) ON DELETE cascade ON UPDATE CASCADE,
                        INDEX ( `product_id`), INDEX(`customer_id`)
                    ) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");
            }
        if(!$setup->tableExists($setup->getTable('itoris_productqa_questions_ratings'))) {
            $setup->run(" CREATE TABLE  {$setup->getTable('itoris_productqa_questions_ratings')} (
                        `customer_id` INT UNSIGNED NULL ,
                        `q_id` INT UNSIGNED NOT NULL ,
                        `value` ENUM( '-1', '1' ) NOT NULL ,
                         FOREIGN KEY (`customer_id`)
                         REFERENCES {$setup->getTable('customer_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE SET NULL,
                         FOREIGN KEY (`q_id`)
                         REFERENCES {$setup->getTable('itoris_productqa_questions')} (`id`) ON DELETE cascade ON UPDATE CASCADE,
                         UNIQUE ( `q_id`,`customer_id` )
                        ) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");
            $setup->run("alter table {$setup->getTable('itoris_productqa_questions_ratings')} add `guest_ip` varchar(19) null;");
            $setup->run("alter table {$setup->getTable('itoris_productqa_questions_ratings')} add unique question_rating_unique (`q_id`,`customer_id`,`guest_ip`);");
            $setup->run("alter table {$setup->getTable('itoris_productqa_questions_ratings')} drop index q_id;");
               }
        if(!$setup->tableExists($setup->getTable('itoris_productqa_questions_visibility'))) {
            $setup->run(" CREATE TABLE IF NOT EXISTS  {$setup->getTable('itoris_productqa_questions_visibility')} (
                        `q_id` INT UNSIGNED NOT NULL ,
                        `store_id` SMALLINT UNSIGNED NOT NULL ,
                         FOREIGN KEY (`q_id`)
                         REFERENCES {$setup->getTable('itoris_productqa_questions')} (`id`) ON DELETE cascade ON UPDATE CASCADE,
                         FOREIGN KEY (`store_id`)
                         REFERENCES {$setup->getTable('store')} (`store_id`) ON DELETE cascade ON UPDATE CASCADE,
                         UNIQUE ( `q_id`,`store_id` )
                       ) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");
             }
        if(!$setup->tableExists($setup->getTable('itoris_productqa_answers'))) {
            $setup->run(" CREATE TABLE  {$setup->getTable('itoris_productqa_answers')} (
		                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `q_id` INT UNSIGNED NOT NULL ,
                        `inappr` BOOLEAN NOT NULL ,
                        `created_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `status` INT UNSIGNED NOT NULL ,
                        `submitter_type` INT UNSIGNED NOT NULL ,
                        `nickname` VARCHAR( 30 ) NOT NULL ,
                        `content` TEXT NOT NULL ,
                        `customer_id` INT UNSIGNED NULL ,
                         FOREIGN KEY (`q_id`)
                         REFERENCES {$setup->getTable('itoris_productqa_questions')} (`id`) ON DELETE cascade ON UPDATE CASCADE,
                        INDEX ( `q_id`), INDEX (`customer_id`)
                    ) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
                ");
            }
        if(!$setup->tableExists($setup->getTable('itoris_productqa_answers_ratings'))) {
                                $setup->run("CREATE TABLE {$setup->getTable('itoris_productqa_answers_ratings')} (
                                `customer_id` INT UNSIGNED NULL ,
                                `a_id` INT UNSIGNED NOT NULL ,
                                `value` ENUM( '-1', '1' ) NOT NULL ,
                                FOREIGN KEY (`customer_id`)
                                REFERENCES {$setup->getTable('customer_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE SET NULL,
                                FOREIGN KEY (`a_id`)
                                REFERENCES {$setup->getTable('itoris_productqa_answers')} (`id`) ON DELETE cascade ON UPDATE CASCADE,
                                UNIQUE ( `a_id`, `customer_id` )
                                ) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");
            $setup->run("alter table {$setup->getTable('itoris_productqa_answers_ratings')} add `guest_ip` varchar(19) null;");
            $setup->run("alter table {$setup->getTable('itoris_productqa_answers_ratings')} add unique answer_rating_unique (`a_id`,`customer_id`,`guest_ip`);");
            $setup->run("alter table {$setup->getTable('itoris_productqa_answers_ratings')} drop index a_id;");
        }
        if(!$setup->tableExists($setup->getTable('itoris_productqa_question_subscriber'))) {
            $setup->run("create table {$setup->getTable('itoris_productqa_question_subscriber')} (
                        `subscriber_id` int unsigned not null auto_increment primary key,
                        `question_id` int unsigned not null,
                        `email` varchar(255) null,
                        `customer_id` int(10) unsigned null,
                        `store_id` smallint(5) unsigned not null,
                        foreign key (`question_id`) references {$setup->getTable('itoris_productqa_questions')} (`id`) on delete cascade on update cascade,
                        foreign key (`customer_id`) references {$setup->getTable('customer_entity')} (`entity_id`) on delete cascade on update cascade,
                        foreign key (`store_id`) references {$setup->getTable('store')} (`store_id`) on delete cascade on update cascade
                        ) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

                      ");
        }
        $strInsertValue='';
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_ENABLED);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_ENABLED."', '1'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_VISIBLE);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_VISIBLE."', '3'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_CAPTCHA);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_CAPTCHA."', '18'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_VISITOR_POST);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_VISITOR_POST."', '5'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_VISITOR_CAN_RATE);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_VISITOR_CAN_RATE."', '0'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_QUESTIONS_APPROVAL);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_QUESTIONS_APPROVAL."', '22'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_ANSWERS_APPROVAL);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_ANSWERS_APPROVAL."', '25'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_QUESTION_LENGTH);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_QUESTION_LENGTH."', '255'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_ANSWER_LENGTH);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_ANSWER_LENGTH."', '1000'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_QUESTIONS_PER_PAGE);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_QUESTIONS_PER_PAGE."', 10),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_ADMIN_EMAIL);
        if(!isset($configNote)) {
            $strInsertValue = $strInsertValue . "('" . $helper::XML_PATH_MODULE_ADMIN_EMAIL . "', 'owner@example.com'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_ALLOW_SUBSCRIBING_QUESTION);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_ALLOW_SUBSCRIBING_QUESTION."', '0'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_TEMPLATE_ADMIN_NOTIFICATION);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_TEMPLATE_ADMIN_NOTIFICATION."', 'itoris_email_productqa_admin'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_TEMPLATE_USER_NOTIFICATION);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_TEMPLATE_USER_NOTIFICATION."', 'itoris_email_productqa_user'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_TEMPLATE_GUEST_NOTIFICATION);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_TEMPLATE_GUEST_NOTIFICATION."', 'itoris_email_productqa_guest'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_TEMPLATE_SENDER_ADMIN_NOTIFICATION);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_TEMPLATE_SENDER_ADMIN_NOTIFICATION."', 'general'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_TEMPLATE_SENDER_USER_NOTIFICATION);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_TEMPLATE_SENDER_USER_NOTIFICATION."', 'general'),";
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_SENDER_GUEST_NOTIFICATION);
        if(!isset($configNote)){
            $strInsertValue=$strInsertValue."('".$helper::XML_PATH_MODULE_SENDER_GUEST_NOTIFICATION."', 'general')";
        }
        if(strlen($strInsertValue)>0) {
            $strInsertValue = trim($strInsertValue,',');
            $setup->run("
            INSERT INTO {$setup->getTable($this->magentoConfigTable)}
            (path, value)
            VALUES $strInsertValue
            ");
        }
        $setup->endSetup();
    }
}
