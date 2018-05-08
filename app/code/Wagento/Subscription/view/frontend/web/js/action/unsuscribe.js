/*global define,alert*/
define(
    [
        'ko',
        'jquery',
        'mage/storage',
        'mage/translate',
        'Wagento_Subscription/js/model/subscription-popup',
        'Magento_Customer/js/customer-data'
    ],
    function (
        ko,
        $,
        storage,
        $t,
        subscription,
        customerData
    ) {
        'use strict';

        return function (subscriptionData) {
            var productData = customerData.get('cart')().items.find(function (item) {
                return Number(subscriptionData.productId) === Number(item['product_id']);
            });

            var parseData = ko.toJSON(productData);
            var sentData = JSON.stringify(parseData);

            return storage.post(
                'subscription/ajax/unsuscribe',
                sentData,
                false
            ).done(
                function (response) {
                    if (response.status == 'success') {
                        var cart = customerData.get('cart')().items;
                        console.log(cart['item_id']);
                        alert(response.message);
                        subscription.closeModal();
                        window.location.reload(true);
                    } else {
                        var cart = customerData.get('cart')().items;
                        console.log(customerData.get('cart')().items);
                        alert(response.message);
                        subscription.closeModal();
                        window.location.reload(true);
                    }
                }
            ).fail(
                function (error) {
                }
            );
        };
    }
);
