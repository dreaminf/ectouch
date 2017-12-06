//index.js
//获取应用实例
var app = getApp()
var shiftdata = {
  1: "65%",
  0: "15%"
}
var goodsId = 0
var ran = 100
Page({
  data: {
    new: '',
    good: '',
    child: 'top-hoverd-btn',
    goodsMore: '../../res/images/more.png',
    //快捷导航
    navCont: [
      {
        title: '首页',
        url: "../index/index"
      },
      {
        title: '用户中心',
        url: "../user/user"
      }
    ],
    indicatorDots: true,
    autoplay: true,
    interval: 4000,
    duration: 1000,
    //评论列表


  },
  //事件处理函数
  //头部菜单切换(商品 详情、评论)
  toNew: function () {
    console.log('new');
    this.updateBtnStatus('new');
    wx.redirectTo({
      url: "../goods/goods?objectId=" + goodsId
    })
  },
  toGood: function () {
    console.log('good');
    this.updateBtnStatus('good');
    wx.redirectTo({
      url: "../goods_detail/detail?objectId=" + goodsId
    })
  },
  toChild: function () {
    console.log('child');
    this.updateBtnStatus('child');
    wx.redirectTo({
      url: "../goods_comment/comment?objectId=" + goodsId
    })
  },
  updateBtnStatus: function (k) {
    this.setData({
      new: this.getHoverd('new', k),
      good: this.getHoverd('good', k),
      child: this.getHoverd('child', k),
    });
  },
  getHoverd: function (src, dest) {
    return (src === dest ? 'top-hoverd-btn' : '');
  },


  onLoad: function (options) {
    var that = this
    goodsId = options.objectId;

    console.log('onLoad')
    this.shiftanimation = wx.createAnimation({
      duration: 500,
      timingFunction: "ease",
    })
    this.shiftanimation.left("15%").step();
    this.setData({
      shiftanimation: this.shiftanimation.export()
    })
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








