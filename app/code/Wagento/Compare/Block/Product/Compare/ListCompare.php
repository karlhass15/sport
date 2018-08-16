<?php
/**
 * Created by PhpStorm.
 * User: Dev4
 * Date: 8/16/18
 * Time: 4:03 PM
 */

namespace Wagento\Compare\Block\Product\Compare;


class ListCompare extends \Magento\Catalog\Block\Product\Compare\ListCompare
{
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = [])
    {
        parent::__construct(
            $context,
            $urlEncoder,
            $itemCollectionFactory,
            $catalogProductVisibility,
            $customerVisitor,
            $httpContext,
            $currentCustomer,
            $data
        );
    }

    /**
     * Retrieve Product Attribute Value
     *
     * @param Product $product
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return \Magento\Framework\Phrase|string
     */
    public function getProductAttributeValue($product, $attribute)
    {
        if (!$product->hasData($attribute->getAttributeCode())) {
            return __('N/A');
        }

        if ($attribute->getSourceModel() || in_array(
                $attribute->getFrontendInput(),
                ['select', 'boolean', 'multiselect']
            )
        ) {
            //$value = $attribute->getSource()->getOptionText($product->getData($attribute->getAttributeCode()));
            $value = $attribute->getFrontend()->getValue($product);
        } else {
            $value = $product->getData($attribute->getAttributeCode());
        }

        if(is_array($value)){
            $value = '';
        }
        return (string)$value == '' ? __('No') : $value;
    }
}