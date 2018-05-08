<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Helper;

use Wagento\Subscription\Helper\Data;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const TRANS_IDENT_EMAIL_NAME = 'trans_email/ident_%s/name';
    const TRANS_IDENT_EMAIL = 'trans_email/ident_%s/email';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var string
     */
    protected $temp_id;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customers;

    /**
     * @var \Wagento\Subscription\Helper\Data
     */
    protected $helper;

    /**
     * Email constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Customer\Model\Customer $customers
     * @param \Wagento\Subscription\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Customer $customers,
        Data $helper
    ) {
    
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->customers = $customers;
        $this->helper = $helper;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * [generateTemplate description]  with template file and tempaltes variables values
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, /* here you can defile area and
                                                                                 store of template for which you prepare it */
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    /**
     * [sendInvoicedOrderEmail description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    /* your send mail method*/
    public function sentReminderEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->getStore()->getStoreId()];
        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier('reminder_email_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo)
            ->getTransport();

        try {
            $result = $transport->sendMessage();
            if (!$result) {
                $response['success'] = true;
            }
            $this->inlineTranslation->resume();
            return $response;
        } catch (\Exception $e) {
            $response['error_msg'] = $e->getMessage();
            $response['error'] = true;
            return $response;
        }
    }


    /**
     * [sendInvoicedOrderEmail description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    /* your send mail method*/
    public function sentStatusChangeEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->getStore()->getStoreId()];
        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier('change_status_email_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo)
            ->getTransport();

        try {
            $result = $transport->sendMessage();
            if (!$result) {
                $response['success'] = true;
            }
            $this->inlineTranslation->resume();
            return $response;
        } catch (\Exception $e) {
            $response['error_msg'] = $e->getMessage();
            $response['error'] = true;
            return $response;
        }
    }

    /**
     * @param $xmlPath
     * @return array
     */
    public function getEmailSenderInfo($xmlPath)
    {
        $emailSender = $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
        $namePath = sprintf(self::TRANS_IDENT_EMAIL_NAME, $emailSender);
        $emailPath = sprintf(self::TRANS_IDENT_EMAIL, $emailSender);

        $senderName = $this->getConfigValue($namePath, $this->getStore()->getStoreId());
        $senderEmail = $this->getConfigValue($emailPath, $this->getStore()->getStoreId());

        $senderInfo = [
            'name' => $senderName,
            'email' => $senderEmail
        ];
        return $senderInfo;
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getRecieverInfo($customerId)
    {
        //Get customer by customerID
        $customer = $this->customers->load($customerId);

        $receiverInfo = [
            'name' => $customer->getFirstname() . " " . $customer->getLastname(),
            'email' => $customer->getEmail()
        ];
        return $receiverInfo;
    }

    /**
     * @param $customerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerName($customerId)
    {
        $customer = $this->customers->load($customerId);
        return $customer->getFirstname() . " " . $customer->getLastname();
    }

    /**
     * @param $id
     * @param $status
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStatusEmailVariables($id, $status, $customerId)
    {
        $emailTempVariables = [
            'store' => $this->getStore(),
            'increament_id' => $id,
            'customer_name' => $this->getCustomerName($customerId),
            'status' => $this->helper->getSubscriptionStatus($status)
        ];
        return $emailTempVariables;
    }

    /**
     * @param $xmlPath
     * @return mixed
     */
    public function getIsEmailConfigEnable($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore());
    }


    /**
     * @param $xmlPath
     * @return mixed
     */
    public function getIsStatusChangeEmailCustomer($xmlPath)
    {
        $emailOptions = $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
        $arrayEmailOptions = explode(',', $emailOptions);
        if (in_array(4, $arrayEmailOptions)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $xmlPath
     * @return mixed
     */
    public function getIsStatusChangeEmailAdmin($xmlPath)
    {
        $emailOptions = $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
        $arrayEmailOptions = explode(',', $emailOptions);
        if (in_array(5, $arrayEmailOptions)) {
            return true;
        } else {
            return false;
        }
    }
}
