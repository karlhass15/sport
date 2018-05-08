<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Unsuscribecart extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /** @var \Wagento\Subscription\Helper */

    protected $subProductHelper;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $prod;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;


    /**
     * Initialize Login controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param AccountManagementInterface $customerAccountManagement
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Wagento\Subscription\Helper\Product $subProductHelper,
        \Magento\Catalog\Model\ProductRepository $product,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
    
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->subProductHelper = $subProductHelper;
        $this->product = $product;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
    }

    public function execute()
    {
        $itemJson = $this->helper->jsonDecode($this->getRequest()->getContent());
        $item = $this->helper->jsonDecode($itemJson);
        if ($item['product_id']) {
            $product_id = $item['product_id'];
            $items = $this->cart->getQuote()->getAllVisibleItems();
            foreach ($items as $item) {
                if ($product_id == $item->getProductId()) {
                    try {
                        $item->setIsSubscribed(0);
                        $item->save();
                        $productName = $this->product->getById($product_id)->getName();
                        $message = 'The Product ' . $productName . ' has been unsubscribed succesfully';
                        $response_json = [
                            'status' => 'success',
                            'message' => $message
                        ];
                        $this->messageManager->addSuccessMessage(__($message));
                    } catch (\Exception $e) {
                        $message = 'The product couldn\'t have been unsuscribed due to ' . $e->getMessage();
                        ;
                        $response_json = [
                            'status' => 'warning',
                            'message' => $message
                        ];
                        $this->messageManager->addWarningMessage(__($message));
                    }
                    break;
                }
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response_json);
    }
}
