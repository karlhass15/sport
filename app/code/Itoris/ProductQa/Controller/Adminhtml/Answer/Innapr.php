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

namespace Itoris\ProductQa\Controller\Adminhtml\Answer;
class Innapr extends \Magento\Backend\App\Action
{
    protected $helper;
    protected $answerFactory;
    public function __construct( \Magento\Backend\App\Action\Context $context,\Itoris\ProductQa\Helper\Data $helper)
    {
        $this->helper=$helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $output=[];
        try {
            $con = $this->helper->getResourceConnection();
            $this->helper->getResourceConnection()->getConnection()->update($con->getTableName('itoris_productqa_answers'), ['inappr' => 0], ['id=?' => (int)$this->getRequest()->getParam('id')]);
            $output['success']= __('Answer has been updated');
            return $this->getResponse()->setBody(\Zend_Json::encode($output));
        }catch(\Exception $e){
            $output['error']=__('Answer has not been updated');
           return $this->getResponse()->setBody(\Zend_Json::encode($output));
        }



    }
}