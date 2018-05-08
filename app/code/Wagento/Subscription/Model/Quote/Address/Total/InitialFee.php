<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Quote\Address\Total;

use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Cart;

class InitialFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Quote\Model\QuoteValidator|null
     */
    private $quoteValidator = null;
    /**
     * @var Cart
     */
    private $cart;
    /**
     * @var \Wagento\Subscription\Helper\Product
     */
    private $subProductHelper;

    /**
     * InitialFee constructor.
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param Cart $cart
     * @param \Wagento\Subscription\Helper\Product $subProductHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        Cart $cart,
        \Wagento\Subscription\Helper\Product $subProductHelper
    ) {
    
        $this->quoteValidator = $quoteValidator;
        $this->cart = $cart;
        $this->subProductHelper = $subProductHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!empty($shippingAssignment->getItems())) {
            return $this;
        }
        $items = $this->cart->getItems();
        $initialfee = $this->getInitialFeeValue($items);
        $total->addTotalAmount('initialfee', $initialfee);
        $total->addBaseTotalAmount('initialfee', $initialfee);
        $quote->setInitialFee($initialfee);
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $items = $this->cart->getItems();
        $initialfee = $this->getInitialFeeValue($items);

        return [
            'code' => 'initialfee',
            'title' => 'Subscription Initial Fee',
            'value' => $initialfee
        ];
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param $items
     * @return float|null
     */
    protected function getInitialFeeValue($items)
    {
        $initialfee = 0.0000;
        foreach ($items as $key => $item) {
            $productId = $item->getProductId();
            $isSubscribed = $item->getIsSubscribed();
            if ($isSubscribed != 0) {
                $initialfee += $this->subProductHelper->getInitialFee($productId);
            }
        }
        return $initialfee;
    }
}
