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

namespace Plumrocket\Estimateddelivery\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductCategory extends \Magento\Framework\Model\AbstractModel
{
    const INHERITED = 0;
    const DISABLED = 1;
    const DYNAMIC_DATE = 2;
    const DYNAMIC_RANGE = 3;
    const STATIC_DATE = 4;
    const STATIC_RANGE = 5;
    const TEXT = 6;

    protected $_result = null;
    protected $_dateEnd = '';

    protected $_helper = null;
    protected $_bankday = null;
    protected $_productModel = null;
    protected $_categoryModel = null;

    /**
      * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|null
      */
    protected $timezone = null;

    /**
      * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
      */
    protected $configurableType;

    protected $_product = null;
    protected $_orderItem = null;
    protected $_category = null;
    protected $bankday;
    protected $bankdayRange;

    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
        \Plumrocket\Estimateddelivery\Helper\Data $helper,
        \Plumrocket\Estimateddelivery\Helper\Bankday $bankday,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Catalog\Model\CategoryFactory $categoryModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->configurableType = $configurableType;
        $this->_helper = $helper;
        $this->_bankday = $bankday;
        $this->_productModel = $productModel;
        $this->_categoryModel = $categoryModel;
        $this->_categoryModel = $categoryModel;
        $this->timezone = $timezone;

    }

    public function getProduct()
    {
        if (null === $this->_product) {
            $this->_product = $this->_registry->registry('product');

            if (!$this->_product || !$this->_product->getId()) {
                $this->_product = $this->_productModel->create();
            }
        }
        return $this->_product;
    }

    public function getCategory()
    {
        if (null === $this->_category) {
            $this->_category = $this->_registry->registry('current_category');

            if (!$this->_category || !$this->_category->getId()) {
                $this->_category = $this->_categoryModel->create();
            }
        }
        return $this->_category;
    }

    public function setProduct($product, $orderItem = null)
    {
        $this->reset();

        if(null === $product->getData('estimated_delivery_enable') && null === $product->getData('estimated_shipping_enable') && $product->getId()) {
            $product = $this->_productModel->create()->load($product->getId());
        }

        if (null !== $orderItem) {
            $this->_orderItem = $orderItem;
        }

        $this->_product = $product;
    }

    public function setCategory($category)
    {
        $this->reset();
        $this->_category = $category;
    }

    public function reset()
    {
        $this->_result = null;
        $this->_dateEnd = '';
        $this->_category = null;
        $this->_product = null;
        $this->_orderItem = null;
    }

    public function getSourceData($categoryProductPages = false)
    {
        if (!$this->_result || $categoryProductPages == true) {
            $this->_result = [];

            if ($this->_helper->moduleEnabled()) {
                $this->_result = [
                    'delivery' => $this->_getData('delivery', $categoryProductPages),
                    'shipping' => $this->_getData('shipping', $categoryProductPages)
                ];
            }
        }

        return $this->_result;
    }

    public function formatDate($value, $type, $start = null, $isRange = false)
    {
        if (null === $start) {
            $start = $this->timezone->date()->getTimestamp() + $this->timezone->date()->getOffset();
        }

        $days = $this->_bankday->getEndDate($type, $start, (int)$value, null, true);

        if ($type == "delivery") {
           $this->bankday['delivery'] = $days;
        } else if ($type == "shipping") {
           $this->bankday['shipping'] = $days;
        }

        if ($isRange) {
            if ($type == "delivery") {
               $this->bankdayRange['delivery'] = $days;
            } else if ($type == "shipping") {
               $this->bankdayRange['shipping'] = $days;
            }
        }

        return strftime(
            '%Y-%m-%d %H:%M:%S',
            $this->_bankday->getEndDate($type, $start, (int)$value)
        );
    }

    public function getBankDays($type = false)
    {
        if ($type && isset($this->bankday[$type])) {
            return $this->bankday[$type];
        }
        return $this->bankday;
    }

    public function getBankDaysRange($type = false)
    {
        if ($type && isset($this->bankdayRange[$type])) {
            return $this->bankdayRange[$type];
        }
        return $this->bankdayRange;
    }

    public function formatDates($data, $type, $start = null)
    {
        if (!isset($data['enable'])) {
            return $data;
        }

        switch ($data['enable']) {
            case self::DYNAMIC_RANGE:
                $data['to'] = $this->formatDate($data['to_origin'], $type, $start, true);
                // no break
            case self::DYNAMIC_DATE:
                $data['from'] = $this->formatDate($data['from_origin'], $type, $start);
                break;
        }

        return $data;
    }

    // ---- Private functions
    protected function _getData($type, $categoryProductPages = false)
    {
        $result = array();
        $product = $this->getProduct();
        if ($product && $product->getId()) {
            if ($categoryProductPages) {
                if ($product->getTypeId() == Configurable::TYPE_CODE) {
                    $result[$product->getId()] = $this->_getDataFromProduct($product, $type);
                    $result[$product->getId() . "_bank"] = $this->getBankDays($type);
                    $result[$product->getId() . "_bank_range"] = $this->getBankDaysRange($type);
                    foreach ($product->getTypeInstance()->getUsedProducts($product) as $simpleproduct) {
                        $simpleProductResult = $this->_getDataFromProduct(
                            $this->_productModel->create()->load($simpleproduct->getId()),
                            $type,
                            true,
                            $product
                        );

                        if (
                           $result[$product->getId()] != $simpleProductResult || $simpleProductResult == false
                        ) {
                            $result[$simpleproduct->getId()] = $simpleProductResult;
                            $result[$simpleproduct->getId() . "_bank"] = $this->getBankDays($type);
                            $result[$simpleproduct->getId() . "_bank_range"] = $this->getBankDaysRange($type);
                        }

                    }
                } else {
                    $result[$product->getId()] = $this->_getDataFromProduct($product, $type, true);
                    $result[$product->getId() . "_bank" ] = $this->getBankDays($type);
                    $result[$product->getId() . "_bank_range"] = $this->getBankDaysRange($type);
                }
            } else {
                $result = $this->_getDataFromProduct($product, $type);
            }

        } else {
            $category = $this->getCategory();

            if ($categoryProductPages) {
                $result[$category->getId()] = $this->_getDataFromCategory($category, $type);
                $result[$category->getId() . "_bank"] = $this->getBankDays($type);
                $result[$category->getId() . "_bank_range"] = $this->getBankDaysRange($type);
            } else {
                $result = $this->_getDataFromCategory($category, $type);
            }

        }

        return $result;
    }

    private function getParentEstimated($type, $product)
    {
          $parentProductEstimate = $this->_parseData($product, $type);
          if ($parentProductEstimate !== null) {
              return $parentProductEstimate;
          }
    }

    protected function _getDataFromProduct($product, $type, $originalValue = false, $parentPoduct = false)
    {
        $result = self::INHERITED;
        $enableType = $this->_value($product, $type, 'enable');
        $result = $this->_parseData($product, $type);

        if ($enableType != self::INHERITED && $result !== null) {
            return $result;
        } else {

            if ($enableType == self::INHERITED && $parentPoduct && $parentPoduct->getId()) {
                 return $this->getParentEstimated($type, $parentPoduct);
            } else if (
                $parentPoductId = $this->configurableType->getParentIdsByChild($product->getId())
            ) {
                if (isset($parentPoductId[0])) {
                    return $this->getParentEstimated(
                          $type, $this->_productModel->create()->load($parentPoductId[0])
                    );
                }
            }

            // scan categories
            if ($this->_registry->registry('current_category')) {
                $cIds = [$this->_registry->registry('current_category')->getId()];
            } else {
                $cIds = $product->getCategoryIds();
            }

            if ($cIds) {
                // foreach by all parents' categories of product and check if any parent set or him parents
                foreach ($cIds as $cid) {

                    $cat = $this->_categoryModel->create()->load($cid);
                    $res = $this->_getDataFromCategory($cat, $type);

                    // if at least parent is enabled then product is enabled
                    // else return will be 0 - inherited or False - disable
                    if ($res) {
                        $result = $res;
                        break;
                    }
                    // If at end all parents will be inherited exept one or each disabled
                    // then product will be disabled
                    if ($res === false) {
                        $result = false;
                    }
                }
            }
        }
        return $result;
    }

    protected function _getDataFromCategory($cat, $type)
    {
        $result = self::INHERITED;
        $parentIds = $cat->getParentIds();

        do {
            if ($cat && $cat->getId() && $cat->getIsActive()) {
                if (
                    $this->_value($cat, $type, 'enable') != self::INHERITED &&
                    ($result = $this->_parseData($cat, $type)) !== null
                ) {
                    $result = $this->_parseData($cat, $type);
                    break;
                }
            }

            $pid = array_pop($parentIds);
            if ($pid) {
                $cat = $this->_categoryModel->create()->load($pid);
            }
        } while ($pid);

        return $result;
    }

    private function  useDefaultText($type, $result, $field)
    {
        if(false !== $result && is_array($result)) {
            if ($this->_helper->getConfig($this->_helper->getConfigSectionId() . '/' . $type . '/default_text_enable')) {
                $result['text'] = trim($this->_helper->getConfig($this->_helper->getConfigSectionId() . '/' . $type . '/default_text'));
                $result['text'] = base64_encode($result['text']);
                if ($field != 'text') {
                    $result['enable'] = (string)self::TEXT;
                    unset($result[$field]);
                }
            }
        }
        return $result;
    }

    protected function _parseData($object, $type, $originalValue = false)
    {
        $enable = $this->_value($object, $type, 'enable');
        $result = ['from' => '', 'to' => '', 'text' => '', 'enable' => $enable];
        $today = $this->timezone->date()->getTimestamp() + $this->timezone->date()->getOffset();

        $start = null;
        if ($this->_orderItem && $this->_orderItem->getId()) {
            $start = strtotime($this->_orderItem->getCreatedAt());
        }

        switch ($enable) {
            case self::DYNAMIC_RANGE:
                $result['to_origin'] = $this->_value($object, $type, 'days_to');
                if (is_null($result['to_origin'])) {
                   $result = $this->useDefaultText($type, $result, 'to_origin');
                   break;
                }
                //$result['to'] = $this->formatDate( $result['to_origin'], $type );
                // no break
            case self::DYNAMIC_DATE:
                $result['from_origin'] = $this->_value($object, $type, 'days_from');
                //$result['from'] = $this->formatDate( $result['from_origin'], $type );
                if (is_null($result['from_origin'])) {
                   $result = $this->useDefaultText($type, $result, 'from_origin');
                }
                break;

            case self::STATIC_RANGE:
                $dateTo = $this->_value($object, $type, 'date_to');
                if (strtotime($dateTo) >= $today) {
                    $result['to'] = $this->_helper->formattingDate($originalValue, $dateTo);
                    $dateFrom = $this->_value($object, $type, 'date_from');
                    $result['from'] = $this->_helper->formattingDate($originalValue, $dateFrom);
                    if (is_null($result['to'])) {
                       $result = $this->useDefaultText($type, $result, 'from');
                       break;
                    }
                    if (is_null($result['to'])) {
                       $result = $this->useDefaultText($type, $result, 'to');
                    }
                    break;
                } else {
                    $result = null;
                }
                // no break
            case self::STATIC_DATE:
                $dateFrom = $this->_value($object, $type, 'date_from');
                if (strtotime($dateFrom) >= $today) {
                    $result['from'] = $this->_helper->formattingDate($originalValue, $dateFrom);
                    if (is_null($result['from'])) {
                       $result = $this->useDefaultText($type, $result, 'from');
                    }
                } else {
                    $result = null;
                }
                break;

            case self::TEXT:
                $result['text'] = base64_encode($this->_value($object, $type, 'text'));
                if (is_null($result['text'])) {
                   $result = $this->useDefaultText($type, $result, 'text');
                }
                break;

            case self::DISABLED:
                $result = false;
                break;
            default:
                $result = null;
                break;
        }

        if (!$originalValue) {
            return $this->formatDates($result, $type, $start);
        }

        return $result;
    }

    protected function _value($object, $type, $param)
    {
        return $object->getData( $this->_param($type, $param) );
    }

    protected function _param($type, $param)
    {
        return 'estimated_' . $type . '_' . $param;
    }

}
