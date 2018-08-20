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
namespace Itoris\ProductQa\Block\Adminhtml\Renderer;
class Visible extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Store {

	public function render(\Magento\Framework\DataObject $row) {
        $skipAllStoresLabel = $this->_getShowAllStoresLabelFlag();
        $origStores = $row->getData($this->getColumn()->getIndex());
		$origStores = $row->getData($this->getColumn()->getIndex());
		$origStores = explode(',', $row->getVisible());
        $stores = array();
        if (is_array($origStores)) {
            foreach ($origStores as $origStore) {
                if (is_numeric($origStore) && $origStore == 0) {
                    if (!$skipAllStoresLabel) {
                        $stores[] = __('All Store Views');
                    }
                }
                elseif (is_numeric($origStore) && $storeName = $this->_getStoreModel()->getStoreName($origStore)) {
                    if ($storeName) {
                        $store = $this->_getStoreModel()->getStoreNameWithWebsite($origStore);
                    } else {
                        $store = $this->_getStoreModel()->getStoreNamePath($origStore);
                    }
                    $layers = array();
                    foreach (explode('/', $store) as $key=>$value) {
                        $layers[] = str_repeat("&nbsp;", $key*3).$value;
                    }
                    $stores[] = implode('<br/>', $layers);
                }
                else {
                    $stores[] = $origStore;
                }
            }
        } else {
            if (is_numeric($origStores) && $storeName = $this->_getStoreModel()->getStoreName($origStores)) {
                if ($this->getColumn()->getStoreView()) {
                    $store = $this->_getStoreModel()->getStoreNameWithWebsite($origStores);
                } else {
                    $store = $this->_getStoreModel()->getStoreNamePath($origStores);
                }
                $layers = array();
                foreach (explode('/', $store) as $key=>$value) {
                    $layers[] = str_repeat("&nbsp;", $key*3).$value;
                }
                $stores[] = implode('<br/>', $layers);
            }
            elseif (is_numeric($origStores) && $origStores == 0) {
                if (!$skipAllStoresLabel) {
                    $stores[] = __('All Store Views');
                }
            }
            elseif (is_null($origStores) && $row->getStoreName()) {
                return $row->getStoreName() . ' ' . $this->__('[deleted]');
            }
            else {
                $stores[] = $origStores;
            }
        }

        return $stores ? join('<br/> ', $stores) : '&nbsp;';
    }
}
