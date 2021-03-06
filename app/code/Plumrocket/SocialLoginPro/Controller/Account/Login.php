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
 * @package     Plumrocket_SocialLoginPro
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\SocialLoginPro\Controller\Account;

class Login extends \Plumrocket\SocialLoginPro\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * Login constructor.
     *
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Plumrocket\SocialLoginPro\Helper\Data     $dataHelper
     * @param \Magento\Store\Model\StoreManager          $storeManager
     * @param \Magento\Framework\View\Layout\Interceptor $layout
     * @param \Magento\Customer\Model\Customer           $customer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Plumrocket\SocialLoginPro\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\View\Layout\Interceptor $layout,
        \Magento\Customer\Model\Customer $customer
    ) {
        parent::__construct($context, $customerSession, $dataHelper, $storeManager, $layout);
        $this->customer = $customer;
    }

    public function execute()
    {
        $session = $this->_getSession();
        $type = $this->getRequest()->getParam('type');

        // API.
        $callTarget = false;
        if ($call = $this->_getHelper()->apiCall()) {
            if (isset($call['type']) && $call['type'] == $type && !empty($call['action'])) {
                $_target = explode('.', $call['action'], 3);
                if (count($_target) === 3) {
                    $callTarget = $_target;
                } else {
                    $this->_windowClose();
                    return;
                }
            }
        }

        if ($session->isLoggedIn() && !$callTarget) {
            return $this->_windowClose();
        }

        $className = 'Plumrocket\SocialLoginPro\Model\\'. ucfirst($type);
        if (!$type || !class_exists($className)) {
            return $this->_windowClose();
        }

        $model = $this->_objectManager->get($className);

        $responseTypes = $model->getResponseType();
        if (is_array($responseTypes)) {
            $response = [];
            foreach ($responseTypes as $responseType) {
                $response[$responseType] = $this->getRequest()->getParam($responseType);
            }
        } else {
            $response = $this->getRequest()->getParam($responseTypes);
        }
        $model->_setLog($this->getRequest()->getParams());

        if (!$model->loadUserData($response)) {
            if ($this->_getHelper()->getDebugMode()) {
                $model->recordLog();
                $this->displayError($model);
                return;
            } else {
                $this->getResponse()->setBody(__('The Login Application was not configured correctly. If your are the admin of store: Please activate “Enable Logging” in Magento Login Extension and try again to see error details.'));
                return;
            }
        }

        // Switch store.
        if ($storeId = $this->_getHelper()->refererStore()) {
            $this->storeManager->setCurrentStore($storeId);
        }

        // API.
        if ($callTarget) {
            list($module, $controller, $action) = $callTarget;
            $this->_forward($action, $controller, $module, ['pslogin' => $model->getUserData()]);
            return;
        }

        if ($customerId = $model->getCustomerIdByUserId()) {
            if ($responseEmail = $model->getUserData('email')) {
                $customer = $this->customer->load($customerId);
                if ($customer->getId() && $this->_getHelper()->isFakeMail($customer->getEmail())) {
                    if ($responseEmail != $customer->getEmail()) {
                        $otherCustomer = $this->customer
                            ->getCollection()
                            ->addFieldToFilter('email', $responseEmail)
                            ->setPageSize(1)
                            ->getFirstItem();

                        if (!$otherCustomer->getId()) {
                            $customer->setEmail($responseEmail)->save();
                        }
                    }
                }
            }
            # Do auth.
            $redirectUrl = $this->_getHelper()->getRedirectUrl();
        } elseif ($customerId = $model->getCustomerIdByEmail()) {
            # Customer with received email was placed in db.
            // Remember customer.
            $model->setCustomerIdByUserId($customerId);
            // System message.
            $url = $this->_getUrl('customer/account/forgotpassword');
            $message = __('Customer with email (%1) already exists in the database. If you are sure that it is your email address, please <a href="%2">click here</a> to retrieve your password and access your account.', $model->getUserData('email'), $url);
            $this->messageManager->addNotice($message);

            $redirectUrl = $this->_getHelper()->getRedirectUrl();
        } else {
            # Registration customer.
            if ($customerId = $model->registrationCustomer()) {
                # Success.
                // Display system messages (before setCustomerIdByUserId(), because reset messages).
                if ($this->_getHelper()->isFakeMail($model->getUserData('email'))) {
                    $this->messageManager->addSuccess(__('Customer registration successful.'));
                } else {
                    $this->messageManager->addSuccess(__('Customer registration successful. Your password was send to the email: %1', $model->getUserData('email')));
                }

                if ($errors = $model->getErrors()) {
                    foreach ($errors as $error) {
                        $this->messageManager->addNotice($error);
                    }
                }

                // Dispatch event.
                $this->_dispatchRegisterSuccess($model->getCustomer());

                // Remember customer.
                $model->setCustomerIdByUserId($customerId);

                // Post mail.
                $model->postToMail();

                // Show share-popup.
                $this->_getHelper()->showPopup();

                $redirectUrl = $this->_getHelper()->getRedirectUrl('register');
            } else {
                # Error.
                $session->setCustomerFormData($model->getUserData());
                $redirectUrl = $this->_getUrl('customer/account/create', ['_secure' => true]);

                if ($errors = $model->getErrors()) {
                    foreach ($errors as $error) {
                        $this->messageManager->addError($error);
                    }
                }

                // Remember current provider data.
                $session->setData('pslogin', [
                    'provider'  => $model->getProvider(),
                    'user_id'   => $model->getUserData('user_id'),
                    'photo'     => $model->getUserData('photo'),
                    'timeout'   => time() + \Plumrocket\SocialLoginPro\Helper\Data::TIME_TO_EDIT,
                ]);
            }
        }

        if ($customerId) {
            // Load photo.
            if ($this->_getHelper()->photoEnabled()) {
                $model->setCustomerPhoto($customerId);
            }

            // Loged in.
            if ($session->loginById($customerId)) {
                $session->regenerateId();
            }

            // Unset referer link.
            $this->_getHelper()->refererLink(null);

            // Remember provider type (for persona).
            $session->setLoginProvider($model->getProvider());
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode([
                'redirectUrl' => $redirectUrl
            ]));
        } else {
            $jsAction = '
                var pslDocument = window.opener ? window.opener.document : document;
                pslDocument.getElementById("pslogin-login-referer").value = "'.htmlspecialchars(base64_encode($redirectUrl)).'";
                pslDocument.getElementById("pslogin-login-submit").click();
            ';

            $body = $this->_jsWrap('if(window.opener && window.opener.location &&  !window.opener.closed) { window.close(); }; '.$jsAction.';');
            $this->getResponse()->setBody($body);
        }
        $session->unsPsloginLog();
    }
}
