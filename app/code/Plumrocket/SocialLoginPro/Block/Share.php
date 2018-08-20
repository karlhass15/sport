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

namespace Plumrocket\SocialLoginPro\Block;

class Share extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \Plumrocket\SocialLoginPro\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $page;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var array
     */
    protected $buttonTypes = [
                            'facebook',
                            'twitter',
                            'google_plusone_share' => 'Google+',
                            'linkedin' => 'LinkedIn',
                            'pinterest',
                            'amazonwishlist' => 'Amazon',
                            'vk' => 'Vkontakte',
                            'odnoklassniki_ru' => 'Odnoklassniki',
                            'mymailru' => 'Mail',
                            'blogger',
                            'delicious',
                            'wordpress',
                            'email',
                            'addthis' => 'AddThis'
                        ];

    /**
     * Share constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Store\Model\Store                       $store
     * @param \Plumrocket\SocialLoginPro\Helper\Data           $dataHelper
     * @param \Magento\Cms\Helper\Page                         $page
     * @param \Magento\Cms\Model\Template\FilterProvider       $filterProvider
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\Store $store,
        \Plumrocket\SocialLoginPro\Helper\Data $dataHelper,
        \Magento\Cms\Helper\Page $page,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,

        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->store = $store;
        $this->dataHelper = $dataHelper;
        $this->page = $page;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    public function getHelper()
    {
        return $this->dataHelper;
    }

    public function showPopup()
    {
        //return $this->getHelper()->showPopup() && $this->getHelper()->shareEnabled();
        return $this->getHelper()->shareEnabled();
    }

    public function getButtonTypes()
    {
        if (!$this->hasData('button_types')) {
            $this->setData('button_types', $this->buttonTypes);
        }
        return $this->getData('button_types');
    }

    public function getButtons()
    {
        $buttons = [];
        foreach ($this->getButtonTypes() as $key1 => $key2) {
            $key = (!is_numeric($key1)) ? $key1 : $key2;
            $title = ucfirst($key2);

            $buttons[] = ['key' => $key, 'title' => $title];
        }

        return $buttons;
    }

    public function getPageUrl()
    {
        $pageUrl = null;
        $shareData = $this->getHelper()->getShareData();

        switch ($shareData['page']) {
            case '__custom__':
                $pageUrl = $shareData['page_link'];
                if (!$this->getHelper()->isUrlInternal($pageUrl)) {
                    $pageUrl = $this->store->getBaseUrl() . $pageUrl;
                }
                break;

            case '__invitations__':
                if ($this->getHelper()->moduleInvitationsEnabled()) {
                    $pageUrl = $this->objectManager->get('Plumrocket\Invitations\Helper\Data')->getRefferalLink();
                } else {
                    $pageUrl = $this->store->getBaseUrl();
                }
                break;

            default:
                if (is_numeric($shareData['page'])) {
                    $pageUrl = $this->page->getPageUrl($shareData['page']);
                }
        }

        // Disable addsis analytics anchor.
        $pageUrl .= '#';

        return $pageUrl;
    }

    public function getTitle()
    {
        $shareData = $this->getHelper()->getShareData();
        return $shareData['title'];
    }

    public function getDescription()
    {
        $process = $this->filterProvider->getPageFilter();
        $shareData = $this->getHelper()->getShareData();
        return $process->filter($shareData['description']);
    }

    public function getJsLayout()
    {
        if ($this->jsLayout) {
            $config = &$this->jsLayout['components']['pslogin-sharepopup']['config'];
            $config['title'] = $this->getTitle();
            $config['description'] = $this->getDescription();
            $config['url'] = $this->getPageUrl();
            $config['buttons'] = $this->getButtons();
        }

        return parent::getJsLayout();
    }
}
