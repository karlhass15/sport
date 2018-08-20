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

namespace Plumrocket\Estimateddelivery\Observer;

class AdminhtmlCategoryTabsObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected $_helper;

    public function __construct(
        \Plumrocket\Estimateddelivery\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->moduleEnabled()) {
            return;
        }

        $tabs = $observer->getEvent()->getTabs();
        $attributeSetId = $tabs->getCategory()->getDefaultAttributeSetId();
        $group = $this->_helper->getGroup($attributeSetId);

        if ($group && $group->getId()) {
            $deliveryAttributes = [];
            $shippingAttributes = [];

            $categoryAttributes = $tabs->getCategory()->getAttributes();
            foreach ($categoryAttributes as $attribute) {
                if ($attribute->isInGroup($attributeSetId, $group->getId())) {
                    if (strpos($attribute->getAttributeCode(), 'estimated_delivery') !== false) {
                        $deliveryAttributes[] = $attribute;
                    } else {
                        $shippingAttributes[] = $attribute;
                    }
                }
            }

            $html = $tabs->getLayout()->createBlock($tabs->getAttributeTabBlock(), '')
                ->setGroup($this->_helper->makeDeliveryGroup($group))
                ->setAttributes($deliveryAttributes)
                ->setAddHiddenFields(false)
                ->toHtml();

            $html .= $tabs->getLayout()->createBlock($tabs->getAttributeTabBlock(), '')
                ->setGroup($this->_helper->makeShippingGroup($group))
                ->setAttributes($shippingAttributes)
                ->setAddHiddenFields(false)
                ->toHtml();

            // $html .= '<script type="text/javascript">refreshEstimatedData();</script>';

            $tabs->setTabData('group_' . $group->getId(), 'content', $html);
        }
    }
}
