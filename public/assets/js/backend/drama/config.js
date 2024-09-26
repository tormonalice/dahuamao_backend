define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            var configIndex = new Vue({
                el: "#configIndex",
                data() {
                    return {
                        activeName: "basic",
                        configData: {
                            basic: [],
                            platform: [],
                            payment: []
                        }
                    }
                },
                mounted() {
                    if(Config.site_id == 0){
                        this.configData['basic'] = [
                        ];
                        this.configData['platform'] = [
                        ];
                    }else{
                        this.configData['basic'] = [{
                            id: 'drama',
                            title: '系统信息',
                            tip: '配置系统基本信息',
                            message: '系统名称、Logo',
                            icon: 'drama-icon',
                            leaf: '#6ACAA5',
                            background: 'linear-gradient(180deg, #BAF0DD 0%, #51BC99 100%)',
                            url: "{:url(drama.config/platform?type=drama')}",
                            button: {
                                background: '#E0F1EB',
                                color: '#13986C'
                            },
                        }, {
                            id: 'user',
                            title: '会员配置',
                            tip: '配置默认会员信息',
                            message: '默认昵称、头像',
                            icon: 'user-icon',
                            leaf: '#E0B163',
                            background: 'linear-gradient(180deg, #FCE6B7 0%, #E9A848 100%)',
                            button: {
                                background: '#F7EDDD',
                                color: '#B07318'
                            },
                        }, {
                            id: 'sms',
                            title: '短信配置',
                            tip: '配置短信服务',
                            message: '登陆注册、短信验证',
                            icon: 'score-icon',
                            leaf: '#EC9371',
                            background: 'linear-gradient(180deg, #FADDC0 0%, #E47F6D 100%)',
                            button: {
                                background: '#F6E5E1',
                                color: '#D75125'
                            },
                        }, {
                            id: 'share',
                            title: '分享配置',
                            tip: '配置默认分享信息',
                            message: '分享标题、图片、海报背景',
                            icon: 'share-icon',
                            leaf: '#915CF9',
                            background: 'linear-gradient(180deg, #D5B8FA 0%, #8F62C9 100%)',
                            button: {
                                background: '#E7DEF6',
                                color: '#6625CF'
                            },
                        }, {
                            id: 'withdraw',
                            title: '提现',
                            tip: '配置默认提现规则',
                            message: '手续费、最小最大金额',
                            icon: 'withdraw-icon',
                            leaf: '#EA6670',
                            background: 'linear-gradient(180deg, #FCB7BE 0%, #D36068 100%)',
                            button: {
                                background: '#F3DCDE',
                                color: '#D61226'
                            },
                        },{
                            id: 'wxguanggao',
                            title: '微信小程序广告配置',
                            tip: '配置小程序广告',
                            message: '广告开关，id',
                            icon: 'withdraw-icon',
                            leaf: '#EA6670',
                            background: 'linear-gradient(180deg, #FED1BE 0%, #ED2266 100%)',
                            button: {
                                background: '#F3DDDE',
                                color: '#D61D26'
                            },
                        }];
                        if(typeof Config.addons['uploads'] != 'undefined' && Config.addons['uploads']['state'] == 1){
                            this.configData['basic'].push({
                                id: 'uploads',
                                title: '云存储配置',
                                tip: '配置云存储',
                                message: '阿里云OSS、腾讯云COS',
                                icon: 'store-icon',
                                leaf: '#487EE5',
                                background: 'linear-gradient(180deg, #84C4FF 0%, #3C68BE 100%)',
                                button: {
                                    background: '#DFE7EE',
                                    color: '#1C54BD'
                                },
                            });
                        }
                        this.configData['basic'].push({
                            id: 'mgg',
                            title: '免广告配置',
                            tip: '免广告开关',
                            message: '免广告开关',
                            icon: 'drama-icon',
                            leaf: '#6ACAA5',
                            background: 'linear-gradient(180deg, #BAF0DD 0%, #51BC99 100%)',
                            button: {
                                background: '#E0F1EB',
                                color: '#13986C'
                            },
                        }/*,{
                            id: 'usersign',
                            title: '福利配置',
                            tip: '配置签到、转盘规则',
                            message: '签到金币、连续签到、转盘抽奖规则',
                            icon: 'user-icon',
                            leaf: '#E0B163',
                            background: 'linear-gradient(180deg, #FCE6B7 0%, #E9A848 100%)',
                            button: {
                                background: '#F7EDDD',
                                color: '#B07318'
                            },
                        },{
                            id: 'batch',
                            title: '购买优惠配置',
                            tip: '批量购买优惠价格',
                            message: '积分不足充值时批量购买剧集给予优惠',
                            icon: 'score-icon',
                            leaf: '#EC9371',
                            background: 'linear-gradient(180deg, #FADDC0 0%, #E47F6D 100%)',
                            button: {
                                background: '#F6E5E1',
                                color: '#D75125'
                            },
                        }*/);
                        this.configData['platform'] = [{
                            id: 'wxOfficialAccount',
                            title: '微信公众号',
                            tip: '配置微信公众号',
                            message: 'AppId、AppSecret、自动登录',
                            icon: 'wxOfficialAccount-icon',
                            leaf: '#6ACAA4',
                            background: 'linear-gradient(180deg, #AAF0D7 0%, #5CC09F 100%)',
                            buttonMessage: '公众号设置',
                            button: {
                                background: '#DEF0EA',
                                color: '#0EA753'
                            },
                        },{
                            id: 'wxMiniProgram',
                            title: '微信小程序',
                            tip: '配置微信小程序',
                            message: 'AppId、AppSecret、自动登录',
                            icon: 'wxMiniProgram-icon',
                            leaf: '#6962F7',
                            background: 'linear-gradient(180deg, #C1BFFF 0%, #6563C9 100%)',
                            buttonMessage: '小程序设置',
                            button: {
                                background: '#D8D8F1',
                                color: '#3932BF'
                            },
                        }, {
                            id: 'App',
                            title: 'App',
                            tip: '配置App平台',
                            message: '生成App实现多端同步使用',
                            icon: 'App-icon',
                            leaf: '#6990E6',
                            background: 'linear-gradient(180deg, #BED6FF 0%, #6785CD 100%)',
                            buttonMessage: 'App设置',
                            button: {
                                background: '#DAE1F6',
                                color: '#1666D3'
                            },
                        },{
                            id: 'H5',
                            title: 'H5',
                            tip: '配置H5平台',
                            message: '微信H5支付信息',
                            icon: 'h5-icon',
                            leaf: '#EC9371',
                            background: 'linear-gradient(180deg, #FABCC0 0%, #E5806D 100%)',
                            buttonMessage: 'H5设置',
                            button: {
                                background: '#F7E6E1',
                                color: '#D75E37'
                            }
                        },{
                            id: 'douyin',
                            title: '抖音',
                            tip: '配置抖音平台',
                            message: 'AppId、AppSecret',
                            icon: 'App-icon',
                            leaf: '#32BFFF',
                            background: 'linear-gradient(180deg, #56BFFF 0%, #6563C9 100%)',
                            buttonMessage: '抖音设置',
                            button: {
                                background: '#D8D8F1',
                                color: '#3932BF'
                            },
                        }];
                        this.configData['payment'] = [{
                            id: 'wechat',
                            title: '微信支付',
                            tip: '',
                            message: '',
                            icon: 'wechat-icon',
                            leaf: '#6ACAA4',
                            background: 'linear-gradient(180deg, #AAF0D7 0%, #5DC1A0 100%)',
                            button: {
                                background: '#DEF0EA',
                                color: '#0EA753'
                            },
                        }, {
                            id: 'alipay',
                            title: '支付宝支付',
                            tip: '',
                            message: '',
                            icon: 'alipay-icon',
                            leaf: '#6990E6',
                            background: 'linear-gradient(180deg, #BFD6FF 0%, #6786CE 100%)',
                            button: {
                                background: '#DAE1F6',
                                color: '#005AD7',
                                cursor: 'auto'
                            },
                        }, {
                            id: 'xunipay',
                            title: '小程序虚拟支付',
                            tip: '',
                            message: '',
                            icon: 'wechat-icon',
                            leaf: '#6962F7',
                            background: 'linear-gradient(180deg, #C1BFFF 0%, #6563C9 100%)',
                            button: {
                                background: '#D8D8F1',
                                color: '#3932BF',
                                cursor: 'auto'
                            },
                        }, {
                            id: 'douyinpay',
                            title: '抖音支付',
                            tip: '',
                            message: '',
                            icon: 'App-icon',
                            leaf: '#6962F7',
                            background: 'linear-gradient(180deg, #C1CEFF 0%, #2363C9 100%)',
                            button: {
                                background: '#D5D8F1',
                                color: '#6232BF',
                                cursor: 'auto'
                            },
                        }];
                    }
                },
                methods: {
                    tabClick(tab, event) {
                        this.activeName = tab.name;
                    },
                    operation(id, title) {
                        let that = this;
                        Fast.api.open("drama/config/platform?type=" + id + "&tab=" + that.activeName + "&title=" + title, title);
                    },
                },
            })
        },
        platform: function () {
            Vue.directive('enterInteger', {
                inserted: function (el) {
                    const input = el.nodeName === 'INPUT' ? el : el.getElementsByTagName('input')[0]
                    const fn = (e) => {
                        input.value = input.value.replace(/(^[^1-9])|[^\d]/g, '')
                        const ev = document.createEvent('HTMLEvents')
                        ev.initEvent('input', true, true)
                        input.dispatchEvent(ev)
                    }
                    input.onkeyup = fn
                    input.onblur = fn
                }
            });
            function debounce(handle, delay) {
                let time = null;
                return function () {
                    let self = this,
                        arg = arguments;
                    clearTimeout(time);
                    time = setTimeout(function () {
                        handle.apply(self, arg);
                    }, delay)
                }
            }
            var configPlatform = new Vue({
                el: "#configPlatform",
                data() {
                    return {
                        platformData: {
                            drama: {
                                name: '',
                                domain: '',
                                h5: '',
                                h5_theme: 'default',
                                // import: '',
                                version: '',
                                logo: '',
                                logo_arr: [],
                                company: '',
                                company_arr: [],
                                copyright: {list: []},
                                mobile_switch: '',
                                android_autoplay: '',
                                mobile: '',
                                email: '',
                                wechat: '',
                                user_protocol: '',
                                privacy_protocol: '',
                                about_us: '',
                                contact_us: '',
                                legal_notice: '',
                                usable_desc: '',
                                vip_desc: '',
                                reseller_desc: '',
                                jfname: '',
                                jfimage: '',
                                jfimage_arr: '',
                                kf_switch: '0',
                                kfyd: '',
                                kfyd_arr: '',
                                kftc: '',
                                kftc_arr: '',
                            },
                            user: {
                                nickname: '',
                                avatar: '',
                                avatar_arr: [],
                                group_id: '',
                                money: '',
                                score: ''
                            },
                            share: {
                                title: '',
                                description: '',
                                image: '',
                                image_arr: [],
                                user_poster_bg: '',
                                user_poster_bg_arr: [],
                                user_poster_bg_color: '',
                                msg_title_bg: '',
                                msg_title_bg_arr: [],
                                msg_title_bg_color: '',
                                msg_title_bg_color_arr: ['#F44336', '#E91E63', '#9C27B0', '#673AB7', '#3F51B5', '#2196F3',
                                    '#03A9F4', '#00BCD4', '#009688', '#4CAF50', '#8BC34A', '#FFEB3B',
                                    '#FFC107', '#FF9800', '#FF5722', '#795548', '#9E9E9E', '#607D8B'],
                            },
                            sms: {
                                type: 'alisms',
                                alisms: {
                                    key: '',
                                    secret: '',
                                    sign: '',
                                    template: [
                                        {key: 'register', value: 'SMS_114000000'},
                                        {key: 'resetpwd', value: 'SMS_114000000'},
                                        {key: 'changepwd', value: 'SMS_114000000'},
                                        {key: 'changemobile', value: 'SMS_114000000'},
                                        {key: 'profile', value: 'SMS_114000000'},
                                        {key: 'notice', value: 'SMS_114000000'},
                                        {key: 'mobilelogin', value: 'SMS_114000000'},
                                        {key: 'bind', value: 'SMS_114000000'}
                                    ]
                                },
                                hwsms: {
                                    app_url: '',
                                    key: '',
                                    secret: '',
                                    sender: '',
                                    sign: '',
                                    template: [
                                        {key: 'register', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'resetpwd', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'changepwd', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'changemobile', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'profile', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'notice', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'mobilelogin', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'bind', value: '8ff55xxxxxxxxxxxxxxxxxxx'}
                                    ]
                                },
                                qcloudsms: {
                                    appid: '',
                                    appkey: '',
                                    sign: '',
                                    isTemplateSender: '',
                                    template: [
                                        {key: 'register', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'resetpwd', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'changepwd', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'changemobile', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'profile', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'notice', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'mobilelogin', value: '8ff55xxxxxxxxxxxxxxxxxxx'},
                                        {key: 'bind', value: '8ff55xxxxxxxxxxxxxxxxxxx'}
                                    ]
                                },
                                baosms: {
                                    username: '',
                                    password: '',
                                    sign: '',
                                },
                            },
                            withdraw: {
                                methods: [],
                                wechat_alipay_auto: 0,
                                service_fee: '',
                                min: '',
                                max: '',
                                perday_amount: 0,
                                perday_num: 0,
                            },
                            wxOfficialAccount: {
                                name: '',
                                wx_type: '4',
                                avatar: '',
                                qrcode: '',
                                avatar_arr: [],
                                qrcode_arr: [],
                                app_id: '',
                                secret: '',
                                url: '',
                                token: '',
                                aes_key: '',
                                // auto_login: '',
                                status: ''
                            },
                            wxMiniProgram: {
                                name: '',
                                avatar: '',
                                qrcode: '',
                                avatar_arr: [],
                                qrcode_arr: [],
                                app_id: '',
                                secret: '',
                                apple_pay: '0',
                                uniad_switch: '0',
                                adpid: '',
                                meizi_switch: '0',
                                url: '',
                                token: '',
                                aes_key: '',
                                jiami: '1',
                                geshi: '1',
                            },
                            H5: {
                                app_id: '',
                                secret: '',
                            },
                            douyin: {
                                appid: '',
                                appsecret: '',
                                meizi_switch: '0'
                            },
                            App: {
                                app_id: '',
                                secret: '',
                            },
                            wechat: {
                                platform: [],
                                mch_id: '',
                                key: '',
                                sub_key: '',
                                cert_client: '',
                                cert_key: '',
                                sub_cert_client: '',
                                sub_cert_key: '',
                                mode: 'normal',
                                sub_mch_id: '',
                                app_id: '',
                            },
                            wechatv3: {
                                platform: [],
                                mode: 0, // 0|2
                                app_id: '',
                                mch_id: '',
                                mch_secret_key: '',
                                mch_secret_cert: '',
                                mch_public_cert_path: '',
                                sub_mch_id: '',
                                sub_mch_secret_key: '',
                                sub_mch_secret_cert: '',
                                sub_mch_public_cert_path: '',
                            },
                            alipay: {
                                platform: [],
                                app_id: '',
                                ali_public_key: '',
                                app_cert_public_key: '',
                                alipay_root_cert: '',
                                private_key: '',
                                mode: 'normal',
                                pid: '',
                            },
                            xunipay: {
                                pay_switch: '0',
                                app_id: '',
                                offer_id: '',
                                app_key: ''
                            },
                            douyinpay: {
                                appid: '',
                                merchant_uid: '',
                                private_key: '',
                                public_key:'',
                                key_version:'',
                                url: '',
                                token: '',
                                salt: '',
                            },
                            wallet: {
                                platform: [],
                            },
                            uploads: {
                                upload_type: '',
                                alioss: {
                                    'accessKeyId': '',
                                    'accessKeySecret': '',
                                    'bucket': '',
                                    'endpoint': '',
                                    'cdnurl': '',
                                    'uploadmode': 'server',
                                    'serverbackup': '1',
                                    'savekey': '/uploads/{year}{mon}{day}/{random}_{filemd5}{.suffix}',
                                    'expire': '600',
                                    'maxsize': '1024M',
                                    'mimetype': 'jpg,png,bmp,jpeg,gif,webp,zip,rar,wav,mp4,mp3,webm,pem,xls,m3u8,avi,mov,ipa,xlsx,apk',
                                    'multiple': '0',
                                    'thumbstyle': '',
                                    'chunking': '0',
                                    'chunksize': '4194304',
                                    'syncdelete': '1',
                                    'apiupload': '1',
                                    'noneedlogin': '',
                                    'noneedloginarr': [],
                                },
                                cos: {
                                    'appId': '',
                                    'secretId': '',
                                    'secretKey': '',
                                    'bucket': '',
                                    'region': '',
                                    'uploadmode': 'server',
                                    'serverbackup': '1',
                                    'uploadurl': '',
                                    'cdnurl': '',
                                    'savekey': '/uploads/{year}{mon}{day}/{random}_{filemd5}{.suffix}',
                                    'expire': '600',
                                    'maxsize': '1024M',
                                    'mimetype': 'jpg,png,bmp,jpeg,gif,webp,zip,rar,wav,mp4,mp3,webm,pem,xls,m3u8,avi,mov,ipa,xlsx,apk',
                                    'multiple': '0',
                                    'thumbstyle': '',
                                    'chunking': '0',
                                    'chunksize': '4194304',
                                    'syncdelete': '1',
                                    'apiupload': '1',
                                    'noneedlogin': '',
                                    'noneedloginarr': [],
                                },
                            },
                            wxguanggao: {
                                gg_dingdan_id: '',
                                gg_dingdan_switch: '',
                                gg_xiahua_id: '',
                                gg_xiahua_switch: '',
                                gg_xiahuatc_id: '',
                                gg_xiahuatc_switch: '',
                                gg_shouye_id: '',
                                gg_shouye_switch: '',
                                gg_xuanji_id: '',
                                gg_xuanji_switch: '',
                                gg_liebiao_id: '',
                                gg_liebiao_switch: '',
                                gg_zhuiju_id: '',
                                gg_zhuiju_switch: '',
                                gg_zanting_id: '',
                                gg_zanting_switch: '',
                            },
                            mgg:{
                                mgg_switch: '0',
                            },
                            usersign: {
                                everyday: 0, // 每日签到固定积分
                                is_inc: 0, // 是否递增签到
                                inc_num: 0, // 递增奖励
                                until_day: 0, // 递增持续天数
                                is_discounts: '0', // 是否连续奖励
                                discounts: [], // 连续签到奖励 {full:5, value:10}
                                is_replenish: 0, // 是否开启补签
                                replenish_days: 1, // 可补签天数 最小1
                                replenish_limit: 0, // 补签事件限制，0 不限制
                                replenish_num: 1, // 补签所消耗积分
                                richtext: {
                                    richtext_id:'',
                                    richtext_title:''
                                },
                                mianfei: 0,
                                shangxian: 0,
                                guanggao_weixin: '',
                                guanggao_douyin: ''
                            },
                            batch:{
                                is_piliang: '0',
                                auto_num: '',
                                is_discounts: '0',
                                discounts: [],
                            }
                        },
                        type: new URLSearchParams(location.search).get('type'),
                        tab: new URLSearchParams(location.search).get('tab'),
                        title: new URLSearchParams(location.search).get('title'),
                        groupList: [],
                        detailForm: {},
                        must_delete: ['logo_arr', 'company_arr', 'avatar_arr', 'image_arr', 'msg_title_bg_arr', 'user_poster_bg_arr', 'msg_title_bg_color_arr', 'qrcode_arr', 'area_arr'],
                        // expressAddress: window.location.origin + '/addons/drama/express/callback',
                        deliverCompany: [],
                        uploadmodeList: [
                            {'model': 'client', 'name': '客户端直传(速度快,无备份)'},
                            {'model': 'server', 'name': '服务器中转(占用服务器带宽,可备份)'},
                        ],
                    }
                },
                mounted() {
                    this.operationData();
                    // if (this.type == 'services') {
                    //     this.getDeliverCompany();
                    //     this.getAreaOptions()
                    // }
                },
                methods: {
                    backupDownload() {
                        this.$confirm('下载图片等静态资源文件?', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            window.open('backup_download', '_blank');
                            return false;
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取资源文件下载'
                            });
                        });
                    },
                    // radio取消选中
                    setUploadType(val) {
                        this.detailForm = this.platformData['uploads'];
                        val === this.detailForm.upload_type ? this.detailForm.upload_type = '' : this.detailForm.upload_type = val;
                    },
                    //添加主规格
                    addMain() {
                        this.detailForm = this.platformData['drama']
                        this.detailForm.copyright.list.push({
                            image: '',
                            name: '',
                            url: '',
                        })
                    },
                    //删除主规格
                    deleteMain(k) {
                        // 删除主规格
                        this.detailForm = this.platformData['drama']
                        this.detailForm.copyright.list.splice(k, 1)
                    },
                    selectColor(type, color) {
                        this.detailForm = this.platformData['share']
                        if(type == 'user_poster_bg_color'){
                            this.detailForm.user_poster_bg_color = color;
                        }else if(type == 'msg_title_bg_color'){
                            this.detailForm.msg_title_bg_color = color;
                        }
                        this.$emit('color-selected', color);
                    },
                    operationData() {
                        this.detailForm = this.platformData[this.type]
                        if (Config.row) {
                            for (key in this.detailForm) {
                                if (Config.row[key]) {
                                    if (Config.row[key] instanceof Object) {
                                        for (inner in Config.row[key]) {
                                            if (Config.row[key][inner]) {
                                                this.detailForm[key][inner] = Config.row[key][inner]
                                            }
                                        }
                                    } else {
                                        this.detailForm[key] = Config.row[key]
                                    }
                                }
                            }
                        }
                        if (this.type == 'drama') {
                            this.detailForm.logo_arr = []
                            this.detailForm.logo_arr.push(Fast.api.cdnurl(this.detailForm.logo))
                            this.detailForm.company_arr = []
                            this.detailForm.company_arr.push(Fast.api.cdnurl(this.detailForm.company))
                        } else if (this.type == 'user') {
                            this.groupList = Config.groupList
                            this.detailForm.avatar_arr = []
                            this.detailForm.avatar_arr.push(Fast.api.cdnurl(this.detailForm.avatar))
                        } else if (this.type == 'share') {
                            this.detailForm.image_arr = []
                            this.detailForm.image_arr.push(Fast.api.cdnurl(this.detailForm.image))
                            this.detailForm.msg_title_bg_arr = []
                            this.detailForm.msg_title_bg_arr.push(Fast.api.cdnurl(this.detailForm.msg_title_bg))
                            this.detailForm.user_poster_bg_arr = []
                            this.detailForm.user_poster_bg_arr.push(Fast.api.cdnurl(this.detailForm.user_poster_bg))
                        } else if (this.type == 'withdraw') {
                            this.detailForm.service_fee = this.detailForm.service_fee * 100
                        } else if (this.type == 'services') {
                            // this.detailForm.area_arr = []
                            // if (this.detailForm.express && this.detailForm.express.Sender) {
                            //     this.detailForm.area_arr = [this.detailForm.express.Sender.ProvinceName, this.detailForm.express.Sender.CityName, this.detailForm.express.Sender.ExpAreaName]
                            // }
                        } else if (this.type == 'chat') {
                            if (!this.detailForm.system.ssl_type) {
                                this.$set(this.detailForm.system, 'ssl_type', 'cert')
                            }
                        } else if (this.type == 'wxOfficialAccount') {
                            this.detailForm.avatar_arr = []
                            this.detailForm.avatar_arr.push(Fast.api.cdnurl(this.detailForm.avatar))
                            this.detailForm.qrcode_arr = []
                            this.detailForm.qrcode_arr.push(Fast.api.cdnurl(this.detailForm.qrcode))
                        } else if (this.type == 'wxMiniProgram') {
                            this.detailForm.avatar_arr = []
                            this.detailForm.avatar_arr.push(Fast.api.cdnurl(this.detailForm.avatar))
                            this.detailForm.qrcode_arr = []
                            this.detailForm.qrcode_arr.push(Fast.api.cdnurl(this.detailForm.qrcode))
                        } else if (this.type == 'uploads') {
                            if(this.detailForm.alioss.noneedlogin){
                                this.detailForm.alioss.noneedloginarr = this.detailForm.alioss.noneedlogin.split(',');
                            }
                            if(this.detailForm.cos.noneedlogin){
                                this.detailForm.cos.noneedloginarr = this.detailForm.cos.noneedlogin.split(',');
                            }
                        }
                        else if (this.type == 'usersign') {
                            const richtext = {};
                            Object.values(this.langOptions).forEach((item, index) => {
                                if (this.detailForm.richtext.hasOwnProperty(item)) {
                                    richtext[item] = this.detailForm.richtext[item];
                                } else {
                                    richtext[item] = {};
                                }
                            });
                            this.detailForm.richtext = richtext;
                        }
                    },
                    addMainSku(type) {
                        this.detailForm = this.platformData['sms']
                        if(type == 'alisms'){
                            this.detailForm.alisms.template.push({
                                key: '',
                                value: '',
                            })
                        }else if(type == 'hwsms'){
                            this.detailForm.hwsms.template.push({
                                key: '',
                                value: '',
                            })
                        }else if(type == 'qcloudsms'){
                            this.detailForm.qcloudsms.template.push({
                                key: '',
                                value: '',
                            })
                        }
                    },
                    deleteMainSku(type, k) {
                        this.detailForm = this.platformData['sms']
                        if(type == 'alisms'){
                            this.detailForm.alisms.template.splice(k, 1)
                        }else if(type == 'hwsms'){
                            this.detailForm.hwsms.template.splice(k, 1)
                        }else if(type == 'qcloudsms'){
                            this.detailForm.qcloudsms.template.splice(k, 1)
                        }
                    },

                    richtextSelect(field) {
                        let that = this;
                        if(field == 'usersign'){
                            Fast.api.open("drama/richtext/select?multiple=false", "选择协议", {
                                callback: function (data) {
                                    that.detailForm.richtext['richtext_id'] = String(data.data.id);
                                    that.detailForm.richtext['richtext_title'] = String(data.data.title);
                                }
                            });
                        }else{
                            Fast.api.open("drama/richtext/select?multiple=false", "选择协议", {
                                callback: function (data) {
                                    that.detailForm[field] = data.data.id;
                                }
                            });
                        }

                        return false;
                    },
                    keysSelect() {
                        Fast.api.open("drama/aikey/index", "Key池管理");
                        return false;
                    },
                    
                   
                    importTestData() {
                        let that = this;
                        Layer.alert('导入测试数据', {
                            btn: [__('导入数据'), __('暂不导入')],
                            title: __('Warning'),
                            yes: function (index) {
                                Fast.api.ajax({
                                    url: 'drama/config/testdata',
                                    data: {
                                        name: 'drama',
                                    }
                                }, function (data, ret) {
                                    that.detailForm['import'] = that.detailForm['version'];
                                    Layer.close(index);
                                });
                            },
                            icon: 1
                        });
                    },

                    attachmentSelectArr(k) {
                        let that = this;
                        Fast.api.open("general/attachment/select?multiple=false", "选择", {
                            callback: function (data) {
                                that.detailForm = that.platformData['drama'];
                                that.detailForm.copyright.list[k]['image'] = data.url;
                            }
                        });
                        return false;
                    },
                    delImgArr(k) {
                        let that = this;
                        that.detailForm = that.platformData['drama'];
                        that.detailForm.copyright.list[k]['image'] = '';
                    },

                    attachmentSelect(type, field) {
                        let that = this;
                        Fast.api.open("general/attachment/select?multiple=false", "选择", {
                            callback: function (data) {
                                switch (type) {
                                    case "image":
                                        that.detailForm[field] = data.url;
                                        that.detailForm[field + '_arr'] = data.url;
                                        break;
                                    // case "file":
                                    //     that.detailForm[field] = data.url;
                                    //     break;
                                    case "ssl":
                                        that.detailForm.system[field] = data.url;
                                        break;
                                }
                            }
                        });
                        return false;
                    },
                    delImg(type, field) {
                        let that = this;
                        switch (type) {
                            case "image":
                                that.detailForm[field] = '';
                                that.detailForm[field + '_arr'] = [];
                                break;
                            case "file":
                                that.detailForm[field] = '';
                                break;
                        }
                    },
                    submitFrom(type) {
                        let that = this;
                        if (type == 'yes') {
                            let submitData = JSON.parse(JSON.stringify(that.detailForm))
                            if (that.type == 'wxMiniProgram') {
                                if(submitData.uniad_switch == 1 && submitData.adpid == ''){
                                    that.$notify({
                                        title: '异常',
                                        message: "开启激励视频广告需要输入广告位id",
                                        type: 'error'
                                    });
                                    return false;
                                }
                            }
                            if (that.type == 'withdraw') {
                                submitData.service_fee = (Number(submitData.service_fee) / 100).toFixed(3)
                            }
                            if (that.type == 'services') {
                                // submitData.express.Sender.ProvinceName = submitData.area_arr[0];
                                // submitData.express.Sender.CityName = submitData.area_arr[1];
                                // submitData.express.Sender.ExpAreaName = submitData.area_arr[2];
                            }
                            if (that.type == 'uploads') {
                                submitData.alioss.noneedlogin = submitData.alioss.noneedloginarr.join(',');
                                submitData.cos.noneedlogin = submitData.cos.noneedloginarr.join(',');
                            }
                            that.must_delete.forEach(i => {
                                if (submitData[i]) {
                                    delete submitData[i]
                                }
                            });
                            Fast.api.ajax({
                                url: 'drama/config/platform?type=' + that.type,
                                loading: true,
                                type: 'POST',
                                data: {
                                    data: JSON.stringify(submitData),
                                    group: that.tab,
                                    title: that.title
                                },
                            }, function (ret, res) {
                                Fast.api.close()
                            })
                        } else {
                            Fast.api.close()
                        }
                    },
                    getDeliverCompany(searchWhere = '') {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/express/select',
                            loading: false,
                            type: 'GET',
                            data: {
                                searchWhere: searchWhere,
                            }
                        }, function (ret, res) {
                            that.deliverCompany = res.data.rows;
                            return false
                        })
                    },
                    deliverDebounceFilter: debounce(function (searchWhere) {
                        this.getDeliverCompany(searchWhere)
                    }, 1000),
                    deliverFilter(searchWhere) {
                        this.deliverDebounceFilter(searchWhere);
                    },
                    getAreaOptions() {
                        var that = this;
                        Fast.api.ajax({
                            url: `drama/area/select`,
                            loading: false,
                            type: 'GET',
                        }, function (ret, res) {
                            that.areaOptions = res.data;
                            return false;
                        })
                    },
                    getBalance(apiKey, type, mode) {
                        let that = this;
                        $.ajax({
                            type: 'GET',
                            url: `drama/ajax/balance?apiKey=`+apiKey+'&site_id='+Config.site_id+'&type='+type+'&mode='+mode,
                            cache: false,
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if(data.code == 1){
                                    that.$notify({
                                        title: '余额',
                                        message: '总：$'+data.data.total_granted+'，已用：$'+data.data.total_used+'，剩余：$'+data.data.total_available,
                                        type: 'success'
                                    });
                                }else{
                                    that.$notify({
                                        title: 'AI异常',
                                        message: data.msg,
                                        type: 'error'
                                    });
                                }
                            }
                        })
                    },
                    changeWechatType() {
                        for (key in this.detailForm) {
                            if (key != 'mode' && key != 'platform') {
                                this.detailForm[key] = ''
                            }
                        }
                    },
                    ajaxUpload(id) {
                        let that = this;
                        var formData = new FormData();
                        formData.append("file", $('#' + id)[0].files[0]);
                        $.ajax({
                            type: "post",
                            url: "ajax/upload",
                            data: formData,
                            cache: false,
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.code == 1) {
                                    that.detailForm[id] = data.data.url
                                } else {
                                    that.$notify({
                                        title: '警告',
                                        message: data.msg,
                                        type: 'warning'
                                    });
                                }
                            }
                        })
                    },
                    //连续签到条件
                    onChangeDiscounts() {
                        //this.detailForm = this.platformData['usersign']
                        this.detailForm.discounts = []
                    },
                    onDeleteDiscounts(index) {
                        //this.detailForm = this.platformData['usersign']
                        this.detailForm.discounts.splice(index, 1);
                    },
                    onAddDiscounts() {
                        //this.detailForm = this.platformData['usersign']
                        this.detailForm.discounts.push({
                            full: '',
                            value: '',
                        })
                    },
                    turntable(){
                        Fast.api.open("drama/turntable", "转盘配置");
                    }
                },
            })
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});