var app = getApp()
var token

var order = {
  consignee: 1,
  shipping: 1,
  msg: ''
}
var cart_goods;
var goods_total;
Page({
  data: {
    index: 0,
    orderJtou: '../../res/images/icon-arrowdown.png',
    addresss_link: '../address/index?from=flow',
    checkList: [],
  },

  onShow: function () {
    token = wx.getStorageSync('token')
    var that = this
    wx.request({
      url: app.apiUrl('ecapi.cart.flow'),
      method: "post",
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      success: function (res) {
        if (res.data[400]!=undefined) {
          wx.switchTab({
            url: '../categroy/categroy'
          })
          return
        }
        if (res.data.cart_goods.length == 0) {
          wx.switchTab({
            url: '../categroy/categroy'
          })
          return
        }
        order.consignee = res.data.consignees.address_id
        var ship = {
          id: [],
          name: []
        }
        cart_goods = res.data.cart_goods
        for (var i in res.data.shipping) {
          ship.id.push(res.data.shipping[i].shipping_id)
          ship.name.push(res.data.shipping[i].shipping_name)

        }
        order.shipping = ship.id[0]

        var attr;
        var temp = '';
        for (var i in res.data.cart_goods) {
          attr = res.data.cart_goods[i].goods_attr.split('\n')
          for (var j in attr) {
            if (attr[j] == '') continue;
            temp += attr[j] + ','
          }
          res.data.cart_goods[i].goods_attr = temp.substring(0, temp.length - 1)
        }
        goods_total = res.data.total
        that.setData({
          checkList: res.data.cart_goods,
          goods_total: res.data.total,
          flowCont: '共' + res.data.total_number + '件商品,合计：',
          flowMoney: res.data.total,
          addresss: res.data.consignees,
          shipping: ship
        })
        that.shippingChange()

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
    }, 2000)
  },
  //配送方式
  shippingChange: function (e) {
    var shipping_index = 0
    if (e != undefined) {
      shipping_index = e.detail.value
    }
    order.shipping = this.data.shipping.id[shipping_index]
    var that = this

    this.setData({
      index: shipping_index
    })

    wx.request({
      url: app.apiUrl('ecapi.shipping.select.shipping'),
      data: {
        address: order.consignee,
        shipping_id: order.shipping,
        goods: cart_goods
      },
      method: 'POST',
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      success: function (res) {
        if (res.data == false) {
          that.setData({
            payfee: '',
            total: goods_total
          })
          return
        }
        if (parseInt(res.data) >= 0) {
          that.setData({
            payfee: res.data,
            payname: that.data.shipping.name[shipping_index],
            total: goods_total + parseInt(res.data)
          })
        }
      }
    })
  },

  //下拉刷新完后关闭
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
  siteDetail: function (e) {
    var that = this
    //获取点击的id值
    var index = e.currentTarget.dataset.index;
    var goodsId = that.data.checkList[index].goods_id;

    wx.navigateTo({
      url: "../goods/index?objectId=" + goodsId
    });
  },
  //提交订单
  submitOrder: function () {

    if (order.consignee == '' || order.consignee == undefined) {
      app.shwomessage('没有收货地址');
      return;
    }
    wx.request({
      url: app.apiUrl('ecapi.cart.checkout'),
      method: "post",
      data: {
        consignee: order.consignee,
        shipping: order.shipping,
        comment: order.msg
      },
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      success: function (res) {
        var oid = res.data.order.order_id
        if (oid != '') {
          wx.redirectTo({
            url: '../flow/done?id=' + oid
          })
        }

      }
    })
    //
  },
  getmsg: function (e) {
    order.msg = e.detail.value
  },
})