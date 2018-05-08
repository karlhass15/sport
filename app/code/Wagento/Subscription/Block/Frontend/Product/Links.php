<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Frontend\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Pricing\Price\LinkPrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Json\EncoderInterface;

/**
 * Links View block
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Links extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var Magento\Checkout\Model\Session
     */
    public $_session;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    public $quoteRepository;

    /**
     * @var \Wagento\Subscription\Helper\Product //use Magento\Checkout\Model\Cart;
     */
    public $helper;
    /**
     * @var EncoderInterface
     */
    public $encoder;


    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Session $session,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Wagento\Subscription\Helper\Product $helper,
        array $data = []
    ) {
    
        $this->_session = $session;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
        $this->encoder = $encoder;
        $this->scopeConfig = $context->getScopeConfig();

        parent::__construct(
            $context,
            $urlEncoder,
            $encoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }
}
