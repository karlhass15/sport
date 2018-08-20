<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Estimateddelivery
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Estimateddelivery\Controller\Ajax;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\Estimateddelivery\Helper\Data;

/**
 * Estimateddelivery Time
 */
class Time extends \Magento\Framework\App\Action\Action
{
    /**
     * DateTime
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->date = $date;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($this->date->gmtDate(Data::DATE_FORMAT));
    }
}
