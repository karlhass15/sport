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

namespace Plumrocket\Estimateddelivery\Helper;

class Data extends Main
{
    const SECTION_ID = 'estimateddelivery';

    const DATE_FORMAT = 'm/d/Y H:i:s';

    /**
     * @var string
     */
    protected $_configSectionId = 'estimateddelivery';

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    protected $attributeGroupCollection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Plumrocket\Estimateddelivery\Model\OrderItem
     */
    protected $orderItemFactory;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface                          $objectManager
     * @param \Magento\Framework\App\Helper\Context                              $context
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $attributeGroupCollection
     * @param \Magento\Framework\App\ResourceConnection                          $resourceConnection
     * @param \Magento\Config\Model\Config                                       $config
     * @param \Magento\Framework\App\ProductMetadataInterface                    $productMetadata
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $attributeGroupCollection,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Plumrocket\Estimateddelivery\Model\OrderItemFactory $orderItemFactory,
        \Magento\Config\Model\Config $config
    ) {
        parent::__construct($objectManager, $context);
        $this->productMetadata = $productMetadata;
        $this->orderItemFactory = $orderItemFactory;
        $this->attributeGroupCollection = $attributeGroupCollection;
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
    }

    public function moduleEnabled($store = null)
    {
        return (bool)$this->getConfig($this->_configSectionId.'/general/enable', $store);
    }

    public function makeDeliveryGroup($group)
    {
        $deliveryGroup = new \Magento\Framework\DataObject(['data' => $group->getData()]);
        $deliveryGroup->setAttributeGroupName(__('Estimated Delivery Date'));
        return $deliveryGroup;
    }

    public function makeShippingGroup($group)
    {
        $shippingGroup = new \Magento\Framework\DataObject(['data' => $group->getData()]);
        $shippingGroup->setAttributeGroupName(__('Estimated Shipping Date'));
        return $shippingGroup;
    }

    public function getGroup($setId)
    {
        $groupName = $this->getGroupName();

        return $this->attributeGroupCollection
            ->setAttributeSetFilter($setId)
            ->addFilter('attribute_group_name', $groupName)
            ->setSortOrder()
            ->load()
            ->getFirstItem();
    }

    public function getGroupName()
    {
        return 'Estimated Delivery/Shipping';
    }

    public function showPosition($position)
    {
        $positions = explode(',', $this->getConfig($this->_configSectionId.'/general/position'));
        if (in_array($position, $positions)) {
            return true;
        }
    }

    public function disableExtension()
    {
        $connection = $this->resourceConnection->getConnection('core_write');
        $connection->delete(
            $this->resourceConnection->getTableName('core_config_data'),
            [$connection->quoteInto('path = ?', $this->_configSectionId.'/general/enable')]
        );

        $this->config->setDataByPath($this->_configSectionId.'/general/enable', 0);
        $this->config->save();
    }

    public function moduleCheckoutspageEnabled()
    {
        return (bool)$this->moduleExists('Checkoutspage');
    }

    public function getDateTimeFormat()
    {
        // return 'M/d/yyyy H:mm';
        return 'MM-dd-yyyy';
    }

    public function formattingDate($originalValue, $date) 
    {
        if (!$originalValue && $date) {
            return date(self::DATE_FORMAT, strtotime($date));
        }
        return $date;
    }

    public function saveOptions($item, $options)
    {
        $shipping = !empty($options['shipping']['value']) ? $options['shipping']['value'] : null;
        $delivery = !empty($options['delivery']['value']) ? $options['delivery']['value'] : null;

        if ($item->getId()) {
            $this->orderItemFactory->create()
                ->setItemId($item->getId())
                ->setShipping($shipping)
                ->setDelivery($delivery)
                ->save();
        }

        return $this;
    }

    public function serialize($data)
    {
        if ($this->versionCheck()) {
            return serialize($data);
        }
        return json_encode($data);
    }

    public function unserialize($data)
    {
        if ($this->versionCheck()) {
            return unserialize($data);
        }
        return json_decode($data);
    }

    private function versionCheck()
    {
        return version_compare($this->productMetadata->getVersion(), "2.2.0", "<");
    }

}
