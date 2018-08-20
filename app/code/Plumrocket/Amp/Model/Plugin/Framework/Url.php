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
 * @package     Plumrocket Amp v2.x.x
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Amp\Model\Plugin\Framework;

use Magento\Framework\UrlInterface;

/**
 * Plugin for processing builtin cache
 */
class Url
{
    /**
     * @var \Plumrocket\Amp\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Plumrocket\Amp\Helper\Data $dataHelper
     */
    public function __construct(
        \Plumrocket\Amp\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Add amp parameter for each url
     * @param  \Magento\Framework\UrlInterface $subject
     * @param  string
     * @return string
     */
    public function afterGetUrl(UrlInterface $subject, $url)
    {
        if ($this->_dataHelper->isAmpRequest()){
            return $this->_dataHelper->getAmpUrl($url);
        }

        return $url;
    }

}