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
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Amp\Block\Page;

class Share extends \Magento\Framework\View\Element\Template
{
    /**
     * List of allowed buttons
     * @var array
     */
    protected $_allowedShareButtons;

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
     * Retrieve allowed social buttons
     * @param  int $store
     * @return array
     */
    public function getActiveShareButtons()
    {
        if ($this->_allowedShareButtons === null) {
            $this->_allowedShareButtons = [];

            if (!$this->_dataHelper->getSocialSharingEnabled()) {
                return $this->_allowedShareButtons;
            }

            $shareButtons = explode(',', $this->_dataHelper->getActiveShareButtons());

            if (is_array($shareButtons) && count($shareButtons)) {
                foreach ($shareButtons as $type) {
                    $params = [];

                    $methodName = 'get' . ucfirst($type) . 'ButtonParams';
                    if (method_exists($this, $methodName)) {
                        $params = array_merge($params, $this->{$methodName}());
                    }

                    $isValidParams = true;
                    foreach ($params as $param => $paramData) {
                        if (isset($paramData['require']) && $paramData['require'] && !$paramData['value']) {
                            $isValidParams = false;
                            break;
                        }
                    }

                    if ($isValidParams) {
                        $this->_allowedShareButtons[$type] = $params;
                    }
                }
            }
        }

        return $this->_allowedShareButtons;
    }

    /**
     * Retrieve additional params for email button
     * @param void
     * @return array
     */
    public function getEmailButtonParams()
    {
        return array(
            'data-param-body' => array(
                'value' => $this->_dataHelper->getAmpUrl(),
                'require' => false,
            ),
        );
    }

    /**
     * Retrieve additional params for email button
     * @param void
     * @return array
     */
    public function getFacebookButtonParams()
    {
        return array(
            'data-param-app_id' => array(
                'value' => $this->_dataHelper->getShareButtonFacebookAppID(),
                'require' => true,
            ),
        );
    }
}
