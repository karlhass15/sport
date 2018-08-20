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
 * @package     Plumrocket_Amp
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Amp\Helper;
use Plumrocket\Amp\Library\Mobile\Detect;

class Data extends Main
{
    /**
     * Section name for configs
     */
    const SECTION_ID = 'pramp';

    /**
     * Default constants values for module
     */
    const MODULE_LOG_PREFIX = 'Plumrocket_Amp::';
    const AMP_HOME_PAGE_KEYWORD = 'amp_homepage';
    const AMP_FOOTER_LINKS_KEYWORD = 'amp_footer_links';

    const AMP_ONLY_OPTIONS_KEYWORD = 'only-options';
    const AMP_ROOT_TEMPLATE_NAME_1COLUMN = '1column_amp';

    const AMP_ROOT_TEMPLATE_NAME_OPTIONS = '1column-options';

    const DEFAULT_ACCESS_CONTROL_ORIGIN = 'cdn.ampproject.org';
    const AMP_DEFAULT_IFRAME_PATH = 'ampiframe.php';

    /**
     * Checking request
     * @var bool
     */
    protected $_isAmpRequest;

    /**
     * [$_ignorePath description]
     * @var string
     */
    protected $_ignorePath;

    /**
     * Client device is mobile
     * @var bool
     */
    protected $_isMobile;

    /**
     * Client device is table
     * @var bool
     */
    protected $_isTablet;

    /**
     * Use Mobile Detect Library
     * @var \Plumrocket\Amp\Library\Mobile\Detect
     */
    protected $_mobileDetected;

