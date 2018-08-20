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

namespace Plumrocket\Amp\Model\Plugin\Framework\Event;

use Magento\Framework\Event\ConfigInterface as ConfigInterface;

/**
 * Plugin for processing builtin cache
 */
class ConfigInterfacePlugin
{
    /**
     * List of observers that need to be disabled
     * @var array
     */
    protected $_disabledObservers;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->_disabledObservers = [
            'Mageplaza\Seo\Observer\GenerateBlocksAfterObserver',
            'Mageplaza\Seo\Observer\Markup',
            'Mageplaza\Seo\Observer\MetaCmsObserver',
            'Mageplaza\Seo\Observer\StoreForm',
            'Mirasvit\Seo\Observer\Snippet',
            'MageWorx\SeoExtended\Observer\ModifyMeta',
        ];
    }

    /**
     * Check of observer instance by list of disabled observers
     * @param  string $instance
     * @return boolean
     */
    protected function _isAllowedObserver($instance)
    {
        if ($instance) {
            return !in_array($instance, $this->_disabledObservers);
        }

        return true;
    }

    /**
     * Add amp parameter for each url
     * @param  ConfigInterface $subject
     * @param  array $result
     * @return array
     */
    public function afterGetObservers(ConfigInterface $subject, $result)
    {
        if (PHP_SAPI === 'cli' || !count($result)) {
            return $result;
        }

        //Need to use object manager to omit issues during setup:static-content:deploy
        $dataHelper = $this->objectManager->get('\Plumrocket\Amp\Helper\Data'); 

        if ($dataHelper->isAmpRequest()){
            foreach ($result as $key => $item) {
                if (isset($item['instance']) && !$this->_isAllowedObserver($item['instance'])) {
                    $result[$key]['disabled'] = true;
                }
            }
        }

        return $result;
    }

}
