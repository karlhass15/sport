define(
    [
        'Wagento_Subscription/js/view/checkout/summary/custom-initialfee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);