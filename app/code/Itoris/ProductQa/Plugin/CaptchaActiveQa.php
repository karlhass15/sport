<?php
namespace Itoris\ProductQa\Plugin;
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
class CaptchaActiveQa

{
    protected $aviableId=['itoris_answer_captcha','itoris_qa_add_question'];
    protected $thisFormId;
    protected $dataHelper;
    public function __construct(
        \Itoris\ProductQa\Helper\Data $dataHelper
    ){
        $this->dataHelper = $dataHelper;
    }
    public function aroundGetCaptcha($subject, \Closure $proceed,$formId)
    {
        if(in_array($formId,$this->aviableId)) {
            $this->thisFormId = $formId;
        }
        return $proceed($formId);
    }
    public function aroundGetConfig($subject, \Closure $proceed,$key, $store = null)
    {

        if(in_array($this->thisFormId,$this->aviableId) && $this->dataHelper->isEnabled()
            && $this->dataHelper->getSettings($this->dataHelper->getStoreManager()->getStore()->getId())->getCaptchaInFront()){
            $returnValue = $proceed($key,$store);
            if(is_array($returnValue)){
                $returnValue = array_unshift($returnValue,$this->thisFormId);
                return $returnValue;
            }
                if($returnValue!='default' && $returnValue!=is_numeric($returnValue)){
                    $returnValue=$returnValue.','.$this->thisFormId;
                    $this->thisFormId=null;
                    return $returnValue;

                }
        }
        return $proceed($key,$store);

    }
}