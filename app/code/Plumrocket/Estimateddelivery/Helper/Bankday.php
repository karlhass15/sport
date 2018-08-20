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

namespace Plumrocket\Estimateddelivery\Helper;

class Bankday extends Data
{
    /**
     * @var array
     */
    protected $holidays = [];

    /**
     * @var array
     */
    protected $weekends = [0, 6];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Plumrocket\Estimateddelivery\Model\OrderItem
     */
    protected $orderItemFactory;

    /**
     * Bankday constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface                          $objectManager
     * @param \Magento\Framework\App\Helper\Context                              $context
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $attributeGroupCollection
     * @param \Magento\Framework\App\ResourceConnection                          $resourceConnection
     * @param \Magento\Config\Model\Config                                       $config
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                        $dateTime
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $attributeGroupCollection,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Config\Model\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Plumrocket\Estimateddelivery\Model\OrderItemFactory $orderItemFactory
    ) {
        parent::__construct(
            $objectManager,
            $productMetadata,
            $context,
            $attributeGroupCollection,
            $resourceConnection,
            $orderItemFactory,
            $config
        );
        $this->dateTime = $dateTime;
    }

    public function loadHolidays($type)
    {
        $this->holidays = [];

        if (!$items = json_decode($this->getConfig($this->_configSectionId . '/' . $type . '/holidays'), true)) {
            $items = [];
        }

        foreach ($items as $item) {
            switch ($item['date_type']) {
                case 'recurring_date':
                    if (!empty($item[ $item['date_type'] ]['month']) && !empty($item[ $item['date_type'] ]['day'])) {
                        $this->holidays[] = str_pad($item[ $item['date_type'] ]['month'], 2, '0', STR_PAD_LEFT)
                            .'-'. str_pad($item[ $item['date_type'] ]['day'], 2, '0', STR_PAD_LEFT);
                    }
                    break;

                case 'single_day':
                    if (!empty($item[ $item['date_type'] ])) {
                        $this->holidays[] = date('m-d-Y', $item[ $item['date_type'] ]);
                    }
                    break;

                case 'period':
                    if (!empty($item[ $item['date_type'] ]['start']) && !empty($item[ $item['date_type'] ]['end'])) {
                        $startTs = $item[ $item['date_type'] ]['start'];
                        $endTs = $item[ $item['date_type'] ]['end'];
                        $end = date('m-d-Y', $endTs);

                        $this->holidays[] = date('m-d-Y', $startTs);
                        if ($startTs < $endTs) {
                            for ($n = 1; $n <= 50; $n++) {
                                $date = date('m-d-Y', strtotime("+{$n} days", $startTs));
                                $this->holidays[] = $date;
                                if ($date == $end) {
                                    break;
                                }
                            }
                        }
                    }
                    break;
            }
        }

        // load weekends
        $config = $this->getConfig($this->_configSectionId . '/' . $type . '/weekend');
        $this->weekends = explode(',', $config);
    }

    // Prepare date
    public function prepareDate($s)
    {
        if ($s !== null && !is_int($s)) {
            $ts = strtotime($s);
            if ($ts === -1 || $ts === false) {
                throw new \Exception('Unable to parse date/time value from input: '.var_export($s, true));
            }
        } else {
            $ts = $s;
        }
        return $ts;
    }

    public function isWeekend($date)
    {
        $ts = $this->prepareDate($date);
        return in_array(date('w', $ts), $this->weekends);
    }

    public function isHoliday($date)
    {
        $ts = $this->prepareDate($date);
        return in_array(date('m-d-y', $ts), $this->holidays)
            || in_array(date('m-d-Y', $ts), $this->holidays)
            || in_array(date('m-d', $ts), $this->holidays);
    }

    // Get weekends with holidays
    public function getHolidays($date, $interval = 60)
    {
        $ts = $this->prepareDate($date);
        $holidays = [];

        for ($i = -$interval; $i <= $interval; $i++) {
            $curr = strtotime($i.' days', $ts);

            if ($this->isWeekend($curr) || $this->isHoliday($curr)) {
                $holidays[] = date('Y-m-d', $curr);
            }
        }

        // move holidays to next work day
        /*
        foreach ($holidays as $date) {
            $ts = $this->prepareDate($date);
            if ($this->isHoliday($ts) && $this->isWeekend($ts)) {
                $i = 0;
                while (in_array(date('Y-m-d', strtotime($i.' days', $ts)), $holidays)) {
                    $i++;
                }
                $holidays[] = date('Y-m-d', strtotime($i.' days', $ts));
            }
        }*/

        return $holidays;
    }


    // get date + n bank days
    public function getEndDate($type, $start, $days, $format = null, $returnDays = false)
    {
        $this->loadHolidays($type);

        $ts = $this->prepareDate($start);
        $holidays = $this->getHolidays($start);

        if ($this->getConfig($this->_configSectionId . '/' . $type . '/time_after_enable')) {
            // $timeAfter = $this->getConfig($this->_configSectionId . '/' . $type . '/time_after');
            // $timeAfter = strtotime( date('Y-m-d', $ts) .' '. str_replace(',', ':', $timeAfter) .':00' );
            // If $ts is time by gmt, $hour set admin by his local time, that $hour need to convert to hour by gmt.
            $currentTs = $this->dateTime->timestamp();
            list($hour, $minute) = explode(',', $this->getConfig($this->_configSectionId . '/' . $type . '/time_after'));
            if ((date('H', $ts) > $hour || (date('H', $ts) == $hour && date('i', $ts) > $minute))
                && date('Y-m-d', $currentTs) == date('Y-m-d', $ts)
            ) {
                $holidays[] = date('Y-m-d', $ts);
            }
        }

        for ($i = 0; $i <= $days; $i++) {
            $curr = strtotime('+'.$i.' days', $ts);
            if (in_array(date('Y-m-d', $curr), $holidays)) {
                $days++;
            }
        }

        if ($returnDays) {
            return $days;
        }

        if ($format) {
            return date($format, strtotime('+'.$days.' days', $ts));
        } else {
            return strtotime('+'.$days.' days', $ts);
        }
    }
}
