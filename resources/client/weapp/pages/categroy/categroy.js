var app = getApp()
Page({
	data: {
		cateLeft: [],
		cateRight: [],
		curNav: 1,
		curIndex: 0,
		hidden: false,
	},

	onLoad: function () {
		var that = this
		//调用应用实例的方法获取全局数据
		wx.request({
			url: app.apiUrl('ecapi.category.all.list'),
			data: {
				"id": "0",
				"per_page": "5",
				"category": "1",
				"shop": 1,
				"page": 1
			},
			method: "post",
			header: {
				'Content-Type': 'application/json'
			},
			success: function (res) {
				that.setData({
					cateLeft: res.data.left,
					cateRight: res.data.right,
					curNav: res.data.left[0].cat_id
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
	//事件处理函数
	selectNav(event) {
		let id = event.target.dataset.id,
			index = parseInt(event.target.dataset.index);
		//console.log(index)
		self = this;
		this.setData({
			curNav: id,
			curIndex: index,
			scrollTop: 0
		})
	},
	//下拉刷新完后关闭
	onPullDownRefresh: function () {
		wx.stopPullDownRefresh()
	},
	onShareAppMessage: function () {
		return {
			title: '订单确认',
			desc: '小程序本身无需下载，无需注册，不占用手机内存，可以跨平台使用，响应迅速，体验接近原生App',
			path: '/pages/categroy/categroy'
		}
	}
})




