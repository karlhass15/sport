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
 * @package     Plumrocket_Amp 2.x.x
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Amp\Block\Page\Head;

class Style extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Magento\Framework\View\Element\Template\Context $context
	 * @param \Plumrocket\Amp\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\Amp\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Retrieve view url without cdn url
     * @param  string $file
     * @param  array  $params
     * @return string
     */
    public function getAmpSkinUrl($file = null, array $params = array())
    {
        $url = $this->getViewFileUrl($file, $params);
        $fontInfo = parse_url($url);
        $baseInfo = parse_url($this->getUrl());
        $url = str_replace($fontInfo['host'], $baseInfo['host'], $url);

        return $url;
    }

    /**
     * Retrieve minified css
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($html) {
            $html = str_replace(
                array(' {', "}\n"),
                array('{', '}'),
                $html
            );
        }

        return $html;
    }

    /**
     * Get max-width and max-height for category image
     * @return array
     */
    public function getCategoryImageSize()
    {
        $categoryImageBlock = $this->getLayout()->getBlock('amp.category.image');

        return [
            'max-width' => $categoryImageBlock ? $categoryImageBlock->getWidth() : 'none',
            'max-height' => $categoryImageBlock ? $categoryImageBlock->getHeight() : 'none',
        ];
    }

    /**
     * Retrieve setting parameter from helper
     * @param void
     * @return boolean
     */
    public function isRtlEnabled()
    {
        return $this->_dataHelper->getRtlEnabled();
    }
}
