//index.js
//获取应用实例
var app = getApp()
var goodsId = 0
var ran = 100
Page({
  data: {
    indicatorDots: true,
    autoplay: true,
    interval: 4000,
    duration: 1000,
    //评论列表


  },
  //事件处理函数
  onLoad: function (options) {
    var that = this
    goodsId = options.objectId;
    wx.request({
      url: app.apiUrl('ecapi.product.get'),
      data: {
        "product": goodsId,
      },
      method: "post",
      header: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
        // order.pro = res.data.specification
        that.setData({
          goodsComment: res.data.comment,
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
  //下拉刷新完后关闭
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
})








