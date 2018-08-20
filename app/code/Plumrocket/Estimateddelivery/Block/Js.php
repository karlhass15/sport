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

namespace Plumrocket\Estimateddelivery\Block;

use Plumrocket\Estimateddelivery\Helper\Data;

/**
 * Estimated delivery load js
 */
class Js extends Product
{
    /**
     * @var string
     */
    protected $deliverySection;

    /**
     * Retrieve json config string
     * @param array $config
     *
     * @return string
     */
    public function getConfig(array $config = [])
    {
        $this->deliverySection = Data::SECTION_ID . '/';
        $_config = $this->_productHelper->getSourceData(true);
        $config = array_merge($_config, $config);

        $config['dateFormat'] = $this->_productHelper->getConfig(
            $this->deliverySection . 'general' . '/date_format'
        );

        foreach (['delivery', 'shipping'] as $type) {
            $config[$type]['cutoftime'] = $this->getCutOfTime($type);
        }

        return json_encode($config);
    }

    private function getCutOfTime($type)
    {
       if ($this->_productHelper->getConfig($this->deliverySection . $type . '/time_after_enable')) {
            return $this->_productHelper->getConfig(
                $this->deliverySection . $type . '/time_after'
            );
       }

       return false;
    }


    public function getJsLayout()
    {
        if ($this->jsLayout) {
            $config = &$this->jsLayout['components']['edelivery-bind']['config'];
            $config['url'] = $this->getUrl('prestimateddelivery/ajax/time');
        }

        return parent::getJsLayout();
    }
}
