var WxParse = require('../../wxParse/wxParse.js');
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
    hiddenOrder: false,
    hiddenAddress: true,
    current: 0,
    number: 1,
    carModels: [],
    good: 'top-hoverd-btn',
    new: '',
    child: '',

    goodsInfoCont: "",



  },
  //事件处理函数
  //头部菜单切换(商品 详情、评论)
  toNew: function () {
    // console.log('new');
    this.updateBtnStatus('new');
    wx.redirectTo({
      url: "../goods/goods?objectId=" + goodsId
    })
  },
  toGood: function () {
    // console.log('good');
    this.updateBtnStatus('good');
    wx.redirectTo({
      url: "../goods_detail/detail?objectId=" + goodsId
    })
  },
  toChild: function () {
    //console.log('child');
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

  //跳转链接
  bindcheckTap: function () {
    wx.navigateTo({
      url: '../classify/classify'
    })
  },
  bindgoodsTap: function () {
    wx.navigateTo({
      url: '../flow/flow'
    })
  },

  /*goods-nav*/
  addNav: function () {
    var that = this;
    that.setData({
      select: !that.data.select,
    })
  },


  /*商品描述导航内容切换*/
  bindHeaderTap: function (event) {
    console.dir("header")
    this.setData({
      current: event.target.dataset.index,
    });
    this.toggleShift()
  },
  bindSwiper: function (event) {
    this.setData({
      current: event.detail.current,
    });
    this.toggleShift()
  },
  toggleShift: function () {
    this.shiftanimation.left(shiftdata[this.data.current]).step()
    this.setData({
      shiftanimation: this.shiftanimation.export()
    })
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
        WxParse.wxParse('goods_desc', 'html', res.data.goods_desc, that, 5);
        // order.pro = res.data.specification
        that.setData({
          goods: res.data,
          parameteCont: res.data.properties,
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
      toOrder: function (res) {
    this.setData({
      hiddenOrder: false,
      hiddenAddress: true
    })
  },
  toAddress: function (res) {
    this.setData({
      hiddenOrder: true,
      hiddenAddress: false
    })
  },
})








