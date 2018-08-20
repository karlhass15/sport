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
namespace Itoris\ProductQa\Block;
class ProductQaAjax extends \Itoris\ProductQa\Block\ProductQa {
	protected $logger;
	public function logger(){
		if($this->logger)
			return $this->logger;
		return $this->logger=$this->getObjectManager()->create('Cm\RedisSession\Handler\LoggerInterface');
	}
	protected function _prepareLayout() {
	}

	/**
	 * Get questions html
	 *
	 * @param $productId
	 * @param $mode
	 * @return string
	 */
	public function getHtmlForQuestions($productId, $mode) {
		$questionId = $this->getRequest()->getParam('question_id', null);
		$this->setQA($productId, $mode, $questionId);
		return $this->getQuestionsHtml($this->getQuestions());
	}

	/**
	 * Get answers html
	 *
	 * @param $questionId
	 * @return string
	 */
	public function getHtmlForAnswers($questionId) {
		$answers = $this->getObjectManager()->get('Itoris\ProductQa\Model\Answers');
		if (!$answers) {
			$answers = $this->getObjectManager()->create('Itoris\ProductQa\Model\Answers');
		}
		$answers = $answers->getAnswers($questionId);
		return $this->getAnswersHtml($answers);

	}

}
