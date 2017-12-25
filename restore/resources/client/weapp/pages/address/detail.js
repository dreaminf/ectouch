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
    address: '',
    addressData: {}
  },

  onLoad: function () {
    var that = this
    var token = wx.getStorageSync('token')

    // 地区赋值
    try {
      var regions = wx.getStorageSync('regions') // 地区列表
      var addressData = wx.getStorageSync('addressData') // 个人资料
      if (regions && addressData) {
        // 赋值
        that.setData({
          countrys: regions,
          addressData: addressData
        });

        // 定位国家
        var countrys = that.data.countrys
        var country = ['请选择'];
        for (var i in countrys) {
          country.push(countrys[i].region_name);
          if (countrys[i].region_id == addressData.country) {
            var currentCountryIndex = parseInt(i) + 1
          }
        }
        that.setData({
          countryList: country,
          countryIndex: currentCountryIndex,
          provinces: countrys[currentCountryIndex - 1].regions
        });

        // 定位省份
        var provinces = that.data.provinces
        var province = ['请选择'];
        for (var i in provinces) {
          province.push(provinces[i].region_name)
          if (provinces[i].region_id == addressData.province) {
            var currentProvinceIndex = parseInt(i) + 1
          }
        }
        that.setData({
          provinceList: province,
          provinceIndex: currentProvinceIndex,
          citys: provinces[currentProvinceIndex - 1].regions
        });

        // 定位城市
        var citys = that.data.citys
        var city = ['请选择'];
        for (var i in citys) {
          city.push(citys[i].region_name)
          if (citys[i].region_id == addressData.city) {
            var currentCityIndex = parseInt(i) + 1
          }
        }
        that.setData({
          cityList: city,
          cityIndex: currentCityIndex,
          districts: citys[currentCityIndex - 1].regions
        });

        // 定位县城
        var districts = that.data.districts
        var district = ['请选择'];
        for (var i in districts) {
          district.push(districts[i].region_name)
          if (districts[i].region_id == addressData.district) {
            var currentDistrictIndex = parseInt(i) + 1
          }
        }
        that.setData({
          districtList: district,
          districtIndex: currentDistrictIndex
        });

        // 赋值
        that.setData({
          consignee: addressData.consignee,
          mobile: addressData.full_mobile,
          address: addressData.full_address,
        })
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
    }, 1000)
  },
  //国
  countryTypeChange: function (e) {
    // console.log('picker发送选择改变，携带值为', e.detail.value)
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
    // console.log('picker发送选择改变，携带值为', e.detail.value)
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
    // console.log('picker发送选择改变，携带值为', e.detail.value)
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
    // console.log('picker发送选择改变，携带值为', e.detail.value)
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
      consignee: that.data.addressData.address_id,
      name: data.consignee,
      mobile: data.mobile,
      tel: '',
      zip_code: '',
      region: region_id,
      address: data.address,
    }

    var token = wx.getStorageSync('token')

    wx.request({
      url: app.apiUrl('ecapi.consignee.update'),
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
