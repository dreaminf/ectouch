var app = getApp()

Page({
  data: {
    countrys: [],
    provinces: [],
    citys: [],
    districts: [],
    // 国
    countryList: [],
    countryIndex: 0,
    // 省
    provinceList: [],
    provinceIndex: 0,
    // 市
    cityList: [],
    cityIndex: 0,
    // 县
    districtList: [],
    districtIndex: 0,
    // 收货人信息
    consignee: '',
    mobile: '',
    address: ''
  },

  onLoad: function () {
    var that = this
    var token = wx.getStorageSync('token')

    // 地区赋值
    try {
      var regions = wx.getStorageSync('regions')
      if (regions) {
        // Do something with return value
        var country = ['请选择'];
        for (var i = 0; i < regions.length; i++) {
          country.push(regions[i].region_name);
        }
        // 赋值
        that.setData({
          countryList: country,
          countrys: regions
        });
      }
    } catch (e) {
      // Do something when catch error
    }

    //加载中
    that.loadingChange()
  },
  loadingChange() {
    setTimeout(() => {
      this.setData({
        hidden: true
      })
    }, 2000)
  },
  //国
  countryTypeChange: function (e) {

    var that = this
    var index = e.detail.value
    var data = that.data.countrys[index - 1].regions

    var province = ['请选择'];
    for (var i = 0; i < data.length; i++) {
      province.push(data[i].region_name)
    }

    that.setData({
      countryIndex: index,
      provinceList: province,
      provinces: data,
      provinceIndex: 0,
      cityIndex: 0,
      districtIndex: 0
    })
  },
  //省
  provinceTypeChange: function (e) {
    var that = this
    var index = e.detail.value
    var data = that.data.provinces[index - 1].regions

    var city = ['请选择'];
    for (var i = 0; i < data.length; i++) {
      city.push(data[i].region_name)
    }

    that.setData({
      provinceIndex: index,
      cityList: city,
      citys: data,
      cityIndex: 0,
      districtIndex: 0
    })
  },
  //市
  cityTypeChange: function (e) {
    var that = this
    var index = e.detail.value
    var data = that.data.citys[index - 1].regions

    var district = ['请选择'];
    for (var i = 0; i < data.length; i++) {
      district.push(data[i].region_name)
    }

    that.setData({
      cityIndex: index,
      districtList: district,
      districts: data,
      districtIndex: 0
    })
  },
  //区
  districtTypeChange: function (e) {
    this.setData({
      districtIndex: e.detail.value
    })
  },
  saveData: function (e) {
    var that = this
    var data = e.detail.value;
    var region_id = 0;
    if (that.data.districts.length > 0) {
      var districtsIndex = parseInt(data.district) - 1
      region_id = that.data.districts[districtsIndex].region_id;
    }

    var postdata = {
      name: data.consignee,
      mobile: data.mobile,
      tel: '',
      zip_code: '',
      region: region_id,
      address: data.address,
    }

    var token = wx.getStorageSync('token')
    wx.request({
      url: app.apiUrl('ecapi.consignee.add'),
      method: 'post',
      header: {
        'X-ECTouch-Authorization': token
      },
      data: postdata,
      success: function (res) {
        var result = res.data
        if (result.error_code > 0) {
          for (var i in result.error_desc) {
            wx.showModal({ title: result.error_desc[i][0] })
            break
          }
          return false;
        }
        wx.showToast({
          title: '保存成功',
          duration: 2000,
          success: function () {
            wx.redirectTo({ url: './index' })
          }
        })
      }
    })
  },
  //下拉刷新完后关闭
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
})
