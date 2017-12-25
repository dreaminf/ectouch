// import menus from './resources/json/menus.js'
var app = getApp()
var token
Page({
  data: {
    items: [],
    startX: 0, //开始坐标
    startY: 0,
    toView: 'blue',
    // 'menus': menus,
    selectedMenuId: 1,
    total: {
      count: 0,
      money: 0
    },
    flowMoney: {
      checkoutUrl: '../checkout/checkout',
    }
  },

  getCartGoods: function (that) {
    //调用应用实例的方法获取全局数据
    wx.request({
      url: app.apiUrl('ecapi.cart.get'),
      method: "POST",
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      success: function (res) {
        var attr;
        var temp = '';
        for (var i in res.data.goods_groups.goods) {
          attr = res.data.goods_groups.goods[i].goods_attr.split('\n')
          temp = ''
          for(var j in attr){
            if(attr[j] == '') continue;
            temp += attr[j]+','
          }
          res.data.goods_groups.goods[i].goods_attr = temp.substring(0, temp.length-1)
        }

        if (res.data.goods_groups == '') {
          that.setData({
            flowLists: { goods: '' }

          })
        } else {
          that.setData({
            flowLists: res.data.goods_groups

          })
        }

      }
    })
  },
  onLoad: function () {
    token = wx.getStorageSync('token')
    var that = this
    this.getCartGoods(that);

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

  addCount: function (event) {
    var that = this

    let data = event.currentTarget.dataset
    let total = this.data.total
    let flowLists = this.data.flowLists


    let flowList = flowLists.goods.find(function (v) {
      return v.rec_id == data.id
    })
    flowList.goods_number = parseInt(flowList.goods_number) + 1
    total.count += 1
    flowLists.total_price += parseInt(flowList.goods_price)

    //改变购物车商品数量
    wx.request({
      url: app.apiUrl('ecapi.cart.update'),
      data: {
        good: data.id,
        amount: flowList.goods_number
      },
      method: 'POST',
      success: function () {
        that.onLoad()
      }
    })

  },
  minusCount: function (event) {
    var that = this

    let data = event.currentTarget.dataset
    let total = this.data.total
    let flowLists = this.data.flowLists
    let flowList = flowLists.goods.find(function (v) {
      return v.rec_id == data.id
    })
    flowLists.total_price -= parseInt(flowList.goods_price)
    if (parseInt(flowLists.total_price) < 0) {
      flowLists.total_price += parseInt(flowList.goods_price)
      return
    }
    flowList.goods_number = parseInt(flowList.goods_number) - 1
    if (parseInt(flowList.goods_number) < 1) {
      flowList.goods_number = parseInt(flowList.goods_number) + 1
      flowLists.total_price += parseInt(flowList.goods_price)
      return
    }


    //改变购物车商品数量
    wx.request({
      url: app.apiUrl('ecapi.cart.update'),
      data: {
        good: data.id,
        amount: flowList.goods_number
      },
      method: 'POST',
      success: function () {
        that.onLoad()
      }
    })

  },
  del: function (event) {
    var that = this
    let data = event.currentTarget.dataset

    wx.showModal({
      title: '提示',
      content: '您确定要移除当前商品吗?',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: app.apiUrl('ecapi.cart.delete'),
            data: { good: data.id },
            method: 'POST',
            header: {
              'Content-Type': 'application/json',
              'X-ECTouch-Authorization': token
            },
            success: function (res) {
              if (res.data.error_code == 0) {
                wx.showToast({
                  title: '删除成功',
                  icon: 'warn',
                  duration: 2000
                })
              } else {
                wx.showToast({
                  title: '删除失败',
                  icon: 'warn',
                  duration: 2000
                })
              }
              that.onLoad()
            }
          })
        }
      }
    })
  },
  flowcartBtn: function () {
    wx.switchTab({
      url: '../categroy/categroy'
    })
  },
  onShow: function () {
    var that = this
    this.getCartGoods(that);
  },
  siteDetail: function (e) {
    var that = this
    //获取点击的id值
    var index = e.currentTarget.dataset.index;
    console.log(index)
    var goodsId = that.data.flowLists.goods[index].goods_id;

    wx.navigateTo({
      url: "../goods/goods?objectId=" + goodsId
    });
  },
  //下拉刷新完后关闭
  onPullDownRefresh: function () {
    var that = this
    this.getCartGoods(that);
    wx.stopPullDownRefresh()
    that.onLoad()

  },

})