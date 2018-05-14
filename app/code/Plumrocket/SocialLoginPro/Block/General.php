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
 * @package     Plumrocket_SocialLoginPro
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\SocialLoginPro\Block;

class General extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Plumrocket\SocialLoginPro\Helper\Data
     */
    protected $dataHelper;

    /**
     * General constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Plumrocket\SocialLoginPro\Helper\Data            $dataHelper
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\SocialLoginPro\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->dataHelper->moduleEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getSkipModules()
    {
        $skipModules = $this->dataHelper->getRefererLinkSkipModules();
        return json_encode($skipModules);
    }
}
