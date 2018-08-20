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
namespace Itoris\ProductQa\Model;
class Questions extends \Magento\Framework\Model\AbstractModel {

	/** @var $_resource  */
	protected $_resource;
	private $tableQuestions = 'itoris_productqa_questions';
	private $tableAnswers = 'itoris_productqa_answers';
	private $tableAnswersRatings = 'itoris_productqa_answers_ratings';
	private $tableQuestionsRatings = 'itoris_productqa_questions_ratings';
	private $tableQuestionsVisibility = 'itoris_productqa_questions_visibility';
	private $tableEavEntityType = 'eav_entity_type';
	private $tableEavAttribute = 'eav_attribute';
	protected $_objectManager;
	private $tableCatalogProductEntityVarchar = 'catalog_product_entity_varchar';
    protected  $_connection;
	const SUBMITTER_ADMIN = 1;
	const SUBMITTER_CUSTOMER = 2;
	const SUBMITTER_VISITOR = 3;
	const STATUS_PENDING = 4;
	const STATUS_APPROVED = 5;
	const STATUS_NOT_APPROVED = 6;
	const SORT_RECENT = 7;
	const SORT_OLDEST = 8;
	const SORT_HELPFUL_ANSWERS = 9;
	const SORT_RECENT_ANSWERS = 10;
	const SORT_OLDEST_ANSWERS = 11;
	const SORT_MOST_ANSWERS = 12;
	protected $_helper;
	public function _construct() {
		$this->_init('Itoris\ProductQa\Model\ResourceModel\Questions');
		$this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
		$this->_resource = $this->getResourceConnection();
		$this->_connection = $this->_resource->getConnection();
		$this->tableQuestions = $this->_resource->getTable($this->tableQuestions);
		$this->tableAnswers = $this->_resource->getTable($this->tableAnswers);
		$this->tableAnswersRatings = $this->_resource->getTable($this->tableAnswersRatings);
		$this->tableQuestionsRatings = $this->_resource->getTable($this->tableQuestionsRatings);
		$this->tableQuestionsVisibility = $this->_resource->getTable($this->tableQuestionsVisibility);
		$this->tableEavEntityType = $this->_resource->getTable($this->tableEavEntityType);
		$this->tableEavAttribute = $this->_resource->getTable($this->tableEavAttribute);
		$this->tableCatalogProductEntityVarchar = $this->_resource->getTable($this->tableCatalogProductEntityVarchar);
	}
	/** @return \Itoris\ProductQa\Helper\Data */
	public function getDataHelper(){
		if(!$this->_helper){
			$this->_helper=$this->_objectManager->create('Itoris\ProductQa\Helper\Data');
		}
		return $this->_helper;
	}
	/**
	 * Save question
	 *
	 * @param $data
	 * @return null|string
	 */
	public function addQuestion($data) {
		$status = (int)$data['status'];
		$submitterType = (int)$data['submitter_type'];
		$productId = (int)$data['product_id'];
		$nickname = $this->_connection->quote($data['nickname']);
		$content = $this->_connection->quote($data['content']);
		$customerId = (int)$data['customer_id'];
		if(!$customerId){
			$customerId='NULL';
		}
		$notify = (int)$data['notify'];
		$notifyEmail = isset($data['notify_email']) ? $this->_connection->quote($data['notify_email']) : '\'\'';
		$newsletter = isset($data['newsletter']) ? (int)$data['newsletter'] : 0;
		$newsletterEmail = isset($data['newsletter_email']) ? $data['newsletter_email'] : '';
		$this->_connection->query("INSERT into $this->tableQuestions (`status`, `submitter_type`,
								`product_id`, `nickname`, `content`, `customer_id`, `notify`, `email`) VALUES
								($status, $submitterType, $productId, $nickname, $content, $customerId, $notify, $notifyEmail)");

		$qId = $this->_connection->lastInsertId();
		if ($this->getDataHelper()->getRegistry()->registry('q_id')) {
			$this->getDataHelper()->getRegistry()->unregister('q_id');
		}
		$this->getDataHelper()->getRegistry()->register('q_id', $qId);
		$this->updateVisibility($qId, $data['store_id']);
		$output = null;
		if ($newsletter) {
			 $output = $this->singUpNewsletter($customerId, $newsletterEmail);
		}
		return $output;
	}
	public function getResourceConnection(){
		return $this->getResource();
	}
	/**
	 * Change question visibility
	 *
	 * @param $id
	 * @param $storeIds
	 */
	public function updateVisibility($id, $storeIds) {
		$values = array();
		if (is_array($storeIds)) {
			foreach ($storeIds as $key => $sId) {
				$values[] = '(' . $id . ',' . (int)$sId . ')';
			}
			$values = implode(',', $values);
		} else {
			$values = '(' . $id . ',' . (int)$storeIds . ')';
		}
		$this->_connection->query("delete from $this->tableQuestionsVisibility where q_id = $id");
		$this->_connection->query("INSERT INTO $this->tableQuestionsVisibility (`q_id`, `store_id`) values $values");
	}

