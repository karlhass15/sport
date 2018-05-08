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
class Suscribecart extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Checkout\Model\Sidebar
     */
    protected $sidebar;

    /**
     * Suscribecart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Wagento\Subscription\Helper\Product $subProductHelper
     * @param \Magento\Catalog\Model\ProductRepository $product
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Checkout\Model\Sidebar $sidebar
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Wagento\Subscription\Helper\Product $subProductHelper,
        \Magento\Catalog\Model\ProductRepository $product,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Sidebar $sidebar
    ) {
    
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->subProductHelper = $subProductHelper;
        $this->product = $product;
        $this->cart = $cart;
        $this->sidebar = $sidebar;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $subOptionsJson = $this->helper->jsonDecode($this->getRequest()->getContent());
        $subOptions = $this->helper->jsonDecode($subOptionsJson);

        if ($subOptions['subItemId']) {
            $product_id = $subOptions['productId'];
            $qty = $subOptions['subqty'];
            $itemid = $subOptions['subItemId'];
            $links = $subOptions['links'];

           // $this->sidebar->checkQuoteItem($itemid);
           // $this->sidebar->updateQuoteItem($itemid, $qty);
            $addProduct = $this->subProductHelper
                ->addToCartSubscriptionProduct($product_id, $qty, $links);
            if ($addProduct == 1) {
                $_product = $this->product->getById($subOptions['productId'])->getName();
                $message = 'product ' . $_product . ' subscribed successfully';
                $response_json = [
                    'status' => 'success',
                    'message' => $message
                ];
                $this->messageManager->addSuccessMessage(__($message));
                $this->sidebar->removeQuoteItem($subOptions['subItemId']);
            } else {
                $response_json = [
                    'status' => 'error',
                    'message' => $addProduct
                ];
                $this->messageManager->addErrorMessage(__($addProduct));
            }
        }
        return $resultJson->setData($response_json);
    }
}
