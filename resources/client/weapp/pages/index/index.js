var app = getApp();
Page({
  data: {
    hidden: false,
    searchSize: '20',
    searchColor: 'rgba(255,255,255,1)',
    classifyUrl: "../../res/images/fenlei.png",
    indexGoods: [],
    orderList: {},
    //轮播图
    banner: [
      {
        "pic": 'http://shop.ectouch.cn/mini-program/data/attached/afficheimg/1475869586530689271.png',
        "link": "../product_list/product_list?id=3",
      },
    ],
    indicatorDots: true,
    autoplay: true,
    interval: 4000,
    duration: 1000,
  },

  onLoad: function () {
    var that = this
    //推荐商品列表
    var token = wx.getStorageSync('token')
    wx.request({
      url: app.apiUrl('ecapi.site.get'),
      data: {
        per_page: "8",
        page: "1"
      },
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      method: "POST",
      success: function (res) {
        that.setData({
          indexGoods: res.data
        })

      }
    })
    //加载中
    this.loadingChange()
  },
  loadingChange() {
    setTimeout(() => {
      this.setData({
        hidden: true
      })
    }, 1000)
  },
  //
  siteDetail: function (e) {
    var that = this
    //获取点击的id值
    var index = e.currentTarget.dataset.index;
    var goodsId = that.data.indexGoods[index].goods_id;

    wx.navigateTo({
      url: "../goods/index?objectId=" + goodsId
    });
  },
  //下拉刷新完后关闭
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
  // 搜索链接
  bindSearchTap: function () {
    wx.navigateTo({
      url: '../search/index'
    })
  },
  // 分类链接
  bindCateTap: function () {

    wx.switchTab({
      url: '../category/index'
    })
  },
    goTop: function (e) {
    this.setData({
      scrollTop: 0
    })
  },
  scroll: function (e) {
    if (e.detail.scrollTop > 500) {
      this.setData({
        floorstatus: true
      });
    } else {
      this.setData({
        floorstatus: false
      });
    }
  },
  onShareAppMessage: function () {
    return {
      title: '小程序首页',
      desc: '小程序本身无需下载，无需注册，不占用手机内存，可以跨平台使用，响应迅速，体验接近原生App',
      path: '/pages/index/index'
    }
  }
})