	/**
	 * Retrieve questions for a product
	 *
	 * @param $productId
	 * @param int $mode
	 * @return array
	 */
	public function getQuestions($productId, $mode = \Itoris\ProductQa\Model\Questions::SORT_RECENT, $includeQuestionId = null, $searchQuery = null) {
		$storeId = $this->getDataHelper()->getStoreManager()->getStore()->getId();
		$order = 'e.created_datetime desc';
		$order_by_answers = '';
		$select_answers = '';
		$join_answers_rating = '';
		switch ($mode) {
			case \Itoris\ProductQa\Model\Questions::SORT_OLDEST :
				$order = 'e.created_datetime';
				break;
			case \Itoris\ProductQa\Model\Questions::SORT_MOST_ANSWERS :
				$order_by_answers = ' answers desc,';
				break;
			case \Itoris\ProductQa\Model\Questions::SORT_RECENT_ANSWERS :
				$select_answers = ', max(a.created_datetime) as answer_date';
				$order_by_answers = ' answer_date desc,';
				break;
			case \Itoris\ProductQa\Model\Questions::SORT_OLDEST_ANSWERS :
				$select_answers = ', min(if(a.created_datetime,a.created_datetime,now())) as answer_date';
				$order_by_answers = ' answer_date,';
				break;
			case \Itoris\ProductQa\Model\Questions::SORT_HELPFUL_ANSWERS:
				//$select_answers = ", sum(if(r_a.value ='1', 1, 0)) as helpful";
				//$join_answers_rating = "left join $this->tableAnswersRatings as r_a on a.id = r_a.a_id";
				$select_answers = ", (select sum(if(value = '1', 1, 0))-sum(if(value = '-1', 1, 0)) from `itoris_productqa_answers_ratings` where a_id in (select id from `itoris_productqa_answers` where q_id=e.id and status=5)) as helpful";
				$join_answers_rating = "";
				$order_by_answers = " helpful desc,";
				break;
		}
		$limit = '';
		if (is_null($includeQuestionId) && $this->getDataHelper()->getRegistry()->registry('page') > 1) {
			$limit = 'limit ' . ($this->getDataHelper()->getRegistry()->registry('page')-1) * $this->getDataHelper()->getRegistry()->registry('perPage') . ', ' .  $this->getDataHelper()->getRegistry()->registry('perPage');
		}
		$searchCondition = '';
		if ($searchQuery) {
			$queryParts = explode(' ', $searchQuery);
			$searchConditionParts = array();
			foreach ($queryParts as $queryPart) {
				$queryPart = trim($queryPart);
				if ($queryPart) {
					$searchConditionParts[] = "(e.content like " . $this->_connection->quote('%' . $queryPart . '%') . ")";
				}
			}
			if (!empty($searchConditionParts)) {
				$searchCondition .= " and (" . implode(' and ', $searchConditionParts);
				$answersQuestionIds = $this->_objectManager->create('Itoris\ProductQa\Model\Answers')->getQuestionIdsByQuery($searchQuery);
				if (!empty($answersQuestionIds)) {
					$answersQuestionIds = array_map('intval', $answersQuestionIds);
					$searchCondition .= " or e.id in (" . implode(',', $answersQuestionIds) . ")";
				}
				$searchCondition .= ')';
			}
		}
		$questions =  $this->_connection->fetchAll("SELECT SQL_CALC_FOUND_ROWS e.id, e.nickname, e.content, count(DISTINCT a.id) as answers,
							datediff(now(),e.created_datetime) as date,
							sum(if(r.value = '-1', 1, 0)) as bad,
							sum(if(r.value = '1', 1, 0)) as good
							$select_answers	FROM
							$this->tableQuestions  as e
							left join $this->tableAnswers  as a on e.id = a.q_id
							and a.status = " . \Itoris\ProductQa\Model\Answers::STATUS_APPROVED . "
							left join $this->tableQuestionsRatings as r on e.id = r.q_id
							inner join $this->tableQuestionsVisibility  as v on e.id = v.q_id
							$join_answers_rating
							where e.product_id = ". (int) $productId . " and v.store_id = $storeId
							and e.status = " . \Itoris\ProductQa\Model\Questions::STATUS_APPROVED . "
							{$searchCondition}
							group by e.id order by $order_by_answers $order
							$limit ;
		");
		$countQuestions = (int)$this->_connection->fetchOne("select FOUND_ROWS()");
		if (!is_null($includeQuestionId)) {
			$perPage = $this->getDataHelper()->getRegistry()->registry('perPage') ? $this->getDataHelper()->getRegistry()->registry('perPage') : 100;
			$pageQuestions = array();
			$hasQuestion = false;
			$pageNum = 1;
			for ($i = 0; $i < count($questions); $i++) {
				$pageQuestions[] = $questions[$i];
				if ($questions[$i]['id'] == $includeQuestionId) {
					$hasQuestion = true;
				}
				if (count($pageQuestions) == $perPage) {
					if (!$hasQuestion) {
						$pageQuestions = array();
					} else {
						break;
					}
					$pageNum++;
				}
			}
			if ($hasQuestion) {
				$questions = $pageQuestions;
			}
			$this->getDataHelper()->getRegistry()->unregister('page');

			$this->getDataHelper()->getRegistry()->register('page', $pageNum);
		}
		$questions = $this->correctRating($questions);
		if ($this->getDataHelper()->getRegistry()->registry('pages')) {
			$this->getDataHelper()->getRegistry()->unregister('pages');
		}
		if ($this->getDataHelper()->getRegistry()->registry('perPage')) {
			if(!$this->getDataHelper()->getRegistry()->registry('pages'))
			$this->getDataHelper()->getRegistry()->register('pages', ceil($countQuestions / $this->getDataHelper()->getRegistry()->registry('perPage')));
			$questions = array_slice($questions, 0, $this->getDataHelper()->getRegistry()->registry('perPage'));
		} else {
			if(!$this->getDataHelper()->getRegistry()->registry('pages'))
			$this->getDataHelper()->getRegistry()->register('pages',1);
		}

		return $questions;
	}

	public function getQuestionInfo($questionId) {
		$question = $this->_connection->fetchRow("SELECT e.*, p.value as product_name, group_concat(DISTINCT v.store_id) as visible,
													sum(if(r.value = '-1', 1, 0)) as bad, sum(if(r.value = '1', 1, 0)) as good
		  										from $this->tableQuestions as e
		  										left join $this->tableEavEntityType as eType
												on eType.entity_type_code = 'catalog_product'
												left join $this->tableEavAttribute as eAttr
												on eAttr.attribute_code = 'name' and eAttr.entity_type_id = eType.entity_type_id
												left join $this->tableCatalogProductEntityVarchar as p
												 on p.entity_id = e.product_id and p.attribute_id = eAttr.attribute_id
												left join $this->tableQuestionsVisibility as v
												 on v.q_id = e.id
												left join $this->tableQuestionsRatings as r
												 on e.id = r.q_id
												where e.id = $questionId
												group by e.id
		");
		$rows = count(explode(',', $question['visible']));
		if ($rows) {
			$question['good'] /= $rows;
			$question['bad'] /= $rows;
		}
		return $question;
	}

	/**
	 * Retrieve question visibility
	 *
	 * @param $questionId
	 * @return array
	 */
	public function getQuestionVisibility($questionId) {
		$visibility = $this->_connection->fetchAll("SELECT store_id from $this->tableQuestionsVisibility where q_id = $questionId");
		return $visibility;
	}

	/**
	 * Add a rating to a question
	 * Print a rating sum for the question
	 *
	 * @param $questionId
	 * @param $customerId
	 * @param $value
	 */
	public function addRating($questionId, $customerId, $value, $guestIp = null) {
		$questionId = (int)$questionId;
		if (is_null($guestIp)) {
			$customerId = (int)$customerId;
			$guestIp = 'null';
			$isExistsSql = "customer_id = {$customerId} and guest_ip is null";
		} else {
			$customerId =  'null';
			$guestIp = $this->_connection->quote($guestIp);
			$isExistsSql = "customer_id is null and guest_ip = {$guestIp}";
		}

		$value = $this->_connection->quote($value);
		try {
			$isExists = $this->_connection->fetchOne("select q_id from $this->tableQuestionsRatings where q_id = $questionId and {$isExistsSql}");
			if (!$isExists) {
				$this->_connection->query("insert into $this->tableQuestionsRatings (`customer_id`, `q_id`, `value`, `guest_ip`) values ($customerId, $questionId, $value, {$guestIp})");
			}
			return $this->_connection->fetchOne("select sum(if(value = $value, 1, 0)) from $this->tableQuestionsRatings where q_id = $questionId");
		} catch (\Exception $e) {

		}
	}

	/**
	 * Mark a question like inappropriate
	 *
	 * @param $questionId
	 */
	public function setInappr($questionId) {
		$questionId = (int)$questionId;
		$this->_connection->query("update $this->tableQuestions set `inappr` = 1 where id = $questionId");
	}

	/**
	 * Correct question rating.
	 * If question has answer the result for rating should be divided by the number of answers
	 *
	 * @param $questions
	 * @return mixed
	 */
	protected function correctRating($questions) {
		foreach ($questions as $key => $question) {
			if ($question['good'] && $question['answers']) {
				$questions[$key]['good'] = $question['good']/$question['answers'];
			}
			if ($question['bad'] && $question['answers']) {
				$questions[$key]['bad'] = $question['bad']/$question['answers'];
			}
		}
		return $questions;
	}

	/**
	 * Subscribe a user to newsletters
	 *
	 * @param $customerId
	 * @param $email
	 * @return string
	 */
	public function singUpNewsletter($customerId, $email) {
		$customerId = (int)$customerId;
        $customerSession    = $this->_objectManager->get('Magento\Customer\Model\Session');
        try {
			if (defined('Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG')) {
				if ($this->getDataHelper()->getScopeConfig()->getValue(\Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1
					&& !$customerSession->isLoggedIn()
				) {
					return __('Sorry, but administrator denied subscription for guests');
				}
			}
            if ($customerId) {
				$email = $this->_objectManager->create('Magento\Customer\Model\Customer')
                    ->load($customerId)
                    ->getEmail();
			} else {
				if( $this->_objectManager->create('Magento\Newsletter\Model\Subscriber')->loadByEmail($email)->getId()
					|| $this->_objectManager->create('Magento\Customer\Model\Customer')
                        ->setWebsiteId($this->getDataHelper()->getStoreManager()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId()
				) {
					return __('There was a problem with the subscription: This email address is already assigned to another user.');
				}
			}
			$this->_objectManager->create('Magento\Newsletter\Model\Subscriber')->subscribe($email);
			return __('Thank you for your subscription.');
        } catch (\Exception $e) {
			return __('There was a problem with the subscription.');
        }
	}

	public function sendNotifications($baseNotification) {
		$notifications = array();
			$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($this->getCustomerId());
			$type = $this->getEmail() ? \Itoris\ProductQa\Model\Notify::GUEST : \Itoris\ProductQa\Model\Notify::CUSTOMER;
			$notification = $baseNotification;
			$notification['question_details'] = $this->getContent();
			$notification['customer_name'] = $customer->getFirstname();
			$notification['customer_email'] = $type == \Itoris\ProductQa\Model\Notify::GUEST ? $this->getEmail() : $customer->getEmail();
			$notification['recipient_type'] = $type;
			$notifications[] = $notification;
		$subscribers = $this->_objectManager->create('Itoris\ProductQa\Model\ResourceModel\Question\Subscriber\Collection')
			->addFieldToFilter('question_id', array('eq' => $this->getId()));
		if (count($subscribers)) {
			foreach ($subscribers as $subscriber) {
				$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($subscriber->getCustomerId());
				$type = $subscriber->getCustomerId() ? \Itoris\ProductQa\Model\Notify::CUSTOMER : \Itoris\ProductQa\Model\Notify::GUEST;
				$notification = $baseNotification;
				$notification['question_details'] = $this->getContent();
				$notification['customer_name'] = $customer->getFirstname();
				$notification['customer_email'] = $type == \Itoris\ProductQa\Model\Notify::GUEST ? $subscriber->getEmail() : $customer->getEmail();
				$notification['recipient_type'] = \Itoris\ProductQa\Model\Notify::GUEST;
				$store = $this->getDataHelper()->getStoreManager()->getStore($subscriber->getStoreId());
				$notification['store_name'] = $store->getName();
				$notification['website_id'] = $store->getWebsiteId();
				$notification['store_id'] = $store->getId();
				$notification['load_settings'] = true;
				$notifications[] = $notification;
			}
		}
		$loadedSettings = array();
		foreach ($notifications as $_notification) {
			if (isset($_notification['load_settings']) && $_notification['load_settings']) {
				$key = 's' . $_notification['store_id'] . 'w' . $_notification['website_id'];
				if (!isset($loadedSettings[$key])) {
					$loadedSettings[$key] = $this->getDataHelper()->getSettings($_notification['store_id']);
				}
				$settings = $loadedSettings[$key];
			} else {
				$settings = null;
			}
			$notification = $this->_objectManager->create('Itoris\ProductQa\Model\Notify')->sendNotification($_notification, $_notification['recipient_type'], $settings);
		}

		return $this;
	}
}
