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
namespace Itoris\ProductQa\Model\ResourceModel\Answers;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	protected $tableAnswers = 'itoris_productqa_answers';
	protected $tableQuestions = 'itoris_productqa_questions';
	protected $tableProduct = 'catalog_product_entity_varchar';
	protected $tableCustomer = 'customer_entity_varchar';
	protected $tableQuestionsVisibility = 'itoris_productqa_questions_visibility';
	protected $tableEavEntityType = 'eav_entity_type';
	protected $tableEavAttribute = 'eav_attribute';
	protected $_objectManager;
	protected $_helper;
	protected function _construct() {
		parent::_construct();
		$this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
		$this->_init('Itoris\ProductQa\Model\Answers','Itoris\ProductQa\Model\ResourceModel\Answers');
		$this->tableAnswers = $this->getResource()->getTable($this->tableAnswers);
		$this->tableQuestions = $this->getResource()->getTable($this->tableQuestions);
		$this->tableProduct = $this->getResource()->getTable($this->tableProduct);
		$this->tableCustomer = $this->getResource()->getTable($this->tableCustomer);
		$this->tableQuestionsVisibility = $this->getResource()->getTable($this->tableQuestionsVisibility);
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
		 $this->getSelect()->from(array('main_table' => $this->tableAnswers))
					->joinLeft(
			 			array('q' => $this->tableQuestions),
						'q.id = main_table.q_id',
			 			array(
							 'question'       => 'q.content',
							'question_inappr' => 'q.inappr',
							'question_id'     => 'q.id'
						)
		 			)
		 			->joinLeft(array('eType' => $this->tableEavEntityType), "eType.entity_type_code = 'catalog_product'")
		 			->joinLeft(array('eAttr' => $this->tableEavAttribute), "eAttr.attribute_code = 'name' and eAttr.entity_type_id = eType.entity_type_id")
					->joinLeft(array('p' => $this->tableProduct), 'p.entity_id = q.product_id and p.attribute_id = eAttr.attribute_id', 'value')
                    ->group('main_table.id');

		 if ($this->getDataHelper()->getRegistry()->registry('answersPage')) {
			 switch ($this->getDataHelper()->getRegistry()->registry('answersPage')) {
				 case \Itoris\ProductQa\Block\Adminhtml\Answers::PAGE_PENDING:
				 	$this->getSelect()->where('main_table.status = '. \Itoris\ProductQa\Model\Answers::STATUS_PENDING);
				 	break;
				 case \Itoris\ProductQa\Block\Adminhtml\Answers::PAGE_INAPPR:
					$this->getSelect()->where('main_table.inappr = 1');
					break;
			 }
		 }

		 return $this;
	 }

	/**
	 * Select customer answers
	 *
	 * @param $id
	 * @return \Itoris\ProductQa\Model\ResourceModel\Answers\Collection
	 */
	public function getCustomerAnswers($id) {
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$this->getSelect()->reset(null)
			->from(array('main_table' => $this->tableAnswers),
				  array('main_table.id', 'main_table.created_datetime', 'main_table.content', 'main_table.status',)
			)
			->joinLeft(array('q' => $this->tableQuestions), 'main_table.q_id = q.id', array('question' => 'q.content', 'product_id' => 'q.product_id'))
			->joinLeft(array('eType' => $this->tableEavEntityType), "eType.entity_type_code = 'catalog_product'")
		 	->joinLeft(array('eAttr' => $this->tableEavAttribute), "eAttr.attribute_code = 'name' and eAttr.entity_type_id = eType.entity_type_id")
			->joinLeft(array('p' => $this->tableProduct), 'p.entity_id = q.product_id and p.attribute_id = eAttr.attribute_id  and p.store_id = '.intval($storeManager->getStore()->getId()), array('product_name' => 'p.value'))
			->joinLeft(array('v' => $this->tableQuestionsVisibility), 'v.q_id = q.id',array('store_id' => 'v.store_id'))
			->where('main_table.inappr = 0')
			->where('main_table.submitter_type =?', \Itoris\ProductQa\Model\Answers::SUBMITTER_CUSTOMER)
			->where('main_table.customer_id = ?', $id)
			->where('main_table.status != ?', \Itoris\ProductQa\Model\Answers::STATUS_NOT_APPROVED)
            ->group('main_table.id')
			->order('main_table.created_datetime desc');
            
		return $this;
	}
	public function getAnswerCount(){
		$countSelect = clone $this->getSelect();
		$countSelect->reset(null)->from(array('main_table' => $this->tableAnswers))
			->joinLeft(
				array('q' => $this->tableQuestions),
				'q.id = main_table.q_id',
				array(
					'question'       => 'q.content',
					'question_inappr' => 'q.inappr',
					'question_id'     => 'q.id'
				)
			)
			->joinLeft(array('eType' => $this->tableEavEntityType), "eType.entity_type_code = 'catalog_product'")
			->joinLeft(array('eAttr' => $this->tableEavAttribute), "eAttr.attribute_code = 'name' and eAttr.entity_type_id = eType.entity_type_id")
			->joinLeft(array('p' => $this->tableProduct), 'p.entity_id = q.product_id and p.attribute_id = eAttr.attribute_id', 'value');
		$countSelect->where('main_table.status = '. \Itoris\ProductQa\Model\Answers::STATUS_APPROVED);
		$countSelect->where('q.status = '. \Itoris\ProductQa\Model\Questions::STATUS_APPROVED);
		$countSelect->where('q.product_id = '. (int)$this->getDataHelper()->getRegistry()->registry('current_product')->getId());
		return  count($this->_fetchAll($countSelect));
	}
	/**
	 * Select question answers
	 *
	 * @param $questionId
	 * @return \Itoris\ProductQa\Model\ResourceModel\Answers\Collection
	 */
	public function questionAnswers($questionId) {
		$this->getSelect()->reset(null)->from(array('main_table' => $this->tableAnswers))
							->joinLeft(array('c' => $this->tableCustomer),
									'c.entity_id = main_table.customer_id and
									(c.attribute_id = 5 or c.attribute_id = 7)',
									array('user_name' => 'group_concat(c.value SEPARATOR " ")')
							)
							->where('main_table.q_id = ?', $questionId)
							->group('main_table.id')
							->order('main_table.created_datetime desc');
		return $this;
	}
}

