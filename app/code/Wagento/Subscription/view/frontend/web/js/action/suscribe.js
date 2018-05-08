/*global define,alert*/
define(
    [
        'ko',
        'jquery',
        'mage/storage',
        'mage/translate',
        'Wagento_Subscription/js/model/subscription-popup'
    ],
    function (
        ko,
        $,
        storage,
        $t,
        subscription
    ) {
        'use strict';
        return function (subscriptionData) {
            var parseData = ko.toJSON(subscriptionData);
            var sentData = JSON.stringify(parseData);
            return storage.post(
                'subscription/ajax/suscribe',
                sentData,
                false
            ).done(
                function (response) {
                    if (response.status == 'success') {
                        $('body').trigger('processStart');
                        alert(response.message);
                        subscription.closeModal();
                        window.location.reload(true);
                    } else {
                        $('body').trigger('processStart');
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
