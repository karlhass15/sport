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
namespace Itoris\ProductQa\Model\ResourceModel\Questions;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	protected $tableQuestions = 'itoris_productqa_questions';
	protected $tableProduct = 'catalog_product_entity';
	protected $tableProductName = 'catalog_product_entity_varchar';
	protected $tableEavEntityType = 'eav_entity_type';
	protected $tableEavAttribute = 'eav_attribute';
	protected $tableQuestionVisibility = 'itoris_productqa_questions_visibility';
	protected $tableAnswers = 'itoris_productqa_answers';
	protected $_objectManager;
	protected $_helper;
	public static $itorisRecords;

	protected function _construct() {
		$this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
		$this->_init('Itoris\ProductQa\Model\Questions','Itoris\ProductQa\Model\ResourceModel\Questions');
		$this->tableQuestions = $this->getResource()->getTable($this->tableQuestions);
		$this->tableProduct = $this->getResource()->getTable($this->tableProduct);
		$this->tableProductName = $this->getResource()->getTable($this->tableProductName);
		$this->tableQuestionVisibility = $this->getResource()->getTable($this->tableQuestionVisibility);
		$this->tableAnswers = $this->getResource()->getTable($this->tableAnswers);
		$this->tableEavEntityType = $this->getResource()->getTable($this->tableEavEntityType);
		$this->tableEavAttribute = $this->getResource()->getTable($this->tableEavAttribute);
	}
	/** @return \Itoris\ProductQa\Helper\Data */
	public function getDataHelper(){
		if(!$this->_helper){
			$this->_helper=$this->_objectManager->create('Itoris\ProductQa\Helper\Data');
		}
		return $this->_helper;
	}
	protected function _initSelect() {

		$this->getSelect()->from(array('main_table' => $this->tableQuestions))
					->joinLeft(array('p1' => $this->tableProduct), 'p1.entity_id = main_table.product_id', 'sku')
		 			->joinLeft(array('eType' => $this->tableEavEntityType), "eType.entity_type_code = 'catalog_product'")
		 			->joinLeft(array('eAttr' => $this->tableEavAttribute), "eAttr.attribute_code = 'name' and eAttr.entity_type_id = eType.entity_type_id")
					->joinLeft(array('p2' => $this->tableProductName), 'p2.entity_id = main_table.product_id and p2.attribute_id = eAttr.attribute_id', array('value' => 'p2.value'))
					->joinLeft(array('v' => $this->tableQuestionVisibility), 'v.q_id = main_table.id',array('visible' => 'group_concat(DISTINCT v.store_id)'))
					->joinLeft(array('a' => $this->tableAnswers), 'a.q_id = main_table.id', array('answers' => 'COUNT(DISTINCT a.id)'))
					->group('main_table.id');

		if ($this->getDataHelper()->getRegistry()->registry('questionsPage')) {
			switch ($this->getDataHelper()->getRegistry()->registry('questionsPage')) {
				case \Itoris\ProductQa\Block\Adminhtml\Questions::PAGE_PENDING:
					$this->getSelect()->where('main_table.status = '. \Itoris\ProductQa\Model\Questions::STATUS_PENDING);
					break;
			case \Itoris\ProductQa\Block\Adminhtml\Questions::PAGE_INAPPR:
				$this->getSelect()->where('main_table.inappr = 1');
				break;
			case \Itoris\ProductQa\Block\Adminhtml\Questions::PAGE_NOT_ANSWERED:
				$this->getSelect()->having(' answers = 0 ');
				break;
			}
		}
		$this->setTotalRecords();
		return $this;
	}

	/**
	 * Select customer questions
	 *
	 * @param $id
	 * @return Itoris_ProductQa_Model_ResourceModel_Questions_Collection
	 */
	public function getCustomerQuestions($id){
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$this->getSelect()->reset(null)
			->from(array('main_table' => $this->tableQuestions), array('main_table.id', 'main_table.created_datetime', 'main_table.content', 'main_table.status', 'main_table.product_id', ))
			->joinLeft(array('eType' => $this->tableEavEntityType), "eType.entity_type_code = 'catalog_product'")
			->joinLeft(array('eAttr' => $this->tableEavAttribute), "eAttr.attribute_code = 'name' and eAttr.entity_type_id = eType.entity_type_id")
			->joinLeft(array('p' => $this->tableProductName), 'p.entity_id = main_table.product_id and p.attribute_id = eAttr.attribute_id and p.store_id = '.intval($storeManager->getStore()->getId()), array('product_name' => 'p.value'))
			->joinLeft(array('a' => $this->tableAnswers), 'a.q_id = main_table.id', array('answers' => 'COUNT(DISTINCT a.id)'))
			->joinLeft(array('v' => $this->tableQuestionVisibility), 'v.q_id = main_table.id',array('store_id' => 'v.store_id'))
			->where('main_table.inappr = 0')
			->where('main_table.submitter_type =?', \Itoris\ProductQa\Model\Questions::SUBMITTER_CUSTOMER)
			->where('main_table.customer_id = ?', $id)
			->where('main_table.status != ?', \Itoris\ProductQa\Model\Questions::STATUS_NOT_APPROVED)
			->group('main_table.id')
			->order('main_table.created_datetime desc');
		return $this;
	}

	/**
	 * Overload parent getAllIds, because HAVING should be reset too
	 *
	 * @return array
	 */
	public function getAllIds() {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Zend_Db_Select::ORDER);
        $idsSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(\Zend_Db_Select::COLUMNS);
        $idsSelect->reset(\Zend_Db_Select::HAVING);
        $idsSelect->columns(
            'main_table.' . $this->getResource()->getIdFieldName()
        );
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

	protected function setTotalRecords() {
		$countSelect = clone $this->getSelect();
		$countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
		$countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
		$this->_totalRecords = count($this->_fetchAll($countSelect));
	}

	public function getSize() {
        $this->setTotalRecords();
        return $this->_totalRecords;
    }
	public function getSizeWithApprovalProduct(){
		if(!self::$itorisRecords){
			$this->setSizeWithApprovalProduct();
		}

		return self::$itorisRecords;
	}
	public function setSizeWithApprovalProduct(){
		$product = $this->getDataHelper()->getRegistry()->registry('current_product');
        if (!$product) return;
		$countSelect = clone $this->getSelect();
		$countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
		$countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
		$countSelect->where('main_table.product_id = '.(int)$product->getId());
		$countSelect->where('main_table.status = 5');
		$this->_totalRecords = count($this->_fetchAll($countSelect));
		self::$itorisRecords=$this->_totalRecords;
	}
}
