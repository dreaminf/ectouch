var app = getApp()
var url = app.apiUrl("ecapi.product.list");
var page = 1;
var per_page = 10;
var category = '';
var brand = 1;
var shop = 1;
var keyword = "";
var sort_key = "0";
var sort_value = "1";

// 获取数据的方法，具体怎么获取列表数据大家自行发挥
var GetList = function (that) {
    that.setData({
        hidden: false
    });
    wx.request({
        url: url,
        data: {
            page: page,
            per_page: per_page,
            category: category,
            brand: brand,
            shop: shop,
            keyword: keyword,
            sort_key: sort_key,
            sort_value: sort_value
        },
        method: "post",
        success: function (res) {
            var list = that.data.list;
            for (var i = 0; i < res.data.list.length; i++) {
                list.push(res.data.list[i]);
            }
            that.setData({
                list: list
            });
            page++;
            that.setData({
                hidden: true
            });
            if (sort_value == 1){
              sort_value=0
            }else{
              sort_value=1;
            }
        }
    });
}
Page({
    data: {
        loadingSize: '20',
        loadingColor: "#444444",
        current: "0",
        hidden: true,
        list: [],
        scrollTop: 0,
        scrollHeight: 0
    },
    onLoad: function (e) {
        category = e.id || ''
        keyword = e.content || ''
        //   这里要非常注意，微信的scroll-view必须要设置高度才能监听滚动事件，所以，需要在页面的onLoad事件中给scroll-view的高度赋值
        var that = this;
        wx.getSystemInfo({
            success: function (res) {
                //console.info(res.windowHeight);
                that.setData({
                    scrollHeight: res.windowHeight
                });
            }
        });
        //加载中
        this.loadingChange()
        page = 1;
        var that = this;
        GetList(that);
    },
    loadingChange() {
        setTimeout(() => {
            this.setData({
                hidden: true
            })
        }, 2000)
    },
    /*header*/
    bindHeaderTap: function (event) {
        this.setData({
            current: event.target.dataset.index,
        });
        page = 1;
        sort_key = event.target.dataset.index
        this.setData({
            list: [],
        });
        GetList(this)
    },
    goodsDetail: function (e) {
        var that = this
        //获取点击的id值
        var index = e.currentTarget.dataset.index;
        var goodsId = that.data.list[index].goods_id;

        wx.navigateTo({
            url: "../goods/goods?objectId=" + goodsId
        });
    },
    //下拉刷新完后关闭
    onPullDownRefresh: function () {
        wx.stopPullDownRefresh()
    },

    onShow: function () {
        //   在页面展示之后先获取一次数据

    },


    bindDownLoad: function () {
        //   该方法绑定了页面滑动到底部的事件
        var that = this;
        GetList(that);
    },
    scroll: function (event) {
        //   该方法绑定了页面滚动时的事件，我这里记录了当前的position.y的值,为了请求数据之后把页面定位到这里来。
        this.setData({
            scrollTop: event.detail.scrollTop,
        });
    },
    goToTop: function () { //回到顶部
        this.setData({
            scrollTop: 0
        })
    },
    refresh: function (event) {
        //   该方法绑定了页面滑动到顶部的事件，然后做上拉刷新
        // page = 1;
        this.setData({
            //list: [],
            scrollTop: 0
        });
        //GetList(this)
    },
    onShareAppMessage: function () {
        return {
            title: '商品列表',
            desc: '小程序本身无需下载，无需注册，不占用手机内存，可以跨平台使用，响应迅速，体验接近原生App',
            path: '/pages/product_list/product_list'
        }
    }

})