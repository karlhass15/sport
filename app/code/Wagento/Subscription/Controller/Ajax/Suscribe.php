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
class Suscribe extends \Magento\Framework\App\Action\Action
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
        \Magento\Catalog\Model\ProductRepository $product
    ) {
    
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->subProductHelper = $subProductHelper;
        $this->product = $product;
    }

    public function execute()
    {
        $subOptionsJson = $this->helper->jsonDecode($this->getRequest()->getContent());
        $subOptions = $this->helper->jsonDecode($subOptionsJson);
        $productId = $subOptions['productId'];
        $subQty = $subOptions['subqty'];
        $subLinks = $subOptions['links'];
        $addProduct = $this->subProductHelper
            ->addToCartSubscriptionProduct($productId, $subQty, $subLinks);
        if ($addProduct == 1) {
            $_product = $this->product->getById($subOptions['productId'])->getName();
            $message = 'product ' . $_product . ' subscribed successfully';
            $response_json = [
                'status' => 'success',
                'message' => $message
            ];
            $this->messageManager->addSuccessMessage(__($message));
        } else {
            $response_json = [
                'status' => 'error',
                'message' => $addProduct
            ];
            $this->messageManager->addErrorMessage(__($addProduct));
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response_json);
    }
}
