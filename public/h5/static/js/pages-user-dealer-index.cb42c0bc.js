(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-dealer-index"],{"10eb":function(e,t,n){"use strict";n("7a82"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")},n("d9e2"),n("d401")},4053:function(e,t,n){"use strict";n("7a82"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){if(Array.isArray(e))return(0,a.default)(e)};var a=function(e){return e&&e.__esModule?e:{default:e}}(n("b680"))},"44ab":function(e,t,n){"use strict";n.r(t);var a=n("96ec"),o=n.n(a);for(var i in a)["default"].indexOf(i)<0&&function(e){n.d(t,e,(function(){return a[e]}))}(i);t["default"]=o.a},52967:function(e,t,n){"use strict";n.r(t);var a=n("ae18"),o=n("44ab");for(var i in o)["default"].indexOf(i)<0&&function(e){n.d(t,e,(function(){return o[e]}))}(i);n("7cc9");var r=n("f0c5"),l=Object(r["a"])(o["default"],a["b"],a["c"],!1,null,"76168c36",null,!1,a["a"],void 0);t["default"]=l.exports},"7cc9":function(e,t,n){"use strict";var a=n("de32"),o=n.n(a);o.a},"96ec":function(e,t,n){"use strict";n("7a82");var a=n("ee27").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,n("14d9"),n("e25e"),n("d81d"),n("4de4"),n("d3b7");var o=a(n("d0ff")),i=a(n("f07e")),r=a(n("c964")),l=a(n("f3f3")),s=n("26cb"),c={data:function(){return{isColor:getApp().globalData.isColor,isBgColor:getApp().globalData.isBgColor,buttonStyle:{width:"100%",height:"108rpx",border:"none",fontSize:"32rpx",color:"#fff",background:"".concat(getApp().globalData.isBgColor),borderRadius:"16rpx",margin:"0",fontWeight:"bold"},buttonLoading:!1,msg:"",levelData:[],level:0,dredgeLevel:0,maxLevel:0,dredge:{reseller_id:"",total_fee:""},userInfo:""}},computed:(0,l.default)((0,l.default)({},(0,s.mapGetters)("user",["token"])),(0,s.mapGetters)("app",["iosIsPay"])),watch:{},onPageScroll:function(e){var t=e.scrollTop>100?.95:.0095*e.scrollTop;this.navbarBg="rgba(255, 255, 255, ".concat(t,")")},onLoad:function(){this.getPageData()},onShow:function(){var e=this;return(0,r.default)((0,i.default)().mark((function t(){return(0,i.default)().wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,e.$onLaunched;case 2:e.isColor=getApp().globalData.isColor,e.isBgColor=getApp().globalData.isBgColor,e.buttonStyle.background=getApp().globalData.isBgColor,console.log(e.isColor);case 6:case"end":return t.stop()}}),t)})))()},methods:(0,l.default)((0,l.default)({},(0,s.mapActions)("user",["getUserInfo"])),{},{dredgeDealer:function(){var e=this,t=function(){e.buttonLoading=!0,uni.showLoading({title:"开通中...",mask:!0}),e.$request("dealer.createOrder",(0,l.default)((0,l.default)({},e.dredge),{},{platform:e.$utils.platforms()})).then((function(t){1===t.code?e.callPay(t.data.order_sn,"wechat",t.data.platform):(e.buttonLoading=!1,uni.hideLoading())})).catch((function(t){e.buttonLoading=!1,uni.hideLoading()}))};this.dredgeLevel<this.level?uni.showModal({title:"提示",content:"当前购买等级小于已购买等级，是否继续购买？",success:function(e){e.confirm?t():e.cancel}}):t()},xGetPay:function(e,t,n){var a=this;uni.login({provider:"weixin",success:function(o){"login:ok"===o.errMsg&&a.$request("common.xunipay",{order_sn:e,payment:t,platform:n,code:o.code}).then((function(e){if(console.log(e),1===e.code)if("H5"==n){var t=document.createElement("divpay");t.innerHTML=e.data.pay_data,document.body.appendChild(t)}else a.xunipay(e.data.pay_data)}))}})},getPay:function(e,t,n){var a=this;this.$request("common.pay",{order_sn:e,payment:t,platform:n}).then((function(e){if(console.log(e),1===e.code)if("H5"==n){var t=document.createElement("divpay");t.innerHTML=e.data.pay_data,document.body.appendChild(t)}else a.pay(e.data.pay_data)}))},callPay:function(e,t,n){var a=this;this.$request("common.ifxunipay").then((function(o){o.data.xunipay_switch,a.getPay(e,t,n)}))},xunipay:function(e){},pay:function(e){var t=this;WeixinJSBridge.invoke("getBrandWCPayRequest",{appId:e.appId,timeStamp:e.timeStamp,nonceStr:e.nonceStr,package:e.package,signType:e.signType,paySign:e.paySign},(function(e){"get_brand_wcpay_request:ok"==e.err_msg?(uni.showToast({title:"支付成功",icon:"none",duration:2e3}),t.buttonLoading=!1,t.getPageData(),uni.hideLoading()):(uni.showToast({title:"支付失败",icon:"none",duration:2e3}),t.buttonLoading=!1,uni.hideLoading())}))},compareVersion:function(e,t){if("string"!==typeof e||"string"!==typeof t)return 0;var n=e.split("."),a=t.split("."),o=Math.max(n.length,a.length);while(n.length<o)n.push("0");while(a.length<o)a.push("0");for(var i=0;i<o;i++){var r=parseInt(n[i],10),l=parseInt(a[i],10);if(r>l)return 1;if(r<l)return-1}return 0},levelCardClick:function(e){this.dredgeLevel=e.level,this.dredgeLevelId=e.id,this.dredge.reseller_id=e.id,this.dredge.total_fee=e.price},dealerLevelList:function(){var e=this;this.$request("dealer.level").then((function(t){if(1===t.code){e.levelData=t.data.list,e.msg=t.data.reseller_desc.content;var n=t.data.list.map((function(e){return e.level}));if(e.maxLevel=Math.max.apply(Math,(0,o.default)(n)),e.dredgeLevel=e.level+1>e.maxLevel?e.maxLevel:e.level+1,e.dredgeLevel<=e.maxLevel){var a=t.data.list.filter((function(t){return t.level==e.dredgeLevel}))[0];e.dredge.reseller_id=a.id,e.dredge.total_fee=a.price}}}))},getPageData:function(){var e=this;this.getUserInfo().then((function(t){if(1===t.code){if(e.level=t.data.reseller?t.data.reseller.level:0,e.userInfo=t.data,t.data.reseller){var n={id:t.data.reseller.reseller_id,level:t.data.reseller.level,levelText:t.data.reseller.reseller_json.name,expireText:t.data.reseller.expiretime_text};e.userInfo=(0,l.default)((0,l.default)({},e.userInfo),n)}e.dealerLevelList()}}))}})};t.default=c},ae18:function(e,t,n){"use strict";n.d(t,"b",(function(){return o})),n.d(t,"c",(function(){return i})),n.d(t,"a",(function(){return a}));var a={uParse:n("ca0f").default,uButton:n("e0f1").default},o=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("v-uni-view",{staticClass:"page_content"},[n("v-uni-view",{staticClass:"head_content"},[n("CustomNavbar",{attrs:{title:"开通经销商"}})],1),n("v-uni-view",{staticClass:"main_content"},[n("v-uni-view",{staticClass:"info_box"},[n("v-uni-view",{staticClass:"user"},[n("v-uni-view",{staticClass:"avatar"},[n("v-uni-image",{staticClass:"image",attrs:{src:e.userInfo.avatar,mode:"aspectFill"}})],1),0==e.level?n("v-uni-view",{staticClass:"content"},[n("v-uni-view",{staticClass:"p1"},[e._v("亲爱的平台用户，您好")]),n("v-uni-view",{staticClass:"p2"},[e._v("您还不是分销商，请在下面点击开通经销商")])],1):n("v-uni-view",{staticClass:"content"},[n("v-uni-view",{staticClass:"p1"},[e._v("亲爱的"+e._s(e.userInfo.levelText)+"，您好")]),n("v-uni-view",{staticClass:"p2"},[e._v("恭喜你，您已是我们的"+e._s(e.userInfo.levelText))])],1)],1),n("v-uni-view",{staticClass:"card"},[0==e.level?n("v-uni-view",{staticClass:"texts"},[n("v-uni-text",{staticClass:"text1",style:"color:"+e.isColor},[e._v("平台用户")]),n("v-uni-text",{staticClass:"text3"},[e._v("永久有效")])],1):n("v-uni-view",{staticClass:"texts"},[n("v-uni-text",{staticClass:"text1"},[e._v(e._s(e.userInfo.levelText))]),n("v-uni-text",{staticClass:"text3"},[e._v(e._s(e.userInfo.expireText))])],1)],1)],1),n("v-uni-view",{staticClass:"content_box"},[n("v-uni-view",{staticClass:"level_box"},[n("v-uni-view",{staticClass:"title"},[e._v("等级分类")]),e.levelData.length?n("v-uni-view",{staticClass:"list_box"},e._l(e.levelData,(function(t,a){return n("v-uni-view",{key:a,staticClass:"item",class:{active:t.level==e.dredgeLevel},style:t.level==e.dredgeLevel?"background-image: linear-gradient(to right, #000, #000),"+e.isBgColor:"",on:{click:function(n){arguments[0]=n=e.$handleEvent(n),e.levelCardClick(t)}}},[n("v-uni-view",{staticClass:"text1"},[e._v(e._s(t.expire_text))]),n("v-uni-view",{staticClass:"text2",style:"color:"+e.isColor},[n("v-uni-text",[e._v("￥")]),n("v-uni-text",{staticClass:"num"},[e._v(e._s(t.price))])],1),n("v-uni-view",{staticClass:"text3"},[e._v("直接分润"+e._s(Number(t.direct))+"%")]),n("v-uni-view",{staticClass:"text3"},[e._v("间接分润"+e._s(Number(t.indirect))+"%")]),n("v-uni-view",{staticClass:"tip",style:"background:"+e.isBgColor},[e._v(e._s(t.name))])],1)})),1):e._e()],1),n("v-uni-view",{staticClass:"text_box"},[n("u-parse",{attrs:{content:e.msg}})],1)],1)],1),e.levelData.length?n("v-uni-view",{staticClass:"footer_content"},[n("v-uni-view",{staticClass:"button_box"},[0!=e.dredgeLevel?n("u-button",{attrs:{text:"立即开通",loading:e.buttonLoading,customStyle:e.buttonStyle},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.dredgeDealer.apply(void 0,arguments)}}}):e._e()],1)],1):e._e()],1)},i=[]},d0ff:function(e,t,n){"use strict";n("7a82"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return(0,a.default)(e)||(0,o.default)(e)||(0,i.default)(e)||(0,r.default)()};var a=l(n("4053")),o=l(n("a9e0")),i=l(n("dde1")),r=l(n("10eb"));function l(e){return e&&e.__esModule?e:{default:e}}},d6af:function(e,t,n){var a=n("24fb");t=a(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.page_content[data-v-76168c36]{position:relative}.page_content[data-v-76168c36]::before{content:"";width:100%;height:%?410?%;position:absolute;top:0;left:0;z-index:0;background-image:url(https://img.nymaite.com/video_short/images/v_bg.png);background-repeat:no-repeat;background-size:auto 100%;background-position:110% 100%}.page_content .head_content[data-v-76168c36]{background:#f9f9fb}.page_content .main_content[data-v-76168c36]{overflow-x:hidden;padding-bottom:%?200?%}.page_content .main_content .info_box[data-v-76168c36]{width:140%;background:#f9f9fb;border-radius:0 0 50% 50%;margin-left:-20%;padding:0 calc(20% + %?32?%);overflow:hidden}.page_content .main_content .info_box .user[data-v-76168c36]{position:relative;display:flex;align-items:center;margin-top:%?10?%}.page_content .main_content .info_box .user .avatar[data-v-76168c36]{width:%?114?%;height:%?114?%;border-radius:50%;overflow:hidden}.page_content .main_content .info_box .user .avatar .image[data-v-76168c36]{width:100%;height:100%}.page_content .main_content .info_box .user .content[data-v-76168c36]{margin-left:%?24?%}.page_content .main_content .info_box .user .content .p1[data-v-76168c36]{font-size:%?32?%;color:#333;margin-bottom:%?8?%;font-weight:700}.page_content .main_content .info_box .user .content .p2[data-v-76168c36]{font-size:%?28?%;color:#999}.page_content .main_content .info_box .card[data-v-76168c36]{position:relative;height:%?120?%;margin-top:%?10?%;border-radius:%?16?%;padding:%?32?% %?40?% 0 %?40?%;background:linear-gradient(.749turn,#5c5660,#39343b)}.page_content .main_content .info_box .card[data-v-76168c36]::before{content:"";width:100%;height:100%;position:absolute;top:0;left:0;background-image:url(https://img.nymaite.com/video_short/images/line_bg.png);background-repeat:no-repeat;background-size:auto 110%;background-position:50% 0}.page_content .main_content .info_box .card .texts[data-v-76168c36]{position:relative;display:flex;align-items:center;justify-content:space-between}.page_content .main_content .info_box .card .texts .text1[data-v-76168c36]{font-size:%?36?%;color:#feb787;font-weight:700}.page_content .main_content .info_box .card .texts .text2[data-v-76168c36]{font-size:%?24?%;color:#fff}.page_content .main_content .info_box .card .texts .text3[data-v-76168c36]{font-size:%?24?%;color:#fff}.page_content .main_content .content_box[data-v-76168c36]{padding:%?32?%}.page_content .main_content .content_box .level_box[data-v-76168c36]{position:relative;background:#fff}.page_content .main_content .content_box .level_box .title[data-v-76168c36]{font-size:%?36?%;font-weight:700;color:#272d2f}.page_content .main_content .content_box .level_box .list_box[data-v-76168c36]{margin-top:%?36?%;display:flex;flex-wrap:wrap;justify-content:space-between}.page_content .main_content .content_box .level_box .list_box .item[data-v-76168c36]{position:relative;min-width:calc((100% - %?60?%) / 3);border-radius:%?20?%;padding:%?72?% %?20?% %?36?% %?20?%;border:%?4?% solid transparent;background:#4d4d4d;margin:0 %?0?% %?30?% 0}.page_content .main_content .content_box .level_box .list_box .item[data-v-76168c36]:nth-child(3n){margin-right:0}.page_content .main_content .content_box .level_box .list_box .item.active[data-v-76168c36]{background-clip:padding-box,border-box;background-origin:padding-box,border-box;background-image:linear-gradient(90deg,#000,#000),linear-gradient(90deg,#f28d48,#feb685)}.page_content .main_content .content_box .level_box .list_box .item .text1[data-v-76168c36]{font-size:%?28?%;color:#fff}.page_content .main_content .content_box .level_box .list_box .item .text2[data-v-76168c36]{font-size:%?36?%;color:#f28c46;margin:%?8?% 0}.page_content .main_content .content_box .level_box .list_box .item .text2 .num[data-v-76168c36]{font-size:%?64?%;font-weight:900}.page_content .main_content .content_box .level_box .list_box .item .text3[data-v-76168c36]{font-size:%?24?%;color:#fff}.page_content .main_content .content_box .level_box .list_box .item .tip[data-v-76168c36]{font-size:%?24?%;font-weight:700;color:#fff;padding:%?8?% %?16?%;border-radius:0 %?20?% 0 %?20?%;background:linear-gradient(90deg,#f28b45,#feb787);position:absolute;top:%?-4?%;right:%?-4?%}.page_content .main_content .content_box .text_box[data-v-76168c36]{margin-top:%?40?%;border-radius:%?16?%;box-shadow:0 0 %?60?% 0 hsla(0,0%,40%,.15);padding:%?40?%;font-size:%?28?%;color:#333;overflow:hidden}.page_content .main_content .content_box .text_box .title[data-v-76168c36]{font-weight:700;padding-bottom:%?36?%;border-bottom:%?2?% solid #ddd;margin-bottom:%?36?%}.page_content .footer_content[data-v-76168c36]{width:100%;padding:0 %?32?%;position:fixed;bottom:%?40?%;left:0}',""]),e.exports=t},de32:function(e,t,n){var a=n("d6af");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var o=n("4f06").default;o("5078c958",a,!0,{sourceMap:!1,shadowMode:!1})}}]);