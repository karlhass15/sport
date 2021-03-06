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
namespace Itoris\ProductQa\Helper;
class Form {
    protected $_objectManager;
    public function getObjectManager(){
        if(!$this->_objectManager){
            $this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->_objectManager;
    }
	public function getStoreSelectOptions() {
		/* @var $storeModel \Magento\Store\Model\System */
		$storeModel = $this->getObjectManager()->get('Magento\Store\Model\System\Store');
        if(!$storeModel){
            $storeModel = $this->getObjectManager()->create('Magento\Store\Model\System\Store');
        }
        //Magento\Store\Model\System

        $options = array();

        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
						$groupId = 'website_' . $website->getCode();
                        $options['website_' . $website->getCode()] = array(
                            'label'    => $website->getName(),
                            'value' => array(),
                        );
                    }
                    $options[$groupId]['value'][] = array(
						'value' => $store->getId(),
                        'label'    => $store->getName(),
						'title'   => $store->getName(),
                    );
                }
            }
        }

        return $options;
    }
}
