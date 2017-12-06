var app = getApp()
Page({
  data: {
    userHeader: '../../res/images/user-bg.png',
    userHeaderUrl: '../../res/images/header.jpg',
    orderJtou: '../../res/images/icon-arrowdown.png',
    userInfo: {},
    indexGoods: [],
    orderIcon: '../../res/images/order.png',
    userOrder: [
      {
        link: "../user_order/order?id=0",
        pic: "../../res/images/icon-order.png",
        cont: "全部订单",
        num: '1'
      },
      {
        link: "../user_order/order?id=1",
        pic: "../../res/images/icon-daifukuan.png",
        cont: "待付款",
        num: '1'
      },
      {
        link: "../user_order/order?id=2",
        pic: "../../res/images/icon-daifahuo.png",
        cont: "待收货",
        num: '1'
      },
    ],
    userLists: [
      {
        name: "我的收货地址",
        pic: "../../res/images/address.png",
        link: "../address/index?from=user"
      },
      {
        name: "我的帮助",
        pic: "../../res/images/help.png",
        link: "../help/help"
      }
    ],
  },



  onLoad: function () {
    var that = this

    // 获取用户数据
    app.getUserInfo(function (userInfo) {
      that.setData({
        userInfo: userInfo
      })
    })
    var token = wx.getStorageSync('token')
    wx.request({
      url: app.apiUrl('ecapi.site.get'),
      data: {
        per_page: "10",
        page: "1"
      },
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      method: "POST",
      success: function (res) {
        that.setData({
          indexGoods: res.data.goodsList
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
  siteDetail: function (e) {
    var that = this
    //获取点击的id值
    var index = e.currentTarget.dataset.index;
    console.log(index)
    var goodsId = that.data.indexGoods[index].goods_id;

    wx.navigateTo({
      url: "../goods/goods?objectId=" + goodsId
    });
  },
  bindOrder: function () {
    wx.navigateTo({
      url: "../user_order/order"
    })
  },
  //下拉刷新完后关闭
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
})
