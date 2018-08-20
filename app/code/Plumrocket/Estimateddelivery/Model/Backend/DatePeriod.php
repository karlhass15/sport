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
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Estimateddelivery\Model\Backend;

class DatePeriod extends \Magento\Framework\App\Config\Value
{
    protected $helperData;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Plumrocket\Estimateddelivery\Helper\Data $helperData,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function afterLoad()
    {
        if ($value = json_decode($this->getValue(), true)) {
            $this->setValue($value);
        }
        parent::afterLoad();
    }
 
    public function beforeSave()
    {
        if ($value = $this->getValue()) {
            if (is_array($value)) {
                array_walk_recursive($value, ['self', '_convertDate']);
            }
        }
        $this->setValue(json_encode($value));
        parent::beforeSave();
    }

    protected function _convertDate(&$val, $key)
    {
        if (is_string($val)
            && strlen($val) == strlen($this->helperData->getDateTimeFormat())
            && false !== strpos($val, '-')
        ) {
            list($month, $day, $year) = explode('-', trim($val), 3);
            if (checkdate($month, $day, $year)) {
                $val = strtotime("$year-$month-$day");
            }
        }
    }
}
