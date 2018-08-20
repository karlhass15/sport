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
namespace Itoris\ProductQa\Block\Adminhtml\Renderer\Edit;
class PostedBy extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text {
	protected $_helper;
	protected $_objectManager;
	public function __construct() {
		parent::_construct();
		$this->setTemplate('Itoris_ProductQa::grid.phtml');
		$this->setRowClickCallback('openGridRow');
		$this->_emptyText = __('No records found.');
		$this->_defaultLimit = 1000;
	}
	public function getObjectManager(){
		if($this->_objectManager)
			return $this->_objectManager;
		return $this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
	}
	public function render(\Magento\Framework\DataObject $row) {
		$html = '<div style="text-align: center">';
		switch($row->getSubmitterType()){
			case \Itoris\ProductQa\Model\Answers::SUBMITTER_CUSTOMER:
				$html .= '<a href="'. $this->getObjectManager()->create('Itoris\ProductQa\Helper\Data')->getUrl('customer/index/edit/id/'. $row->getCustomerId() .'/') .'" style="color: red;">'. $row->getUserName() .'</a><br/>'
				      . __('Customer');
				break;
			case \Itoris\ProductQa\Model\Answers::SUBMITTER_ADMIN:
				$html .= __('Administrator');
				break;
			case \Itoris\ProductQa\Model\Answers::SUBMITTER_VISITOR:
				$html .= __('Guest');
				break;
		}
		$html .= '</div>';
		return $html;
	}
}
