<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCTQA
 * @copyright  Copyright (c) 2017 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
namespace Itoris\ProductQa\Helper;

use Magento\Framework\DataObject;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $alias = 'productqa';
    protected $_responseFactory;
    protected $_helperBackendData;
    protected $customer;
    protected $captcha;
    /** @var mixed Magento\Backend\App\ConfigInterface */
    protected $_backendConfig;
    protected $_date;
    protected $name='';
    protected $email;
    protected $_transportBuilder;
    protected $objectManager;
    const SCOPE_TYPE_STORES = 'store';
    const XML_PATH_MODULE_ENABLED = 'itoris_productqa/general/enabled';
    const XML_PATH_MODULE_VISIBLE = 'itoris_productqa/general/visible';
    const XML_PATH_MODULE_CAPTCHA = 'itoris_productqa/general/captcha';
    const XML_PATH_MODULE_VISITOR_POST = 'itoris_productqa/general/visitor_post';
    const XML_PATH_MODULE_VISITOR_CAN_RATE = 'itoris_productqa/general/visitor_can_rate';
    const XML_PATH_MODULE_QUESTIONS_APPROVAL = 'itoris_productqa/general/questions_approval';
    const XML_PATH_MODULE_ANSWERS_APPROVAL = 'itoris_productqa/general/answers_approval';
    const XML_PATH_MODULE_QUESTION_LENGTH = 'itoris_productqa/general/question_length';
    const XML_PATH_MODULE_ANSWER_LENGTH = 'itoris_productqa/general/answer_length';
    const XML_PATH_MODULE_QUESTIONS_PER_PAGE = 'itoris_productqa/general/questions_per_page';
    const XML_PATH_MODULE_ADMIN_EMAIL = 'itoris_productqa/general/admin_email';
    const XML_PATH_MODULE_ALLOW_SUBSCRIBING_QUESTION = 'itoris_productqa/general/allow_subscribing_question';
    const XML_PATH_MODULE_TEMPLATE_ADMIN_NOTIFICATION = 'itoris_productqa/email_config/template_admin_notification';
    const XML_PATH_MODULE_TEMPLATE_SENDER_ADMIN_NOTIFICATION = 'itoris_productqa/email_config/email_admin_sender';
    const XML_PATH_MODULE_TEMPLATE_USER_NOTIFICATION = 'itoris_productqa/email_config/template_user_notification';
    const XML_PATH_MODULE_TEMPLATE_SENDER_USER_NOTIFICATION = 'itoris_productqa/email_config/email_user_sender';
    const XML_PATH_MODULE_TEMPLATE_GUEST_NOTIFICATION = 'itoris_productqa/email_config/template_guest_notification';
    const XML_PATH_MODULE_SENDER_GUEST_NOTIFICATION = 'itoris_productqa/email_config/email_guest_sender';
    const XML_PATH_LICENSE = 'itoris_core/installed/Itoris_ProductQa';
    const XML_PATH_CAPTCHA_ENABLED_STOREFRONT = 'customer/captcha/enable';
    protected $_registry;
    protected $emulation;
    protected $_request;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\Registry $registry


    )
    {
        $this->_responseFactory = $responseFactory;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_transportBuilder=$transportBuilder;
        $this->_objectManager = $objectManager;
        $this->_backendConfig = $this->_objectManager->create('Magento\Backend\App\ConfigInterface');
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
        $this->_date = $dateTime;
        $this->_request = $this->_objectManager->get('\Magento\Framework\App\RequestInterface');
        $this->_localeResolver = $localeResolver;
        $this->registry = $registry;
        parent::__construct($context);
    }
    public function stopEnvironmentEmulation() {
        $this->emulation->stopEnvironmentEmulation();
        return $this;
    }
    public function startEnvironmentEmulation($storeId) {
        $area = \Magento\Framework\App\Area::AREA_FRONTEND;
        /** @var  $emulation  \Magento\Store\Model\App\Emulation */
        $emulation = $this->_objectManager->create('Magento\Store\Model\App\Emulation');
        $emulation->startEnvironmentEmulation($storeId, $area);
        $this->emulation=$emulation;
        return $emulation;
    }
    public function captcheResolver(){
        if( $this->getObjectManager()->get('Magento\Captcha\Observer\CaptchaStringResolver'))
         return $this->getObjectManager()->get('Magento\Captcha\Observer\CaptchaStringResolver');
        return $this->getObjectManager()->create('Magento\Captcha\Observer\CaptchaStringResolver');
    }
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    public function getSettings($store)
    {
        if($this->getRegistry()->registry('settings')){
          return  $this->getRegistry()->registry('settings');
        }
        $registrySettings =  new DataObject([
            'visible' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_VISIBLE, self::SCOPE_TYPE_STORES, $store),
            'admin_email' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_ADMIN_EMAIL, self::SCOPE_TYPE_STORES, $store),
            'sender_user_subject' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_TEMPLATE_SENDER_USER_NOTIFICATION, self::SCOPE_TYPE_STORES, $store),
            'sender_admin_subject' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_TEMPLATE_SENDER_ADMIN_NOTIFICATION, self::SCOPE_TYPE_STORES, $store),
            'sender_guest_subject' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_SENDER_GUEST_NOTIFICATION, self::SCOPE_TYPE_STORES, $store),
            'allow_subscribing_question' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_ALLOW_SUBSCRIBING_QUESTION, self::SCOPE_TYPE_STORES, $store),
            'answer_length' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_ANSWER_LENGTH, self::SCOPE_TYPE_STORES, $store),
            'question_length' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_QUESTION_LENGTH, self::SCOPE_TYPE_STORES, $store),
            'answer_approval' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_ANSWERS_APPROVAL, self::SCOPE_TYPE_STORES, $store),
            'captcha' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_CAPTCHA, self::SCOPE_TYPE_STORES, $store),
            'template_guest_notification' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_TEMPLATE_GUEST_NOTIFICATION, self::SCOPE_TYPE_STORES, $store),
            'template_user_notification' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_TEMPLATE_USER_NOTIFICATION, self::SCOPE_TYPE_STORES, $store),
            'question_approval' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_QUESTIONS_APPROVAL, self::SCOPE_TYPE_STORES, $store),
            'visitor_can_rate' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_VISITOR_CAN_RATE, self::SCOPE_TYPE_STORES, $store),
            'questions_per_page' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_QUESTIONS_PER_PAGE, self::SCOPE_TYPE_STORES, $store),
            'visitor_post' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_VISITOR_POST, self::SCOPE_TYPE_STORES, $store),
            'template_admin_notification' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_TEMPLATE_ADMIN_NOTIFICATION, self::SCOPE_TYPE_STORES, $store),
            'captcha_in_front' => $this->scopeConfig
                ->getValue(self::XML_PATH_MODULE_CAPTCHA, self::SCOPE_TYPE_STORES, $store),

        ]);
        $this->getRegistry()->register('settings',$registrySettings);
        return $registrySettings;
    }
