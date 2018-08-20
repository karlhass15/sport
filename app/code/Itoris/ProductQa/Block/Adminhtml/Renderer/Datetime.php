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
class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime {

	public function render(\Magento\Framework\DataObject $row) {
        if ($data = $row->getCreatedDatetime()) {
			$format = $this->getColumn()->getFormat();
            $data = $this->_localeDate->formatDateTime(
                $data,
                $format ?:\IntlDateFormatter::MEDIUM,
                $format ?:\IntlDateFormatter::MEDIUM,
                null,
                $this->getColumn()->getTimezone() === false ? 'UTC' : null );
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}
