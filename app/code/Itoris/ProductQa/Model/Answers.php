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
class Answers extends \Magento\Framework\Model\AbstractModel{

	/** @var $_resource */
	protected $_resource;
	private $tableAnswers = 'itoris_productqa_answers';
	private $tableAnswersRatings = 'itoris_productqa_answers_ratings';
	protected $_objectManager;
	protected $_connection;
	const SUBMITTER_ADMIN = 1;
	const SUBMITTER_CUSTOMER = 2;
	const SUBMITTER_VISITOR = 3;
	const STATUS_PENDING = 4;
	const STATUS_APPROVED = 5;
	const STATUS_NOT_APPROVED = 6;
	protected $_helper;
	public function _construct() {
		$this->_init('Itoris\ProductQa\Model\ResourceModel\Answers');
		$this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
		$this->_resource = $this->getResourceConnection();
		$this->_connection = $this->_resource->getConnection();
		$this->tableAnswers = $this->_resource->getTable($this->tableAnswers);
		$this->tableAnswersRatings = $this->_resource->getTable($this->tableAnswersRatings);
	}
	public function getResourceConnection(){
		return $this->getResource();
	}
	/**
	 * Save answer
	 *
	 * @param $data
	 * @return string
	 */
	public function addAnswer($data) {
		$dataObj = new \Magento\Framework\DataObject($data);
		$status = (int)$dataObj->getStatus();
		$submitter_type = (int)$dataObj->getSubmitterType();
		$nickname = $this->_connection->quote($dataObj->getNickname());
		$content = $this->_connection->quote($dataObj->getContent());
		$customer_id = (int)$dataObj->getCustomerId();
		$q_id = (int)$dataObj->getQId();
		$newsletter = (int)$dataObj->getNewsletter();
		$this->_connection->query("INSERT into $this->tableAnswers (`status`, `submitter_type`,
								`q_id`, `nickname`, `content`, `customer_id`) VALUES
								($status, $submitter_type, $q_id, $nickname, $content, $customer_id)");
		$output = '';
		if ($newsletter) {
			/** @var $questionModel \Itoris\ProductQa\Model\Questions */
			$questionModel = $this->_objectManager->create('Itoris\ProductQa\Model\Questions');
			$output = $questionModel->singUpNewsletter($customer_id, $dataObj->getNewsletterEmail());
		}
		return $output;
	}

	/**
	 * Retrieve answers for question(s)
	 *
	 * @param $questionsIds
	 * @return array
	 */
	public function getAnswers($questionsIds) {

		if (is_array($questionsIds)) {
			foreach ($questionsIds as $question) {
				$questions[] = (int)$question;
			}
			$questions = implode(',', $questions);
		} else {
			$questions = (int)$questionsIds;
		}
		return $this->_connection->fetchAll("SELECT e.id,e.q_id, e.nickname, e.content,
							datediff(now(),e.created_datetime) as date,
							sum(if(r.value = '-1', 1, 0)) as bad,
							sum(if(r.value = '1', 1, 0)) as good	FROM
							$this->tableAnswers  as e
							left join $this->tableAnswersRatings  as r on e.id = r.a_id
							where e.status = ". \Itoris\ProductQa\Model\Answers::STATUS_APPROVED ." and
 							 e.q_id IN($questions)
							group by e.id order by e.created_datetime desc");
	}

	public function getQuestionIdsByQuery($query) {

		$queryParts = explode(' ', $query);
		$searchConditionParts = array();
		foreach ($queryParts as $queryPart) {
			$queryPart = trim($queryPart);
			if ($queryPart) {
				$searchConditionParts[] = "(content like " . $this->_connection->quote('%' . $queryPart . '%') . ")";
			}
		}
		if (empty($searchConditionParts)) {
			return array();
		} else {
			$query = implode(' and ', $searchConditionParts);
			return $this->_connection->fetchCol("select distinct q_id from {$this->tableAnswers} where {$query} and status = ". \Itoris\ProductQa\Model\Answers::STATUS_APPROVED ."");
		}
	}

	/**
	 * Add a rating to an answer
	 * Print a rating sum for the answer
	 *
	 * @param $answerId
	 * @param $customerId
	 * @param $value
	 */
	public function addRating($answerId, $customerId, $value, $guestIp = null) {
		$answerId = (int)$answerId;
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
			$isExists = $this->_connection->fetchOne("select a_id from $this->tableAnswersRatings where a_id = $answerId and {$isExistsSql}");
			if (!$isExists) {
				$this->_connection->query("insert into $this->tableAnswersRatings (`customer_id`, `a_id`, `value`, `guest_ip`) values ($customerId, $answerId, {$value}, {$guestIp})");
			}
			return $this->_connection->fetchOne("select sum(if(value = $value, 1, 0)) from $this->tableAnswersRatings where a_id = $answerId");
		} catch(\Exception $e) {}
	}

	/**
	 * Mark answer like inappropriate
	 *
	 * @param $answerId
	 */
	public function setInappr($answerId){
		$answerId = (int)$answerId;
		$this->_connection->query("update $this->tableAnswers set `inappr` = 1 where id = $answerId");
	}
}
