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
        getTradeStatus: function (id) {
            getTradeStatus(id);
        }
    };

    /**
     * 查询支付状态
     * @param id
     */
    function getTradeStatus(id) {
        var getTradeStatusInterval = setInterval(function () {
            $.get("/trade/trade/query?id=" + id, function (result) {
                if (result.status == 'success') {
                    clearInterval(getTradeStatusInterval);
                    window.location.href = "/trade/trade/return?id=" + id;
                }
            });
        }, 3000);
    }

    return pub;
})(window.jQuery);