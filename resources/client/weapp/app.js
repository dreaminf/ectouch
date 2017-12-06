//app.js
App({
  onLaunch: function () {
    // 获取用户数据
    this.getUserInfo();
  },
  getUserInfo: function (cb) {
    var that = this
    if (that.globalData.userInfo) {
      typeof cb == "function" && cb(that.globalData.userInfo)
    } else {
      //调用登录接口
      wx.login({
        success: function (result) {
          var code = result.code;
          wx.getUserInfo({
            success: function (res) {
              res.userInfo.code = code;
              that.doLogin(res.userInfo);
              that.globalData.userInfo = res.userInfo
              typeof cb == "function" && cb(that.globalData.userInfo)
            }
          })
        }
      })
    }
  },
  // 小程序用户登录
  doLogin: function (userinfo) {
    var that = this
    if (userinfo.code) {
      // 发起网络请求
      wx.request({
        url: that.apiUrl('ecapi.auth.weixin.mplogin'),
        method: 'POST',
        data: {
          userinfo
        },
        success: function (res) {
          wx.setStorage({
            key: 'token',
            data: res.data.token
          })
          wx.setStorage({
            key: 'openid',
            data: res.data.openid
          })
        }
      })
    } else {
      console.log('获取用户登录态失败！' + res.errMsg)
    }
  },
  globalData: {
    userInfo: null
  },
  // 设置服务端API
  apiUrl: function (api) {
    return 'https://www.ectouch.cn/ecapi/public/' + api;
  },
  shwomessage: function (msg, time = 1000, icon = 'warn') {
    wx.showToast({
      title: msg,
      icon: icon,
      duration: time
    })
  },
  redirectTo : function (url){
     wx.navigateTo({
      url: url
    });
  },
  payOrder : function(order_id, openid, token){
    var that = this
    wx.request({
      url: that.apiUrl('ecapi.payment.pay'),
      data: {
        order: order_id,
        openid: openid,
        code: 'wxpay.web'
      },
      header: {
        'Content-Type': 'application/json',
        'X-ECTouch-Authorization': token
      },
      method: "POST",
      success: function (res) {
        if (res.statusCode == 500) {
          return
        }
        var wxpayinfo = res.data.wxpay
        if (wxpayinfo == '') {
          return
        }
        //发起支付
        wx.requestPayment({
          'timeStamp': wxpayinfo.timestamp,
          'nonceStr': wxpayinfo.nonce_str,
          'package': wxpayinfo.packages,
          'signType': 'MD5',
          'paySign': wxpayinfo.sign,
          'success': function (payres) {
            if (payres.errMsg == 'requestPayment:ok') {
              //成功修改订单状态
              wx.request({
                url: that.apiUrl('ecapi.product.get'),
                data: {
                  "product": order_id,
                },
                method: "post",
                header: {
                  'Content-Type': 'application/json'
                },
                success: function (res) {
                  that.redirectTo('../order/detail?objectId=' + order_id)
                }
              })
            }
          },
          'fail': function (payres) {
            that.redirectTo('../order/detail?objectId=' + order_id)

          }
        })

      }
    })
  }
})
