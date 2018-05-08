var config = {
    deps: [
        "Wagento_Subscription/js/subscription"
    ],
    "map": {
        "*": {
            'Magento_Checkout/js/model/shipping-save-processor/default': 'Wagento_Subscription/js/model/shipping-save-processor/default'
        }
    }
};