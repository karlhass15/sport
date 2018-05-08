<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Sales\Subscription\View;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Wagento\Subscription\Model\SubscriptionSales;
use Wagento\Subscription\Helper\Data;

class SubscriptionDetails extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var SubscriptionSales
     */
    protected $subscriptionSales;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * SubscriptionDetails constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param SubscriptionSales $subscriptionSales
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        SubscriptionSales $subscriptionSales,
        Data $helper,
        array $data = []
    ) {
    
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->subscriptionSales = $subscriptionSales;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('sales_subscription');
        $customerId = $model->getCustomerId();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('salesSub_');
        $form->setFieldNameSuffix('salesSub');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Subscription Information')]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
        $fieldset->addField(
            'how_many',
            'text',
            [
                'name' => 'how_many',
                'label' => __('How Many'),
                'required' => false
            ]
        );
        $fieldset->addField(
            'next_renewed',
            'date',
            [
                'name' => 'next_renewed',
                'label' => __('Next Renewal Date'),
                'date_format' => 'dd-MM-yyyy',
                'required' => true
            ]
        );

        $fieldset->addField(
            'shipping_address_id',
            'select',
            [
                'name' => 'shipping_address_id',
                'label' => __('Shipping Address'),
                'required' => true,
                'values' => $this->helper->getCustomerAddressInline($customerId)
            ]
        );

        $fieldset->addField(
            'billing_address_id',
            'select',
            [
                'name' => 'billing_address_id',
                'label' => __('Billing Address'),
                'required' => true,
                'values' => $this->helper->getCustomerAddressInline($customerId)
            ]
        );

        $fieldset->addField(
            'public_hash',
            'select',
            [
                'name' => 'public_hash',
                'label' => __('Card Details'),
                'required' => true,
                'values' => $this->helper->getCardCollection($customerId)
            ]
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Subscription Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Subscription Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