    /**
     * Use for getBaseUrl
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Used in Processing HTTP-Headers for cross domain requests
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * Form Key for getAddToCart method
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $config;

    /**
     * needed for disable modules
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * needed for Plumrocket Base and for function "getConfigPath"
     * @var array
     */
    protected $_allowedPages;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Response\Http       $response
     * @param \Magento\Framework\Data\Form\FormKey       $formKey
     * @param \Magento\Config\Model\Config               $config
     * @param \Magento\Framework\App\ResourceConnection  $resourceConnection
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Config\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->storeManager         = $storeManager;
        $this->response             = $response;
        $this->formKey              = $formKey;
        $this->resourceConnection   = $resourceConnection;
        $this->config               = $config;
        $this->_configSectionId     = self::SECTION_ID;
        parent::__construct($objectManager, $context);
    }

    /**
     * Retrieve allowed full action names
     * @param  int $store
     * @return array
     */
    public function getAllowedPages($store = null)
    {
        if ($this->_allowedPages === null) {
            $this->_allowedPages = explode(',', $this->getConfig(self::SECTION_ID . '/general/pages', $store));

            if (in_array('catalogsearch_result_index', $this->_allowedPages)) {
                $this->_allowedPages[] = 'pramp_search_index';
            }

            $this->_allowedPages[] = 'turpentine_esi_getBlock';
        }

        return $this->_allowedPages;
    }

    /**
     * Is current page allowed
     * @return boolean
     */
    public function isAllowedPage()
    {
        return in_array($this->getFullActionName(), $this->getAllowedPages());
    }

    /**
     * Get full name of action
     * @return string
     */
    public function getFullActionName()
    {
        if (!$this->_request) {
            return '__';
        }

        return $this->_request->getFullActionName();
    }

    /**
     * @return boolean
     */
    public function isEsiRequest()
    {
        return $this->getFullActionName() == 'turpentine_esi_getBlock';
    }

    /**
     * Get config value
     * @return boolean
     */
    public function isSearchEnabled()
    {
        return in_array('catalogsearch_result_index', $this->getAllowedPages());
    }

    /**
     * Is module enabled
     * @param  int $store
     * @return boolean
     */
    public function moduleEnabled($store = null)
    {
        return (bool)$this->getConfig(self::SECTION_ID . '/general/enabled', $store);
    }

    /**
     * Get config value
     * @param  int Store Identifier
     * @return bool
     */
    public function forceOnMobile($store = null)
    {
        return (bool)$this->getConfig(self::SECTION_ID . '/general/force_mobile', $store);
    }

    /**
     * Get config value
     * @param  int Store Identifier
     * @return bool
     */
    public function forceOnTablet($store = null)
    {
        return (bool)$this->getConfig(self::SECTION_ID . '/general/force_tablet', $store);
    }

    /**
     * Is AMP the current request
     * @return bool
     */
    public function isAmpRequest()
    {
        if ($this->_isAmpRequest === null) {
            if (!$this->moduleEnabled()) {
                return $this->_isAmpRequest = false;
            }

            if (!$this->isAllowedPage()) {
                if ($this->getFullActionName() == '__') {
                    return false;
                } else {
                    return $this->_isAmpRequest = false;
                }
            }

            if ($this->_request->getParam(self::AMP_ONLY_OPTIONS_KEYWORD) == 1) {
                return $this->_isAmpRequest = false;
            }

            if ($this->_request->getParam('noforce') == 1) {
                return false;
            }

            if ($this->_request->getParam('amp') == 1) {
                return $this->_isAmpRequest = true;
            }

            $forceOnMobile = $this->forceOnMobile();
            if ($forceOnMobile) {
                $forceOnTablet = $this->forceOnTablet();
                $isMobile = $this->isMobile();
                $isTablet = $this->isTablet();
                if ($isMobile && !$isTablet) {
                    $this->_isAmpRequest = true;
                } elseif ($forceOnTablet && $isTablet) {
                    $this->_isAmpRequest = true;
                }
            }
        }
        return $this->_isAmpRequest;
    }

    /**
     * @return boolean
     */
    public function isMobile()
    {
        $this->_detectMobile();
        return $this->_isMobile;
    }

    /**
     * @return boolean
     */
    public function isTablet()
    {
        $this->_detectMobile();
        return $this->_isTablet;
    }

    /**
     * Detect current device
     * @return void
     */
    protected function _detectMobile()
    {
        if (!$this->_mobileDetected) {
            $mobileDetect = new \Plumrocket\Amp\Library\Mobile\Detect();
            $this->_isMobile = $mobileDetect->isMobile();
            $this->_isTablet = $mobileDetect->isTablet();
            $this->_mobileDetected = true;
        }
    }

    /* HERE */
    public function setAmpRequest($value)
    {
        $this->_isAmpRequest = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     * Return true if module enabled and exist request param only-options
     */
    public function isOnlyOptionsRequest()
    {
        return $this->moduleEnabled()
            && ($this->_request->getParam('only-options') == 1)
            && ($this->getFullActionName() == 'catalog_product_view');
    }

    /**
     * @param  string $url
     * @return array $urlData
     */
    protected function _parseUrl($url)
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        $url = $url ? $url : $this->_urlBuilder->getCurrentUrl();
        $urlData = parse_url($url);

        if (isset($urlData['query'])) {
            parse_str($urlData['query'], $dataQuery);
            $urlData['query'] = $dataQuery;
        } else {
            $urlData['query'] = [];
        }

        $urlData['fragment'] = isset($urlData['fragment']) ? $urlData['fragment'] : '';

        return $urlData;
    }

    /**
     * @param  array $urlData
     * @param  array $params
     * @return array $urlData
     */
    protected function _mergeUrlParams($urlData, $params)
    {
        if (is_array($params) && count($params)) {
            if (isset($params['_secure'])) {
                $urlData['_secure'] = (bool)$params['_secure'];
                unset($params['_secure']);
            }

            $urlData['query'] = array_merge($urlData['query'], $params);
        }

        return $urlData;
    }

    /**
     * Retrieve port component from URL data
     * @param  array $urlData
     * @return string
     */
    protected function _getPort($urlData)
    {
        return !empty($urlData['port']) ? (':' . $urlData['port']) : '';
    }

    /**
     * String location without amp parameter
     * @return string
     */
    public function getCanonicalUrl($url = null, $params = null)
    {
        $urlData = $this->_mergeUrlParams($this->_parseUrl($url), $params);

        if (isset($urlData['query']['amp'])) {
            unset($urlData['query']['amp']);
        }

        if (isset($urlData['_secure'])) {
            $urlData['scheme'] = 'https';
        }

        $paramsStr = count($urlData['query'])
            ? '?' . urldecode(http_build_query($urlData['query']))
            : '';

        if (!empty($urlData['fragment'])) {
            $paramsStr .= '#' . $urlData['fragment'];
        }

        return $urlData['scheme'] . '://' . $urlData['host'] . $this->_getPort($urlData) . $urlData['path'] . $paramsStr;
    }

    /**
     * Disable extension
     * @return void
     */
    public function disableExtension()
    {
        $connection = $this->resourceConnection->getConnection('core_write');
        $connection->delete($this->resourceConnection->getTableName('core_config_data'),
            [$connection->quoteInto('path = ?', self::SECTION_ID.'/general/enabled')]
        );

        $this->config->setDataByPath(self::SECTION_ID.'/general/enabled', 0);
        $this->config->save();
    }

    /**
     * String location with amp parameter
     * @return string
     */
    public function getAmpUrl($url = null, $params = null)
    {
        $urlData = $this->_mergeUrlParams($this->_parseUrl($url), $params);

        if (!isset($urlData['query']['amp'])) {
            $urlData['query'] = array_merge(['amp' => 1], $urlData['query']);
        }

        if (isset($urlData['_secure'])) {
            $urlData['scheme'] = 'https';
        }

        $paramsStr = count($urlData['query'])
            ? '?' . urldecode(http_build_query($urlData['query']/*, null, '&amp'*/))
            : '';

        if (!empty($urlData['fragment'])) {
            $paramsStr .= '#' . $urlData['fragment'];
        }

        return $urlData['scheme'] . '://' . $urlData['host'] . $this->_getPort($urlData) . $urlData['path'] . $paramsStr;
    }

    /**
     * Retrieve add to cart url by product
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartUrl($product)
    {
        if ($product && $productId = $product->getId()) {
            $addToCartUrl = $this->_urlBuilder->getUrl(
                    self::SECTION_ID . '/cart/add', [
                        'product' => $product->getId(),
                        'form_key'=>$this->formKey->getFormKey(),
                        '_secure'=>true
                    ]
                );

            return $this->getCanonicalUrl($addToCartUrl);
        }

        return '#';
    }

    /**
     * @var object \Magento\Catalog\Model\Product
     * @var string $store
     * @return string add to cart url
     */
    public function getIframeSrc($product, $store = null)
    {
        $secure = $this->getConfig('web/secure/use_in_frontend', $store);
        $ampIframePath = $this->getAmpIframePath();

        if ($secure && $ampIframePath && ($productUrl = $this->getOnlyOptionsUrl($product))) {
            $ampIframeUrlData = parse_url($productUrl);
            $prefix = 'www.';
            $ampIframeUrlData['host'] = (strpos($ampIframeUrlData['host'], $prefix) === 0)
                ? substr($ampIframeUrlData['host'], strlen($prefix))
                : $prefix . $ampIframeUrlData['host'];

            return 'https://' . $ampIframeUrlData['host'] . $this->_getPort($ampIframeUrlData) . '/' . $ampIframePath . '?referrer=' . base64_encode($productUrl);
        }

        return false;
    }

    /**
     * Retrieve url for review product post action
     * @param  \Magento\Catalog\Model\Product $product
     * @return string secure url
     */
    public function getActionForReviewForm($productId)
    {
        return $this->_urlBuilder->getUrl(
            'pramp/review_product/post',
            [
                '_secure' => true,
                'id' => $productId,
            ]
        );
    }

    /**
     * Retrieve base URL for current store
     * @return string URL Link
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @deprecated No longer used by internal code and not recommended.
     * @deprecated Please use sanitizeHttpHeaders for prepare HTTP headers
     * @var void
     * @return null
     */
    public function removeSameOrigin()
    {
        return null;
    }

    /**
     * Processing HTTP-Headers for cross domain requests
     * Setting additional headers for same-origin and cross-origin requests
     * according to https://github.com/ampproject/amphtml/blob/master/spec/amp-cors-requests.md
     * @return void
     */
    public function sanitizeHttpHeaders()
    {
        $sourceOrigin = $this->_request->getParam('__amp_source_origin');

        if (!$sourceOrigin) {
            $urlData  = parse_url($this->getBaseUrl());

            if (!empty($urlData['scheme']) && !empty($urlData['host'])) {
                $sourceOrigin = $urlData['scheme'] . '://' . $urlData['host'] . $this->_getPort($urlData);
            }
        }

        $this->response->setHeader(
                'Access-Control-Allow-Origin',
                $this->getAccessControlOrigin(),
                true)
            ->setHeader(
                'AMP-Access-Control-Allow-Source-Origin',
                $sourceOrigin,
                true)
            ->setHeader('Access-Control-Expose-Headers', 'AMP-Access-Control-Allow-Source-Origin', true)
            ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS', true)
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token', true)
            ->setHeader('Access-Control-Allow-Credentials', 'true', true);
    }

    /**
     * @param  \Magento\Catalog\Model\Product
     * @return string|bool
     */
    public function getOnlyOptionsUrl($product)
    {
        if ($product) {
            $productUrl = (!$product->getProductUrl())
            ? $this->_urlBuilder->getUrl('catalog/product/view', ['id' => $product->getId()])
            : $product->getProductUrl();

            return $this->getCanonicalUrl($productUrl, [self::AMP_ONLY_OPTIONS_KEYWORD => 1, '_secure'=>true]);
        }

        return false;
    }

    /**
     * @param  string $url
     * @return string
     */
    public function getFormReturnUrl($url = null)
    {
        $params = ['_secure'=>true];

        if (!$this->_request->getParam(self::AMP_ONLY_OPTIONS_KEYWORD)) {
            $params[self::AMP_ONLY_OPTIONS_KEYWORD] = 1;
        }

        return $this->getCanonicalUrl($url, $params);
    }

    /**
     * Retrieve source origin for current  page publisher
     * @return string
     */
    public function getAccessControlOrigin()
    {
        /**
         * Base way to detecting
         * Detecting source origin by server variable HTTP_ORIGIN
         */
        if ($this->_request) {
            $httpOrigin = $this->_request->getServer('HTTP_ORIGIN');

            if ($httpOrigin) {
                return $httpOrigin;
            }
        }

        /**
         * Alternative way to detecting
         * Detecting source origin by magento base url
         */
        if ($baseUrl = $this->getBaseUrl()) {
            $urlData = parse_url($baseUrl);
            if (!empty($urlData['host'])) {
                return ('https://' . str_replace('.', '-', $urlData['host']) . '.' . self::DEFAULT_ACCESS_CONTROL_ORIGIN);
            }
        }

        /**
         * Return source origin by default
         */
        return 'https://' . self::DEFAULT_ACCESS_CONTROL_ORIGIN;
    }

    public function isSecure()
    {
        return $this->_request->isSecure();
    }

    public function isCookieRestrictionModeEnabled($store = null)
    {
        return (bool)$this->getConfig(\Magento\Cookie\Helper\Cookie::XML_PATH_COOKIE_RESTRICTION, $store);
    }

    /**
     * Retrieve logo width
     * @param  string $store
     * @return int
     */
    public function getLogoWidth($store = null)
    {
        return (int)$this->getConfig(self::SECTION_ID . '/product_page_add_logo/logo_width', $store);
    }

    /**
     * Retrieve logo height
     * @param  string $store
     * @return int
     */
    public function getLogoHeight($store = null)
    {
        return (int)$this->getConfig(self::SECTION_ID . '/product_page_add_logo/logo_height', $store);
    }

    /**
     * Retrieve logo src attribute
     * @param  string $store
     * @return int
     */
    public function getLogoSrc($store = null)
    {
        $logo = $this->getConfig(self::SECTION_ID . '/product_page_add_logo/logo', $store);
        if ($logo) {
           $logo = $this->_urlBuilder->getUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::SECTION_ID . '/logo/' . $logo;
        }

        return (string)$logo;
    }

    /**
     * Retrieve amp-iframe path
     * @param  string $store
     * @return string
     */
    public function getAmpIframePath($store = null)
    {
        if ($this->getConfig(self::SECTION_ID . '/general/amp_option_iframe', $store)) {
            $path = (string)$this->getConfig(self::SECTION_ID . '/general/amp_iframe_path', $store);

            return $path ? trim($path, '/') : self::AMP_DEFAULT_IFRAME_PATH;
        }

        return false;
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getNavigationsTextColor($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/front_design/navigation_menu_text_color', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getLinkColor($store = null)
    {
       return  (string)$this->getConfig(self::SECTION_ID . '/front_design/link_color', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getLinkColorHover($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/front_design/link_color_hover', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getButtonBgColor($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/front_design/button_bg_color', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getButtonBgColorHover($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/front_design/button_bg_color_hover', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getButtonTextColor($store = null)
    {
        return (string) $this->getConfig(self::SECTION_ID . '/front_design/button_text_color', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function isEnabledHeaderSearch($store = null)
    {
        return (string) $this->getConfig($this->_configSectionId . '/front_design/header_search_enabled', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getButtonTextColorHover($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/front_design/button_text_color_hover', $store);
    }

    /**
     * Retrieve design setting
     * @param  string $store
     * @return string
     */
    public function getPriceTextColor($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/front_design/price_text_color', $store);
    }

    public function getRtlEnabled($store = null)
    {
        return (bool)$this->getConfig(self::SECTION_ID . '/rtl/enabled', $store);
    }

    /**
     * Retrieve social sharing status
     * @param  string $store
     * @return bool $status
     */
    public function getSocialSharingEnabled($store = null)
    {
        return (bool)$this->getConfig(self::SECTION_ID . '/social/sharing_enabled', $store);
    }

    /**
     * Retrieve social sharing buttons
     * @param  string $store
     * @return bool $status
     */
    public function getActiveShareButtons($store = null)
    {
        return (string)$this->getConfig(self::SECTION_ID . '/social/share_button', $store);
    }

    /**
     * Retrieve social sharing
     * @param  string $store
     * @return bool $status
     */
    public function getShareButtonFacebookAppID($store = null)
    {
        return (string)$this->getConfig(self::SECTION_ID . '/social/share_button_facebook_app_id', $store);
    }

    /**
     * Retrieve Google Tag Snippet setting
     * @param  string $store
     * @return string
     */
    public function getGoogleTagCode($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/tag_manager/tag_manager_snippet', $store);
    }

    /**
     * Retrieve is zopim enabled setting
     * @param  string $store
     * @return string
     */
    public function getZopimEnabled($store = null)
    {
        return  (bool)$this->getConfig(self::SECTION_ID . '/zopim/zopim_enabled', $store);
    }

    /**
     * Retrieve zopim key setting
     * @param  string $store
     * @return string
     */
    public function getZopimKey($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/zopim/zopim_key', $store);
    }

    /**
     * Retrieve zopim button label setting
     * @param  string $store
     * @return string
     */
    public function getZopimButtonLabel($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/zopim/zopim_button_label', $store);
    }

    /**
     * Retrieve zopim button background color setting
     * @param  string $store
     * @return string
     */
    public function getZopimButtonBgColor($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/zopim/zopim_button_bg_color', $store);
    }

    /**
     * Retrieve zopim button text color setting
     * @param  string $store
     * @return string
     */
    public function getZopimButtonTextColor($store = null)
    {
        return  (string)$this->getConfig(self::SECTION_ID . '/zopim/zopim_button_text_color', $store);
    }

}
