window.yii.trade = (function ($) {
    var pub = {
        isActive: true,
        init: function () {
            console.info('init trade.');
        },

        /**
         * 查询支付单状态
         * @param id
         */
        getPaymentStatus: function (id) {
            $.get("/trade/trade/query?id=" + id, function (result) {
                if (result.status == 'success') {
                    window.location.href = "/trade/trade/return?id=" + id;
                }
            });
        }
    };
    return pub;
})(window.jQuery);