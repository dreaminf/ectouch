小程序页面规划
=======
####一. 首页(店铺)
```
//a.轮播图

    "banner": {
        "banner_pic":"a.jpg"  //轮播图片
        "banner_link":"#……"  //轮播图链接
    }

//b.广告位

    "activity": {
        "activity_pic":"…….jpg"   //广告图
        "activity_link":"#……."   //广告图链接
    }

//c.推荐商品列表 

    "list":[
        {
            "goods_id":"1"  //商品id
            "goods_name":"夏季衣服而火女……" //商品名称
            "shop_price":"￥50"  //商品价格
            "goods_thumb":"…….jpg" //商品图片
            "goods_link":"#……"   //商品链接
            "goods_sales":"200"  //商品销量
            "delDetailMoney":"￥60"  //商品原价格
            "goods_stock":"10000"  //商品库存
        }
    ]

```
####二. 分类

######1.分类
```
    "category" [
        {
            "id":"1"
            "name":"家用电器" //一级分类标题
            "cat_id":[
                {
                    "id":"10" //二级分类id
                    "name":"大家电"   //二级分类标题
                    "cat_id":[
                        {
                            "id":"10"  //三级分类id
                            "cat_img":"…….jpg"    //三级分类图
                            "name":"平板电视"  //三级分类标题
                            "link":""  //三级分类链接
                        }
                    ]
                }
            ]
            
        }
        ]


```
######2.商品列表
```
//列表
    "list":{
        "goods_id":"1"    //商品id
        "goods_name":"夏季衣服而火女……" //商品名称
        "shop_price":"￥50"  //商品价格
        "goods_thumb":"…….jpg" //商品图片
        "goods_sales":"200"   //商品销量
        "delDetailMoney":"￥60"   //商品原价格
        "goods_stock":"10000"  //商品库存
        }
       
//筛选
    "screen":{
        "brand_name":"美的" //品牌名称
        }
```
####三. 商品详情
```
//a.商品基本信息
    "goodscont":{
        //相册
      "goodsimg":{
            "goods_img":"…….jpg"  //商品相册循环图
          }
        //属性
      "specification":[
        [
          {
          "attr_type":"1"
          "name":"颜色"
          "values"{
              "label":"蓝色",
              "id":"1",
              "price":"12",
              "format_price":"12.00"
          }
        ]
      ]
      }    
      "goods_name":"部分更好的"  //商品标题
      "goods_price":"￥50"  //商品价格
      "delDetailMoney":"￥60"   //商品原价
      "stock":"20"   //商品库存
      "region":"上海"   //地区
    }

//b.评论列表
    "comment":{
      "name":"用户名"   //评论用户
      "time":"2017/6/19"  //评论时间
      "content":"很好"   //评论内容
    
    }

//c.商品详细介绍内容
    "goodsdetail":{
      "goods_detail":"……" //商品详细内容
      "goods_norms":"……"    //商品规格列表
    }
```



####四. 购物流程


#####1.购物车
```
//a.加入列表
    "cartlist":{
        "goods_id":"1"  //商品id
        "goods_name":"水电费额分"  //商品名称
        "goods_number":"2"   //商品数量
        "goods_price":"20"   //商品价格
        "goods_thumb":"…….jpg"   //商品图片
        "total_amount":"20"   //合计数量
        "total_price":"￥1200"  //总价格
    }

//b.推荐商品列表
    "list":{
        "goods_id":"1"  //商品id
        "goods_name":"夏季衣服而火女……" //商品名称
        "shop_price":"￥50"  //商品价格
        "goods_thumb":"…….jpg" //商品图片
        "goods_sales":"200"   //商品销量
        "delDetailMoney":"￥60" //商品原价格
        "goods_stock":"10000"  //商品库存
    }
```

#####2. 订单提交
```
//订单提交
    "checkout":{
        "consignee":"张三"  //收货人姓名
        "address":"上海市……"   //收货人地址
        "mobile":"17602100555"  //联系方式
        "goods_name":"电放保函……"   //商品名字
        "goods_number":"20"   //数量
        "goods_price":"￥50"  //商品价格
        "goods_thumb":"…….jpg"  //商品图片
        "shipping_name":"圆通快递"  //配送方式名字
    }
```

####六. 个人中心

#####1. 订单列表（待支付、待收货、待评价、全部订单）（订单详情）
```
//a.推荐商品列表
  "list":{
        "goods_id":"1"  //商品id
        "goods_name":"夏季衣服而火女……" //商品名称
        "shop_price":"￥50"  //商品价格
        "goods_thumb":"…….jpg" //商品图片
        "goods_sales":"200"   //商品销量
        "delDetailMoney":"￥60" //商品原价格
        "goods_stock":"10000"  //商品库存
    }

//b.订单详情
    "order_detail":{
        "consignee":"张三"  //收货人姓名
        "address":"上海市……"   //收货人地址
        "mobile":"17602100555"  //联系方式
        "order_sn":"55484845454"   //订单号
        "add_time":"2017/6/19"   //订单时间
        "goods_thumb":"…….jpg" //商品图片
        "goods_prices":"￥50" //商品价格
        "goods_number":"60"  //商品数量
        "pay_status":"未付款"  //订单的状态
        "shipping_name":"圆通快递" //快递名字
        "shipping_fee":"10"  //快递的费用
        "money_paid":"50" //已付款的金额
        "order_amount":"0"  //应付款的金额
        "total_numbe":"1000"  //总得数量
        "goods_amount":"5000"   //总金额
    }
//c.订单状态   

```
#####2. 收货地址
```
    "consignees":{
        "consignee":"张三"  //收货人姓名
        "address":"上海市……"  //收货人地址
        "mobile":"17602100555"   //联系方式
    }
```
#####3. 我的钱包
```
//a.钱包
    "wallet":{
        "money_num":"￥500"   //金额数量
        "blocking_num":"0"  //冻结金额
        "wallet_num":"0"  //红包数量
        "integration_num":"0"  //积分数量
    }

//b.账户明细
    {
        "orde_num":"5484515454848"  //操作订单号
        "time":"2017/6/19"  //操作时间
        "money":"50"  //操作金额
    }

//c.提现记录
    {
        "time":"2017/6/19"   //操作时间
        "money":"50"  //操作金额
        "status"："已完成"  //状态
    }
```

#####4. 我的收藏
```
    "list":{
        "goods_id":"1"    //商品id
        "goods_name":"夏季衣服而火女……" //商品名称
        "shop_price":"￥50"  //商品价格
        "goods_thumb":"…….jpg" //商品图片
        "goods_stock":"10000"  //商品库存
    }
```
#####6. 我的优惠券
```
    "coupons":{
        "money":"100" //优惠券金额
        "time":"2017-6-19-2018-1-1" //优惠券有效时间
        "content":"满多少减多少，等"   //内容介绍
    }
```
####五. 搜索
```
//搜索记录
    searchList:{
        "name":"帽子"//搜索名称
        "id":"1"
    }
```
参考项目：

1. 酷客多
2. 拼多多
3. 周黑鸭官方商城


项目难点：

1. 收款方为平台或商家
2. 平台接口需要升级为https
3. 进入后要求先关联手机号码


项目版本库： 

1. 平台版：https://git.oschina.net/dscmall/mini-apps.git
2. 商家版：https://git.oschina.net/dscmall/mini-apps-seller.git
3. 门店版：https://git.oschina.net/dscmall/mini-apps-store.git