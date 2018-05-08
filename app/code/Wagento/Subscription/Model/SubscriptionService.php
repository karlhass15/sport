<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Wagento\Subscription\Model\SubscriptionFactory;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Helper\Data as SubscriptionHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class SubscriptionService
{
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulator;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_orderModel;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $_quoteManagementModel;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * @var
     */
    protected $subProductFactory;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var TimezoneInterface
     */
    protected $dateProcessor;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    protected $tokenRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var GetPaymentNonceCommand
     */
    protected $getPaymentNonceCommand;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteItem
     */
    protected $quoteItem;

    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var Address
     */
    protected $address;


    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * SubscriptionService constructor.
     * @param \Magento\Store\Model\App\Emulation $emulator
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagementModel
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param Json $serializer
     * @param \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory
     * @param \Wagento\Subscription\Model\ProductFactory $subProductFactory
     * @param SubscriptionHelper $subHelper
     * @param PriceHelper $priceHelper
     * @param TimezoneInterface $dateProcessor
     * @param PaymentHelper $paymentHelper
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\ProductMetadata|null $productMetadata
     * @param GetPaymentNonceCommand $getPaymentNonceCommand
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $checkoutSession
     * @param QuoteItem $quoteItem
     * @param AddressConfig $addressConfig
     * @param Address $address
     */
    public function __construct(
        \Magento\Store\Model\App\Emulation $emulator,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Model\QuoteManagement $quoteManagementModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Json $serializer,
        SubscriptionFactory $subscriptionFactory,
        ProductFactory $subProductFactory,
        SubscriptionHelper $subHelper,
        PriceHelper $priceHelper,
        TimezoneInterface $dateProcessor,
        PaymentHelper $paymentHelper,
        PaymentTokenRepositoryInterface $tokenRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\ProductMetadata $productMetadata = null,
        GetPaymentNonceCommand $getPaymentNonceCommand,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession,
        QuoteItem $quoteItem,
        AddressConfig $addressConfig,
        Address $address,
        OrderSender $orderSender
    ) {
    
        $this->emulator = $emulator;
        $this->_quoteFactory = $quoteFactory;
        $this->_orderModel = $orderModel;
        $this->_productModel = $productModel;
        $this->_customerRepository = $customerRepository;
        $this->_quoteManagementModel = $quoteManagementModel;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->subscriptionFactory = $subscriptionFactory->create();
        $this->subProductFactory = $subProductFactory->create();
        $this->subHelper = $subHelper;
        $this->priceHelper = $priceHelper;
        $this->dateProcessor = $dateProcessor;
        $this->paymentHelper = $paymentHelper;
        $this->tokenRepository = $tokenRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getPaymentNonceCommand = $getPaymentNonceCommand;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItem = $quoteItem;
        $this->addressConfig = $addressConfig;
        $this->address = $address;
        $this->orderSender = $orderSender;

        if ($productMetadata === null) {
            // Optional class dependency to preserve backwards compatibility on @api class.
            $this->productMetadata = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\ProductMetadata::class
            );
        } else {
            $this->productMetadata = $productMetadata;
        }
    }

    /**
     * @param $subscriptions
     * @return mixed
     */
    public function generateOrder($subscriptions)
    {
        $firstSubscription = current($subscriptions);
        $this->emulator->startEnvironmentEmulation($firstSubscription->getStoreId());
        try {
            $response = $this->generateQuote($subscriptions);
            $this->emulator->stopEnvironmentEmulation();
        } catch (\Exception $e) {
            $response['error_msg'] = $e->getMessage();
            $response['error'] = true;
        }
        $this->emulator->stopEnvironmentEmulation();
        return $response;
    }

    /**
     * @param $subscriptions
     * @return mixed
     */
    public function generateQuote($subscriptions)
    {
        $firstSubscription = current($subscriptions);
        $customerId = $firstSubscription->getCustomerId();
        $orderId = $firstSubscription->getSubscribeOrderId();
        $subItemId = $firstSubscription->getSubOrderItemId();
        $storeId = $firstSubscription->getStoreId();

        $_order = $this->_orderModel->load($orderId);
        $subProductId = $this->getSubscriptionProductId($_order, $subItemId);

        $price = $this->getSubscriptionProductPrice($_order, $subItemId);
        $subQty = $this->getSubscriptionProductQty($_order, $subItemId);

        $subShipAddressId = $firstSubscription->getShippingAddressId();
        $subBillAddressId = $firstSubscription->getBillingAddressId();
        $publicHash = $firstSubscription->getPublicHash();

        $shipAddress = $this->getShippingAddress($_order, $subShipAddressId);
        $billAddress = $this->getBillingAddress($_order, $subBillAddressId);
        $shippingMethod = $this->getShippingMethod($_order);
        if ($shippingMethod == 'freeshipping_freeshipping') {
            $shippingMethod = 'flatrate_flatrate';
        }
        $paymentMethod = $this->getPaymentMethod($_order);

        try {
            $customer = $this->_customerRepository->getById($customerId);
            $quote = $this->_quoteFactory->create();

            $subName = $firstSubscription->getSubName();
            $subscriptionFrequency = $firstSubscription->getSubFrequency();
            $howMany = $firstSubscription->getHowMany();
            $subDiscount = $firstSubscription->getSubDiscount();
            $subFee = $firstSubscription->getSubFee();
            $additionalOptions = $this->getSubscriptionOptions($subName, $subscriptionFrequency, $howMany ,$subDiscount ,$subFee);

            $product = $this->_productModel->load($subProductId);
            if (!empty($additionalOptions)) {
                foreach ($additionalOptions as $key => $subOption) {
                    $product->addCustomOption(
                        'additional_options',
                        $this->_serializer->serialize($subOption)
                    );
                }
            }

            $quote->setStoreId($storeId);

            /*customer details*/
            $quote->assignCustomer($customer);
            $quote->setCustomerEmail($_order->getCustomerEmail());

            /*Quote Item details*/
            $quoteItem = $quote->addProduct($product);
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->setQty($subQty);

            if (isset($publicHash)) {
                $tokenCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customerId)
                    ->addFilter('public_hash', $publicHash)
                    ->setPageSize(1)
                    ->create();
                $tokens = $this->tokenRepository->getList($tokenCriteria)->getItems();

                if (!empty($tokens)) {
                    $card = array_shift($tokens);
                }
            } else {
                $tokenCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customerId)
                    ->addFilter('entity_id', $firstSubscription->getPaymentTokenId())
                    ->setPageSize(1)
                    ->create();
                $tokens = $this->tokenRepository->getList($tokenCriteria)->getItems();
                if (!empty($tokens)) {
                    $card = array_shift($tokens);
                }
            }

            $payment = $quote->getPayment();
            $result = $this->getPaymentNonceCommand->execute(['public_hash' => $card->getPublicHash(), 'customer_id' => $card->getCustomerId()])->get();

            if (version_compare($this->productMetadata->getVersion(), '2.1.3', '>=')) {
                $payment->setAdditionalInformation('customer_id', $card->getCustomerId());
                $payment->setAdditionalInformation('public_hash', $card->getPublicHash());
                $payment->setAdditionalInformation('payment_method_nonce', $result['paymentMethodNonce']);
            } else {
                $payment->setAdditionalInformation(
                    'token_metadata',
                    [
                        'customer_id' => $card->getCustomerId(),
                        'public_hash' => $card->getPublicHash(),
                        'payment_method_nonce' => $result['paymentMethodNonce'],
                    ]
                );
            }

            $payment->setMethod($paymentMethod);
            $quote->getPayment()->setQuote($quote);
            $billingAddress = $quote->getBillingAddress()->addData($billAddress);
            $shippingAddress = $quote->getShippingAddress()->addData($shipAddress);
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod($shippingMethod)
                ->setPaymentMethod($paymentMethod);

            $quote->getShippingAddress()->setShippingMethod($shippingMethod);
            $quote->setPaymentMethod($paymentMethod);
            $quote->collectTotals();

            // This second collectTotals pulls in the new shipping amount.
            $quote->setTotalsCollectedFlag(false)->collectTotals();

            $quote->setInventoryProcessed(false);
            $quote->save();

            $order = $this->_quoteManagementModel->submit($quote);
            $order_id = $order->getId();
            $this->orderSender->send($order, true);

            if (isset($order_id) && !empty($order_id)) {
                $order = $this->orderRepository->get($order_id);
                $this->deleteQuoteItems(); //Delete cart items
                $response['success'] = true;
                $response['success_data']['increment_id'] = $order->getIncrementId();
                $response['success_data']['next_renewed'] = $this->getNextRenewDate($subscriptionFrequency);
            }
        } catch (\Exception $ex) {
            $response['error_msg'] = $ex->getMessage();
            $response['error'] = true;
        }
        return $response;
    }

    /**
     * @param $_order
     * @param $itemId
     * @return mixed
     */
    public function getSubscriptionProductId($_order, $itemId)
    {
        $items = $_order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getItemId() == $itemId) {
                return $item->getProductId();
            }
        }
    }

    /**
     * @param $_order
     * @param $itemId
     * @return mixed
     */
    public function getSubscriptionProductPrice($_order, $itemId)
    {
        $items = $_order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getItemId() == $itemId) {
                return $item->getPrice();
            }
        }
    }


    /**
     * @param $shippingAddressId
     * @return array
     */
    public function getSubAddress($shippingAddressId)
    {
        $shipAddressData = [];
        $address = $this->address->load($shippingAddressId);
        $shipAddressData = [
            "firstname" => $address->getFirstname(),
            "lastname" => $address->getLastname(),
            "street" => $address->getStreet(),
            "city" => $address->getCity(),
            "postcode" => $address->getPostcode(),
            "telephone" => $address->getTelephone(),
            "country_id" => $address->getCountryId(),
            "region_id" => $address->getRegionId(),
        ];
        return $shipAddressData;
    }

    /**
     * @param $_order
     * @return array
     */
    public function getShippingAddress($_order, $shippingAddressId)
    {
        if (isset($shippingAddressId)) {
            $shipAddressData = $this->getSubAddress($shippingAddressId);
        } else {
            $shipingAddress = $_order->getShippingAddress();
            $shipAddressData = [
                "firstname" => $shipingAddress->getFirstname(),
                "lastname" => $shipingAddress->getLastname(),
                "street" => $shipingAddress->getStreet(),
                "city" => $shipingAddress->getCity(),
                "postcode" => $shipingAddress->getPostcode(),
                "telephone" => $shipingAddress->getTelephone(),
                "country_id" => $shipingAddress->getCountryId(),
                "region_id" => $shipingAddress->getRegionId(),
            ];
        }

        return $shipAddressData;
    }

    /**
     * @param $_order
     * @return array
     */
    public function getBillingAddress($_order, $billinAddressId)
    {
        if (isset($billinAddressId)) {
            $billAddressData = $this->getSubAddress($billinAddressId);
        } else {
            $billingAddress = $_order->getBillingAddress();
            $billAddressData = [
                "firstname" => $billingAddress->getFirstname(),
                "lastname" => $billingAddress->getLastname(),
                "street" => $billingAddress->getStreet(),
                "city" => $billingAddress->getCity(),
                "postcode" => $billingAddress->getPostcode(),
                "telephone" => $billingAddress->getTelephone(),
                "country_id" => $billingAddress->getCountryId(),
                "region_id" => $billingAddress->getRegionId(),
            ];
        }
        return $billAddressData;
    }

    /**
     * @param $_order
     * @return mixed
     */
    public function getShippingMethod($_order)
    {
        return $_order->getShippingMethod();
    }

    /**
     * @param $_order
     * @return mixed
     */
    public function getPaymentMethod($_order)
    {
        return $_order->getPayment()->getMethod();
    }

    /**
     * @param $_order
     * @param $itemId
     * @return mixed
     */
    public function getSubscriptionProductQty($_order, $itemId)
    {
        $items = $_order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getItemId() == $itemId) {
                return $item->getQtyOrdered();
            }
        }
    }

    /**
     * @param $subProductId
     * @param $howMany
     * @return array
     */
    public function getSubscriptionOptions($subName, $subscriptionFrequency, $howMany ,$subDiscount ,$subFee)
    {
        $additionalOptions = [];
        $additionalOptions[] = [
            [
                'label' => "Subscription Plan Name",
                'value' => $subName
            ],
            [
                'label' => "Frequency",
                'value' => $this->subHelper->getSubscriptionFrequency($subscriptionFrequency)
            ],
            [
                'label' => "How Many",
                'value' => $howMany . " " . $howManyUnits
            ],


        ];
        if ($subDiscount != 0.0000) {
            $subDiscountWithCurrency = $this->priceHelper
                ->currency(
                    number_format($subDiscount, 2),
                    true,
                    false
                );
            $discountOption = ['label' => "Discount", 'value' => $subDiscountWithCurrency];
            array_push($additionalOptions[0], $discountOption);
        }

        if ($subFee != 0.0000) {
            $subFeeWithCurrency = $this->priceHelper
                ->currency(
                    number_format($subFee, 2),
                    true,
                    false
                );
            $initialFeeOption = ['label' => "Initial Fee", 'value' => $subFeeWithCurrency];
            array_push($additionalOptions[0], $initialFeeOption);
        }
        return $additionalOptions;
    }

    /**
     * @param $productCollector
     * @return mixed
     */
    private function returnSubscriptionId($productCollector)
    {
        foreach ($productCollector as $item) {
            return $item->getData('subscription_id');
        }
    }

    /**
     * @param $subPlanId
     */
    public function calculateNextRun($frequency)
    {

        $now = $this->dateProcessor->date(null, null, false);
        $date = $now->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);

        /*Daily */
        if ($frequency == 1) {
            $newDate = strtotime('+1 Day', strtotime($date));
            $daily = $this->dateProcessor->date($newDate);
            $nextRunDate = $daily->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }
        //Weekly
        if ($frequency == 2) {
            $newDate = strtotime('+1 Week', strtotime($date));
            $weekly = $this->dateProcessor->date($newDate);
            $nextRunDate = $weekly->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }

        //Monthly
        if ($frequency == 3) {
            $newDate = strtotime('+1 Month', strtotime($date));
            $monthly = $this->dateProcessor->date($newDate);
            $nextRunDate = $monthly->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }

        //Yearly
        if ($frequency == 4) {
            $newDate = strtotime('+1 Year', strtotime($date));
            $monthly = $this->dateProcessor->date($newDate);
            $nextRunDate = $monthly->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }
        return $nextRunDate;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function isQuoteTokenBase(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        if (in_array($quote->getPayment()->getMethod(), $this->getAllMethods())) {
            return true;
        }

        return false;
    }

    /**
     * Return all tokenbase-derived payment methods, without an active check.
     *
     * @api
     *
     * @return array
     */
    public function getAllMethods()
    {
        $methods = [];

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if (isset($data['group']) && $data['group'] == 'tokenbase') {
                $methods[] = $code;
            }
        }

        return $methods;
    }

    /**
     * @throws \Exception
     */
    public function deleteQuoteItems()
    {
        $checkoutSession = $this->checkoutSession;
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();//returns all teh items in session
        foreach ($allItems as $item) {
            $itemId = $item->getItemId();//item id of particular item
            $quoteItem = $this->quoteItem->load($itemId);//load particular item which you want to delete by his item id
            $quoteItem->delete();//deletes the item
        }
    }

    /**
     * @return string
     */
    public function getNextRenewDate($subscriptionFrequency)
    {
        return $this->calculateNextRun($subscriptionFrequency);
    }
}
