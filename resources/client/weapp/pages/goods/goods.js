var WxParse = require('../../wxParse/wxParse.js');
var app = getApp()
var shiftdata = {
  1: "65%",
  0: "15%"
}

var order = {
  id: "",
  num: 1,
  pro: [],
  prostr: []
}
var goodsbtn = ''
var tempOrderPro = [];
var tempOrderProStr = [];

Page({
  data: {
    hiddenOrder: false,
    hiddenAddress: true,
    winWidth: 0,
    winHeight: 0,
    // tab切换  
    currentTab: 0,
    mask: false,
    cart: false,
    current: 0,
    num: 1,
    new: 'top-hoverd-btn',
    good: '',
    child: '',
    goodsComment: [],
    carModels: [],
    goodJiansImg: '../../res/images/icon-arrowdown.png',
    indicatorDots: true,
    autoplay: true,
    interval: 4000,
    duration: 1000,
    //服务
    companyName: '东莞源湖科技有限公司',
    goodsService: [
      // {
      //   title: '正品保证'
      // },
      // {
      //   title: '货到付款'
      // },
      // {
      //   title: '七天退货'
      // },
      // {
      //   title: '极速达'
      // }
    ],
  },

  onLoad: function (options) {
    var that = this
    var goodsId = options.objectId;
    order.id = goodsId
    this.shiftanimation = wx.createAnimation({
      duration: 500,
      timingFunction: "ease",
    })
    this.shiftanimation.left("15%").step();
    this.setData({
      shiftanimation: this.shiftanimation.export()
    })
    wx.getSystemInfo({

      success: function (res) {
        that.setData({
          winWidth: res.windowWidth,
          winHeight: res.windowHeight
        });
      }

    });
    //商品详细信息获取
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
        var t='';
        res.data.goods_desc = res.data.goods_desc.replace('/http/', 'https');
        t = res.data.goods_desc.replace(/src=\"\/data/g, 'src="https:\/\/www\.shop.lymon\.cn/data');
        WxParse.wxParse('goods_desc', 'html', t, that, 5);
        that.setData({
          goodsImg: res.data.goodsImg,
          goods: res.data,
          goodsComment: res.data.comment.slice(0, 5),
          carModels: res.data.specification,
          parameteCont: res.data.properties,
          total_price: res.data.price,
          companyName: res.data.companyName,
        })

        //商品有属性则默认选中第一个
        for (var i in res.data.specification) {
          that.getProper(res.data.specification[i].values[0].id)
        }
        that.getGoodsTotal();
      }
    })
    //加载中
    this.loadingChange()
  },
  onShow: function () {
    order.num = 1;
    order.pro = [];
  },
  loadingChange() {
    setTimeout(() => {
      this.setData({
        hidden: true
      })
    }, 1000)
  },
  /*提交*/
  goodsCheckout: function (e) {
    var token = wx.getStorageSync('token')

    wx.request({
      url: app.apiUrl('ecapi.cart.add'),
      data: {
        "product": order.id,
        "property": JSON.stringify(tempOrderPro),
        "amount": order.num
      },
      method: "post",
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      success: function (res) {
        var result = res.data;
        if (result.error_code == 0) {

          if (goodsbtn == 'cart') {
            //成功则跳转到购物确认页面
            wx.switchTab({
              url: '../flow/flow'
            });
          } else {
            wx.redirectTo({
              url: '../checkout/checkout'
            });
          }
        } else {
          //错误处理
          wx.showToast({
            title: '提交失败',
            icon: 'warn',
            duration: 2000
          })
        }
      }
    })
  },
  //关闭弹框以及遮罩层
  maskHidden: function () {
    this.setData({
      mask: false,
      cart: false
    })
  },
  //事件处理函数
  /*点击购物车 显示商品选择*/
  addCost: function (e) {
    //获取id
    goodsbtn = e.currentTarget.id || 'cart'

    this.setData({
      mask: true,
      cart: true
    })
  },

  /*增加商品数量*/
  up: function () {
    var num = this.data.num;
    num++;
    if (num >= 99) {
      num = 99
    }
    this.setData({
      num: num
    })
    order.num = num;
    this.getGoodsTotal();
  },
  /*减少商品数量*/
  down: function () {
    var num = this.data.num;
    num--;
    if (num <= 1) {
      num = 1
    }
    this.setData({
      num: num
    })
    order.num = num;
    this.getGoodsTotal();
  },
  /*手动输入商品*/
  import: function (e) {
    var num = Math.floor(e.detail.value);
    if (num <= 1) {
      num = 1
    }
    if (num >= 999) {
      num = 999
    }
    this.setData({
      num: num
    })
    order.num = num;
    this.getGoodsTotal();

  },
  /*单选*/
  modelTap: function (e) {
    this.getProper(e.currentTarget.id)
    this.getGoodsTotal();
  },
  /*属性选择计算*/
  getProper: function (id) {
    tempOrderPro = []
    tempOrderProStr = []
    var categoryList = this.data.carModels;

    for (var index in categoryList) {
      for (var i in categoryList[index].values) {
        categoryList[index].values[i].checked = false;
        if (categoryList[index].values[i].id == id) {
          order.pro[categoryList[index].name] = id
          order.prostr[categoryList[index].name] = categoryList[index].values[i].label
        }
      }
    }

    //处理页面
    for (var index in categoryList) {
      if (order.pro[categoryList[index].name] != undefined && order.pro[categoryList[index].name] != '') {
        for (var i in categoryList[index].values) {
          if (categoryList[index].values[i].id == order.pro[categoryList[index].name]) {
            categoryList[index].values[i].checked = true;
          }
        }
      }
    }
    for (var l in order.pro) {
      tempOrderPro.push(order.pro[l]);
    }
    for (var n in order.prostr) {
      tempOrderProStr.push(order.prostr[n]);
    }

    this.setData({
      carModels: categoryList,
      selectedPro: tempOrderProStr.join(',')
    });
  },
  getGoodsTotal: function () {
    //提交属性  更新价格
    var that = this;
    var token = wx.getStorageSync('token')

    wx.request({
      url: app.apiUrl('ecapi.goods.property.total'),
      data: {
        goods_id: order.id,
        goods_number: order.num,
        good_property: tempOrderPro,
      },
      method: 'POST',
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      success: function (res) {
        if (res.data.error_code == 0) {
          that.setData({ total_price: res.data.total_price });
        }
      }
    })
  },

  //头部菜单切换(商品 详情、评论)
  toNew: function () {
    console.log('new');
    this.updateBtnStatus('new');
    wx.redirectTo({
      url: "../goods/goods?objectId=" + order.id
    })
  },
  toGood: function () {
    console.log('good');
    this.updateBtnStatus('good');
    wx.redirectTo({
      url: "../goods_detail/detail?objectId=" + order.id
    })
  },
  toChild: function () {
    console.log('child');
    this.updateBtnStatus('child');
    wx.redirectTo({
      url: "../goods_comment/comment?objectId=" + order.id
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
  flowCart: function () {
    wx.switchTab({
      url: '../flow/flow'
    });
  },
  onShareAppMessage: function () {
    return {
      title: '商品详情',
      desc: '小程序本身无需下载，无需注册，不占用手机内存，可以跨平台使用，响应迅速，体验接近原生App',
      path: '/pages/goods/goods?objectId=' + order.id 
    }
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








