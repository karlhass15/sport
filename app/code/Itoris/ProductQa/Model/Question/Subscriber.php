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
namespace Itoris\ProductQa\Model\Question;
class Subscriber extends \Magento\Framework\Model\AbstractModel {

	public function _construct() {
		$this->_init('Itoris\ProductQa\Model\ResourceModel\Question\Subscriber');
	}

	public function isSubscribed($questionId, $customerId = null, $email = null) {
		$collection = $this->getCollection()
				->addFieldToFilter('question_id', array('eq' => $questionId));
		if ($customerId) {
			$collection->addFieldToFilter('customer_id', array('eq' => $customerId));
		}
		if ($email) {
			$collection->addFieldToFilter('email', array('eq' => $email));
		}

		return count($collection);
	}
}
