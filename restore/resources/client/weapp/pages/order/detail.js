var app = getApp()
var token
var orderId;
Page({
  data: {
    index: 0,
    orderJtou: '../../res/images/icon-arrowdown.png',
    distributionJtou: '../../res/images/icon-arrowdown.png',
    checkList: [],
  },
  onLoad: function (options) {
    orderId = options.objectId
    this.loadOrderDetail(orderId);
    //加载中
    this.loadingChange()
  },
  onShow: function () {
    token = wx.getStorageSync('token')
    this.loadOrderDetail(orderId)
  },
  loadOrderDetail: function (orderId) {
    var that = this

    //商品详细信息获取
    wx.request({
      url: app.apiUrl('ecapi.order.get'),
      data: {
        order: orderId,
      },
      method: "post",
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      success: function (res) {
        var attr;
        var temp = '';
        for (var i in res.data.order.goods) {
          attr = res.data.order.goods[i].goods_attr.split('\n')
          for (var j in attr) {
            if (attr[j] == '') continue;
            temp += attr[j] + ','
          }
          res.data.order.goods[i].goods_attr = temp.substring(0, temp.length - 1)
        }
        that.setData({
          goodsList: res.data.order.goods,
          orders: res.data.order
        })
      }
    })
  },
  //确认收货
  confirm_order: function (e) {
    wx.showModal({
      title: '提示',
      content: '确认收到商品？',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: app.apiUrl('ecapi.order.confirm'),
            data: {
              order: e.currentTarget.dataset.id
            },
            header: {
              'Content-Type': 'application/json',
              'X-ECTouch-Authorization': token
            },
            method: "POST",
            success: function (res) {
              if (res.data.error_code > 0) {
                error_msg = '确认失败'
              } else if (res.data.error_code == 0) {
                error_msg = '确认成功'
                that.orderStatus(that, that.data.current);
              }
              wx.showToast({
                title: error_msg,
                icon: 'warn',
                duration: 500
              })
            }
          })
        }
      }
    })
  },
  loadingChange() {
    setTimeout(() => {
      this.setData({
        hidden: true
      })
    }, 2000)
  },

  siteDetail: function (e) {
    var that = this
    //获取点击的id值
    var index = e.currentTarget.dataset.index;
    console.log(index)
    var goodsId = that.data.goodsList[index].goods_id;

    wx.navigateTo({
      url: "../goods/goods?objectId=" + goodsId
    });
  },
  //取消订单
  cancel_order: function (e) {
    var that = this
    var error_msg = '';
    wx.showModal({
      title: '提示',
      content: '确认取消订单？',
      success: function (res) {
        if (res.confirm) {
          //
          wx.request({
            url: app.apiUrl('ecapi.order.cancel'),
            data: {
              order: orderId
            },
            header: {
              'Content-Type': 'application/json',
              'X-ECTouch-Authorization': token
            },
            method: "POST",
            success: function (res) {
              if (res.data.error_code > 0) {
                app.shwomessage('取消失败', 1000, 'clear')

              } else if (res.data.error_code == 0) {
                app.shwomessage('取消成功', 1000, 'warn')

                that.orderStatus(that, that.data.current);
              }
            }
          })
          //
        }
      }
    })
  },
  //下拉刷新完后关闭
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
  pay_order: function (e) {
    var order_id = e.currentTarget.dataset.id
    var openid = wx.getStorageSync('openid')
    app.payOrder(order_id, openid, token)
  },
})