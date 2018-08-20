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

class DoUse extends \Plumrocket\SocialLoginPro\Controller\AbstractAccount
{

    public function execute()
    {
        $session = $this->_getSession();
        $session->unsPsloginLog();
        if ($session->isLoggedIn() && !$this->getRequest()->getParam('call')) {
            return $this->_windowClose();
        }

        $type = $this->getRequest()->getParam('type');

        $model = $this->getModelByType($type);

        if (!$this->_getHelper()->moduleEnabled() || !$model->enabled()) {
            return $this->_windowClose();
        }

        if ($call = $this->getRequest()->getParam('call')) {
            $this->_getHelper()->apiCall([
                'type'      => $type,
                'action'    => $call,
            ]);
        } else {
            $this->_getHelper()->apiCall(null);
        }

        // Set current store.
        $currentStoreId = $this->storeManager->getStore()->getId();
        if ($currentStoreId) {
            $this->_getHelper()->refererStore($currentStoreId);
        }

        // Set redirect url.
        if ($referer = $this->_getHelper()->getCookieRefererLink()) {
            $this->_getHelper()->refererLink($referer);
        }

        switch ($model->getProtocol()) {
            case 'OAuth':
                if ($link = $model->getProviderLink()) {
                    return $this->_redirect($link);
                } else {
                    if ($this->_getHelper()->getDebugMode()) {
                        $model->recordLog();
                        $this->displayError($model);
                        return;
                    } else {
                        $this->getResponse()->setBody(__('The Login Application was not configured correctly. If your are the admin of store: Please activate “Enable Logging” in Magento Login Extension and try again to see error details.'));
                    }
                }
                break;

            case 'OpenID':
                try {
                    $errorText = null;

                    $profile = new \Zend_OpenId_Extension_Sreg([
                        'nickname'=>true,
                        'email'=>true,
                        'fullname'=>true,
                        'dob'=>true,
                        'gender'=>true,
                    ], null, 1.1);

                    $redirectUrl = $this->_getHelper()->getCallbackURL($model->getProvider());

                    if ($this->getRequest()->isPost()) {
                        $consumer = new \Zend_OpenId_Consumer();
                        $openid_id = $model->prepareIdentifier($this->getRequest()->getParam('openid_id'));
                        if (!$openid_id || !$consumer->login($openid_id, $redirectUrl, $redirectUrl, $profile)) {
                            $errorText = __('OpenID login failed');
                        }
                    }
                } catch (\Exception $e) {
                    $errorText = $e->getMessage();
                }

                $form = $this->layout->getBlockSingleton('Magento\Framework\View\Element\Template')
                    ->setTemplate('Plumrocket_SocialLoginPro::openid.phtml')
                    ->setProvider($model->getProvider())
                    ->setTitle($model->getTitle())
                    ->setErrorText($errorText)
                    ->toHtml();

                $this->getResponse()->setBody($form);
                break;

            case 'BrowserID':
            default:
                return $this->_windowClose();
        }
    }
}