/** @return \Magento\Customer\Model\Session */
    public function getSession()
    {
        //if($this->getObjectManager()->get('Magento\Customer\Model\Session')){
        //    return $this->getObjectManager()->get('Magento\Customer\Model\Session');
        //}else{
            return $this->getObjectManager()->create('Magento\Customer\Model\Session');
        //}


    }
    public function countQuestion(){
        return $this->getObjectManager()->create('Itoris\ProductQa\Model\ResourceModel\Questions\Collection')->getSizeWithApprovalProduct();
    }
    public function countAnswer(){
        return $this->getObjectManager()->create('Itoris\ProductQa\Model\ResourceModel\Answers\Collection')->getAnswerCount();
    }

    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = $this->getObjectManager()->get('Magento\Framework\App\RequestInterface');
        }
        if (!$this->_request)
            $this->_request = $this->getObjectManager()->create('Magento\Framework\App\RequestInterface');
        return $this->_request;
    }

    /** @return \Magento\Backend\Helper\Data */
    public function getBackendHelperData()
    {
        if (!$this->_helperBackendData)
            $this->_helperBackendData = $this->_objectManager->get('Magento\Backend\Helper\Data');
        if (!$this->_helperBackendData)
            $this->_helperBackendData = $this->_objectManager->create('Magento\Backend\Helper\Data');
        return $this->_helperBackendData;
    }

    /** @return \Itoris\CmsDisplayRules\Helper\Data */
    public function isDisabledForStore($storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->getStoreManager()->getStore()->getId();
        }


        return !(bool)$this->scopeConfig->getValue(self::XML_PATH_MODULE_ENABLED, self::SCOPE_TYPE_STORES, $storeId);
    }

    public function isVisible($storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->getStoreManager()->getStore()->getId();
        }


        return !(bool)$this->scopeConfig->getValue(self::XML_PATH_MODULE_VISIBLE, self::SCOPE_TYPE_STORES, $storeId);
    }

    public function isCaptcha($storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->getStoreManager()->getStore()->getId();
        }


        return !(bool)$this->scopeConfig->getValue(self::XML_PATH_MODULE_CAPTCHA, self::SCOPE_TYPE_STORES, $storeId);
    }

    public function getUrl($route, $params=[])
    {
        return $this->_getUrl($route, $params);
    }

    public function getRegistry()
    {
        if (!$this->_registry) {
            $this->_registry = $this->getObjectManager()->get('Magento\Framework\Registry');
        }
        if (!$this->_registry) {
            $this->_registry = $this->getObjectManager()->create('Magento\Framework\Registry');
        }
        return $this->_registry;

    }

    /**
     * @return \Magento\Backend\App\ConfigInterface|mixed
     */
    public function getBackendConfig()
    {
        return $this->_backendConfig;
    }

    public function isDisabledBackendForStore($storeId = null)
    {
        return !(bool)$this->scopeConfig->getValue(self::XML_PATH_MODULE_ENABLED, self::SCOPE_TYPE_STORES, $storeId);
    }

    public function isEnabled()
    {
        if($this->getState()!='frontend') {
            return !$this->isDisabledForStore()
            && count(explode('|', $this->_backendConfig->getValue(self::XML_PATH_LICENSE))) == 2;
        }else{
            return $this->isEnabledCustomer() && (!$this->isDisabledForStore()
            && count(explode('|', $this->_backendConfig->getValue(self::XML_PATH_LICENSE))) == 2);
        }
    }
    public function isEnabledCustomer(){
        if($this->getSettings($this->getStoreManager()->getStore()->getId())->getVisible()==3){
            return true;
        }
        if($this->getSettings($this->getStoreManager()->getStore()->getId())->getVisible()==4 && $this->getSession()->getId()){
            return true;
        }
        return false;
    }
    public function isEnabledBackend($storeId)
    {
        return !$this->isDisabledBackendForStore((int)$storeId)
        && count(explode('|', $this->_backendConfig->getValue(self::XML_PATH_LICENSE))) == 2;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
    }

    /**
     * Convert $days to string: 'today' or 'X days ago' or 'X months ago' or 'X years and Y months ago'
     *
     * @param $days
     * @return string
     */
    public function getDateStr($days)
    {
        $days = (int)$days;
        if ($days < 0) {
            return __('today');
        }
        if (!$days) {
            return __('today');
        }
        if ($days < 31) {
            return $days . __(' days ago');
        } elseif ($days <= 365) {
            return (int)($days / 30) . __(' months ago');
        } else {
            $months = (int)(($days % 365) / 30);
            $months = ($months) ? __(' and ') . $months . __(' months ago') : '';
            return (int)($days / 365) . __(' years') . $months . __(' ago');
        }
    }

    /**
     * Get html link element
     *
     * @param $url
     * @return string
     */
    public function getHtmlLink($url)
    {
        return $url;
    }

    /**
     * Get user type label
     *
     * @param $class
     * @param $id
     * @return string
     */
    public function getUserType($class, $id)
    {
        if ($class == 'Itoris\ProductQa\Model\Answers') {
            switch ($id) {
                case \Itoris\ProductQa\Model\Answers::SUBMITTER_ADMIN:
                    return __('Administrator');
                case \Itoris\ProductQa\Model\Answers::SUBMITTER_CUSTOMER:
                    return __('Customer');
                case \Itoris\ProductQa\Model\Answers::SUBMITTER_VISITOR:
                    return __('Guest');
            }
        }
        if ($class == 'Itoris\ProductQa\Model\Questions') {
            switch ($id) {
                case \Itoris\ProductQa\Model\Questions::SUBMITTER_ADMIN:
                    return __('Administrator');
                case \Itoris\ProductQa\Model\Questions::SUBMITTER_CUSTOMER:
                    return __('Customer');
                case \Itoris\ProductQa\Model\Questions::SUBMITTER_VISITOR:
                    return __('Guest');
            }
        }
    }

    public function getQuestionModel()
    {
        return $this->getObjectManager()->create('Itoris\ProductQa\Model\Questions');
    }
    /** @return \Itoris\ProductQa\Model\Answers */
    public function getAnswerModel()
    {
        return $this->getObjectManager()->create('Itoris\ProductQa\Model\Answers');
    }

    /** @return \Magento\Framework\App\Config\ScopeConfigInterface */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }
    /** @return  \Magento\Framework\App\ResourceConnection */
    public function getResourceConnection(){
        return $this->getObjectManager()->get('Magento\Framework\App\ResourceConnection');
    }
    public function getProductUrl($id, $storeId = null,$productOne=null)
    {
        if(!$productOne)
        $product = $this->getObjectManager()->create('Magento\Catalog\Model\Product')->load((int)$id);
        else
           $product  = $productOne;
        $productUrl = $product->getUrlModel();
            $url = $product->getProductUrl($id);
        if ($storeId) {
            $product->setStoreId($storeId);

                $urlInStore = $product->getProductUrl($id);
            $urls = array(
                'url' => $url,
                'url_in_store' => $urlInStore,
            );
            return $urls;
        }

        return $url;
    }
    public function getAlias()
    {
        return $this->alias;
    }
    /** @return  \Magento\Captcha\Helper\Data */
    public function getCaptcha(){
        if($this->captcha){
            return $this->captcha;
        }
      return  $this->captcha = $this->getStoreManager()->create('Magento\Captcha\Helper\Data');
    }
    public function prepareHtmlText($text)
    {
        return nl2br($text);
    }
    public function sendEmail($templateId, $templateParams = array(), $store,$emailsettings,$templateModel)
    {
        $state = $this->_objectManager->get('Magento\Framework\App\State');
        $sender['email'] = $this->scopeConfig->getValue('trans_email/ident_'.$emailsettings.'/email');
        $sender['name'] = $this->scopeConfig->getValue('trans_email/ident_'.$emailsettings.'/name');
        try {
            $this->getMessageManager()->addSuccessMessage(__('Email has been sent'));
            $this->_sendEmailTemplate($templateId,$sender,$templateParams,$store->getId(),$templateModel);
        }
        catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage(__('Failed to send email'));
        }
    }
    public function setName($name){
        $this->name=$name;
    }
    public function setEmailTo($email){
        $this->email=$email;
    }
    /** @return \Magento\Framework\Message\ManagerInterface */
    public function getMessageManager(){
        return $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
    }
    /**
     * Send corresponding email template
     *
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     */
    protected function _sendEmailTemplate($templateId, $sender, $templateParams = [], $storeId = null,$templateModel)
    {
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )
         ->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $sender
        )->addTo(
            $this->email,
            $this->name
        )->getTransport();
        $transport->sendMessage();
        return $this;
    }
    protected $_escaper;
    public function getEscaper(){
        if(!$this->_escaper){
            $this->_escaper = $this->getObjectManager()->get('Magento\Framework\Escaper');
        }
        if(!$this->_escaper){
            $this->_escaper = $this->getObjectManager()->create('Magento\Framework\Escaper');
        }
        return $this->_escaper;
    }
    public function isEnabledProductTabs() {
        return (int)$this->_backendConfig->getValue('itoris_producttabsslider/general/enabled') && !$this->isDisabledProductTabsForStore()
        && count(explode('|', $this->_backendConfig->getValue('itoris_core/installed/Itoris_Producttabsslider'))) == 2;
    }

    public function isDisabledProductTabsForStore(){
        return !(bool)$this->scopeConfig->getValue('itoris_producttabsslider/general/enabled', self::SCOPE_TYPE_STORES, $this->getStoreManager()->getStore()->getId());
    }
    public function getState(){
      return $this->getObjectManager()->get('Magento\Framework\App\State')->getAreaCode();
    }
}
