<?php

namespace Plumrocket\ProductFilter\Helper;

use \Magento\Catalog\Model\Product\ProductList\Toolbar;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{

    //Default filter separator
    const FILTER_PARAM_SEPARATOR = '-';

    const USE_SEO_URL_CONFIG_PATH = 'general/seo_url';

    /**
     * Toolbar variables
     * @var array
     */
    protected $_toolbarVars = [
        Toolbar::PAGE_PARM_NAME,
        Toolbar::ORDER_PARAM_NAME,
        Toolbar::DIRECTION_PARAM_NAME,
        Toolbar::MODE_PARAM_NAME,
        Toolbar::LIMIT_PARAM_NAME
    ];

    /**
     * Data helper
     * @var \Plumrocket\ProductFilter\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Array of selected params
     *
     * @var null | array
     */
    protected $selectedParams = [];

    /**
     * Url constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data                                  $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Plumrocket\ProductFilter\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Use seo friendly url
     * @return boolean
     */
    public function useSeoFriendlyUrl()
    {
        return (bool)$this->_dataHelper->getUseSeoFriendlyUrl();
    }

    /**
     * Retrieve SEO friendly url for current item
     * @param  string $code
     * @param  string $value
     * @param  boolean $removeCurrent Removing current value with this parameter
     * @return string
     */
    public function getUrlForItem($code, $value, $removeCurrent = false)
    {
        $value = urlencode($value);
        $currentUrl = $this->_urlBuilder->getCurrentUrl();

        if ($removeCurrent) {
            $currentUrl = $this->removeParamsFromUrl($currentUrl, $code);
        }

        $delimiters = [];
        if ($this->getCategoryUrlSufix()) {
            $delimiters[] = $this->getCategoryUrlSufix();
        }
        $delimiters[] = '?';

        $selectedParams = $this->getSelectedParams();
        foreach ($delimiters as $delimiter) {
            $_parsed = explode($delimiter, $currentUrl);

            if (count($_parsed) < 2) {
                continue;
            }

            $currentPath = trim($_parsed[0], '/');
            $url = $currentPath . '/' . $code . $this->getFilterParamSeparator() . $value;

            // get sorted params
            $params = [];
            foreach ($selectedParams as $param => $paramValue) {
                $str = $param . $this->getFilterParamSeparator() . (
                    $removeCurrent && $param == $code
                        ? $value
                        : $paramValue
                    );
                if (strpos($currentPath, $str) !== false || $param == $code) {
                    $params[$param] = $str;
                }
            }

            // check if url params sorted
            if (count($params)) {
                asort($params);
                $imploded = implode('/', $params);
                if (strpos($url, $imploded) === false) {
                    $url = $this->removeParamsFromUrl($url, array_keys($params));
                    $url .= '/' . $imploded;
                }
            }

            $url .= $delimiter . $_parsed[1];
            return $url;
        }

        $currentPath = trim($currentUrl, '/');
        $url = $currentPath . '/' . $code . $this->getFilterParamSeparator() . $value;

        return $url;
    }

    /**
     * Remove params from url string
     *
     * @param string         $currentUrl
     * @param array | string $codes
     * @return mixed
     */
    protected function removeParamsFromUrl($currentUrl, $codes)
    {
        if (is_array($codes)) {
            foreach ($codes as $code) {
                $currentUrl = $this->removeParamsFromUrl($currentUrl, $code);
            }
        } else {
            $currentUrl = preg_replace("/(\/".$codes."-[[:alnum:]]+)(\/|\.|\?|$|#)/i", '$2', $currentUrl);
        }
        return $currentUrl;
    }

    /**
     * Retrieve reset url
     * @param  string $code
     * @param  string $value
     * @return string
     */
    public function getResetUrl($code, $value)
    {
        $value = html_entity_decode(urlencode($value));
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $urlParam = DIRECTORY_SEPARATOR . $code . $this->getFilterParamSeparator() . $value;
        $url = str_replace($urlParam, '', $currentUrl);
        return $url;
    }

    /**
     * Retrieve clear all url
     * @param  \Magento\Catalog\Model\Layer $layer
     * @return string
     */
    public function getClearAllUrl($layer)
    {
        $url = $this->_urlBuilder->getCurrentUrl();
        foreach ($layer->getState()->getFilters() as $item) {
            $code = $item->getFilter()->getRequestVar();
            $url = preg_replace("/(".$code."-[[:alnum:]]+)(?:\/|\?|$|#)/i", '', $url);
        }

        return $url;
    }

    /**
     * Retrieve filter param separator
     * @return string
     */
    public function getFilterParamSeparator()
    {
        //This can be rewrited or added new functionality
        return self::FILTER_PARAM_SEPARATOR;
    }

    /**
     * Rertrieve canonical url
     * @return string
     */
    public function getCanonicalUrl()
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $parts = explode('?', $currentUrl);
        return $parts[0];
    }

    /**
     * Retrieve toolbar vars
     * @return array
     */
    public function getToolbarVars()
    {
        return $this->_toolbarVars;
    }

    /**
     * Retrieve filter value
     * @return string
     */
    public function getValueByFilter($filter)
    {
        if ($this->useSeoFriendlyUrl()) {

            if ($filter->getFilter()->getRequestVar() != 'price' && $filter->getFilter()->getRequestVar() != 'cat') {
                $value = $this->_dataHelper->getConvertedAttributeValue($filter->getLabel());
            } else {
                $value = $this->_dataHelper->getConvertedAttributeValue($filter->getValue());
            }
        } else {
            $value = $filter->getValue();
        }

        if (is_array($value)) {
            $value = implode('-', $value);
        }

        return $value;
    }

    /**
     * Retrieve category url sufix
     * @return string
     */
    public function getCategoryUrlSufix()
    {
        return (string)$this->_dataHelper->getConfig(\Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX);
    }

    /**
     * Check url
     * If enabled seo friendly urls, then add sufix to the end of url
     * @param  string $url
     * @return string
     */
    public function checkUrl($url)
    {
        if ($this->useSeoFriendlyUrl()) {

            $sufix = $this->getCategoryUrlSufix();

            $currentUrl = $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            $_url = explode(str_replace($sufix, '', $currentUrl), $url);
            if (!empty($_url[1])) {
                $_urlParts = explode('/', str_replace($sufix, '', $_url[1]));
                sort($_urlParts);
                $url = $currentUrl . implode('/', $_urlParts);
            } else {
                $_url = explode('/result/', $url);
                if (!empty($_url[1])) {
                    $_url2 = explode($sufix ? $sufix : '?', $_url[1]);
                    $_urlParts = explode('/', $_url2[0]);
                    sort($_urlParts);
                    $url = str_replace($_url2[0], implode('/', $_urlParts), $url);
                }
            }

            if ($sufix && strpos($url, $sufix) !== false) {
                $url = str_replace($sufix, '', $url);
                $p = strpos($url, '?');
                if ($p !== false) {
                    $url = substr($url, 0, $p) . $sufix . substr($url, $p);
                } else {
                    $url .= $sufix;
                }
            }
        }

        return $url;
    }

    /**
     * Retrieve current selected parameters
     * @return array
     */
    public function getSelectedParams()
    {
        return $this->selectedParams;
    }

    /**
     * @param array $parts
     * @return array|null
     */
    public function setSourceFilterParts(array $parts)
    {
        if (!$this->selectedParams) {
            foreach ($parts as $part) {
                list($code, $value) = explode($this->getFilterParamSeparator(), $part);
                $this->selectedParams[$code] = $value;
            }
        }

        return $this->selectedParams;
    }
}
