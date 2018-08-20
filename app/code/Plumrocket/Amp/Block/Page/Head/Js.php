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

class Js extends \Magento\Framework\View\Element\Template
{
	/**
	 * Array of scripts
	 * @var array
	 */
	protected $_js = [];

	/**
	 * String of scripts prepared  for output
	 * @var string
	 */
    protected $_html;

    /**
     * Return list of used scripts
     * @var void
     * @return array
     */
    public function getAmpScripts()
    {
        return array_keys($this->_js);
    }

    /**
     * Add new amp js
     * @param string $src
     * @param string $type
     */
	public function addJs($src, $type, $element = null) {
        $type = trim((string)$type);
        $this->_js[$type]['src'] = $src;
        $this->_js[$type]['element'] = $element ? $element : 'element';
        return $this;
	}

    public function removeJs($type)
    {
        if (array_key_exists($type, $this->_js)) {
            unset($this->_js[$type]);
        }

        return $this;
    }

    /**
     * Retrieve prepared string of scripts
     * @return string
     */
    protected function _toHtml()
    {
        $this->_html = '';
        foreach ($this->_js as $type => $data) {
            $this->_html .= '<script async ' . ($type ? 'custom-' . $data['element'] . '="' . $type . '"' : '') . ' src="' . $data['src'] . '"></script>';
        }

        $this->_html .= '<script async src="https://cdn.ampproject.org/v0.js"></script>';
        return $this->_html;
    }

}