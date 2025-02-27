define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'toastr'], function ($, undefined, Backend, Table, Form, Toastr) {

    var Controller = {
        index: function () {
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
            var userIndex = new Vue({
                el: "#userIndex",
                data() {
                    return {
                        data: [],
                        // chooseType: 0,
                        // platformType: 'all',
                        // roleType: 'all',
                        // joinTime: '',
                        searchKey: '',

                        offset: 0,
                        limit: 10,
                        totalPage: 0,
                        currentPage: 1,

                    }
                },
                created() {
                    this.getData();
                },
                methods: {
                    getData() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/user/index',
                            loading: true,
                            type: 'GET',
                            data: {
                                searchWhere: that.searchKey,
                                // platform: that.platformType,
                                // role: that.roleType,
                                // jointime: that.joinTime,
                                offset: that.offset,
                                limit: that.limit,
                            },
                        }, function (ret, res) {
                            that.data = res.data.rows;
                            that.data.forEach(i => {
                                i.visible = false;
                            })
                            that.totalPage = res.data.total;
                            return false;
                        })
                    },
                    operation(type, id) {
                        let that = this;
                        switch (type) {
                            case 'platform':
                                that.platformType = id;
                                break;
                            case 'role':
                                that.roleType = id;
                                break;
                            case 'filter':
                                that.offset = 0;
                                that.limit = 10;
                                that.currentPage = 1;
                                that.getData();
                                break;
                            case 'clear':
                                that.platformType = 'all';
                                that.roleType = 'all';
                                break;
                            case 'edit':
                                Fast.api.open('drama/user/profile?id=' + id, '查看', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                            case 'del':
                                that.$confirm('此操作将永久直接删除用户, 是否继续?', '提示', {
                                    confirmButtonText: '确定',
                                    cancelButtonText: '取消',
                                    type: 'warning'
                                }).then(() => {
                                    Fast.api.ajax({
                                        url: 'drama/user/del/ids/' + id,
                                        loading: true,
                                        type: 'POST',
                                    }, function (ret, res) {
                                        that.getData();

                                    })
                                    return false;
                                }).catch(() => {
                                    that.$message({
                                        type: 'info',
                                        message: '已取消删除'
                                    });
                                });
                                break;
                            case 'platformname':
                                let type = ''
                                switch (id) {
                                    case 'H5':
                                        type = 'H5'
                                        break;
                                    case 'wxOfficialAccount':
                                        type = '微信公众号'
                                        break;
                                    case 'wxMiniProgram':
                                        type = '微信小程序'
                                        break;
                                    case 'App':
                                        type = 'App'
                                        break;
                                }
                                return type
                                break;
                            default:
                                Fast.api.open('drama/user/profile?id=' + type.id, '查看', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                        }
                    },
                    handleSizeChange(val) {
                        this.offset = 0
                        this.limit = val;
                        this.currentPage = 1;
                        this.getData()
                    },
                    handleCurrentChange(val) {
                        this.currentPage = val;
                        this.offset = (val - 1) * this.limit;
                        this.getData()
                    },
                    isShoose() {
                        this.chooseType == 0 ? 1 : 0;
                        if (this.chooseType == 0) {
                            this.activityType = 'all';
                            this.priceFrist = "";
                            this.priceLast = "";
                        }
                    },
                    tableRowClassName({
                        rowIndex
                    }) {
                        if (rowIndex % 2 == 1) {
                            return 'bg-color';
                        }
                        return '';
                    },
                    tableCellClassName({
                        columnIndex
                    }) {
                        if (columnIndex == 2 || columnIndex == 9) {
                            return 'cell-left';
                        }
                        return '';
                    },
                    debounceFilter: debounce(function () {
                        this.getData()
                    }, 1000),
                },
                watch: {
                    searchKey(newVal, oldVal) {
                        if (newVal != oldVal) {
                            this.offset = 0;
                            this.limit = 10;
                            this.currentPage = 1;
                            this.debounceFilter();
                        }
                    },
                },
            })
        },
        recyclebin: function () { },
        add: function () { },
        profile: function () {
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
            let formatterHtml = {
                time: (row, value) => {
                    return `${moment(row.createtime * 1000).format('YYYY-MM-DD HH:mm:ss')}`
                },
                image: (row, value) => {
                    return `<img src="/assets/addons/drama/img/decorate/${row.platform}.png" />`
                },
                shareUser: (row, value) => {
                    if (row.user) {
                        return `<img style="width:24px;height:24px;margin-right:10px" src="${Fast.api.cdnurl(row.user.avatar)}" /><div>${row.user.nickname}</div>`
                    }
                },
                changeNumber: (row, value) => {
                    let str = ""
                    if (row.money) {
                        str = `${row.money > 0 ? '+' + row.money : row.money}`
                    } else if (row.score) {
                        str = `${row.score > 0 ? '+' + row.score : row.score}`
                    } else if (row.usable) {
                        str = `${row.usable > 0 ? '+' + row.usable : row.usable}`
                    }
                    return str
                },
                shareMessage: (row, value) => {
                    // page  page_id
                    let str = ""
                    return str
                },
                goods: (row, value) => {
                    if (row.goods) {
                        return `<img style="width:24px;height:24px;margin-right:10px" src="${Fast.api.cdnurl(row.goods.image)}" /><div class="flex-1 ellipsis-item" style="text-align:left">${row.goods.title}</div>`
                    }
                },
                coupon: (row, value) => {
                    if (row.coupons) {
                        return `${row.coupons[value.field]}`
                    }
                },
                couponStatus: (row, value) => {
                    let str = ''
                    if (row.usetime) {
                        str = 1
                    } else {
                        str = 0
                    }
                    return str
                },
                operUser: (row) => {
                    let htmls = ''
                    if (row.oper) {
                        if (row.oper.avatar) {
                            htmls += `<img style="width:24px;height:24px;margin-right:10px" src="${Fast.api.cdnurl(row.oper.avatar)}" />`
                        }
                        if (row.oper.name) {
                            htmls += `<div class="ellipsis-item">${row.oper.name}</div>`
                        }
                    }
                    return htmls
                },
                operType: (row) => {
                    return row.oper ? row.oper.type : ''
                },
            }
            var userDetail = new Vue({
                el: "#userDetail",
                data() {
                    return {
                        data: {},
                        groupList: [],
                        upPassword: '',

                        activeStatus: 'money_log',
                        logList: [],
                        columns: {
                            'money_log': [{
                                type: 'time',
                                field: 'createtime',
                                title: '交易时间',
                                width: '160px',
                                formatter: formatterHtml.time,
                            }, {
                                type: 'text',
                                field: 'wallet',
                                title: '变动余额',
                                width: '120px',
                            }, {
                                type: 'text',
                                field: 'before',
                                title: '变更前',
                                width: '120px',
                            }, {
                                type: 'text',
                                field: 'after',
                                title: '剩余余额',
                                width: '120px',
                            }, {
                                type: 'htmls',
                                field: 'oper.type',
                                title: '操作人类型',
                                width: '120px',
                                formatter: formatterHtml.operType,
                            }, {
                                type: 'htmls',
                                field: 'oper',
                                title: '操作人',
                                width: '200px',
                                formatter: formatterHtml.operUser,
                            }, {
                                type: 'text',
                                field: 'memo',
                                title: '备注',
                                width: '400px',
                                align: 'left',
                            }],
                            'score_log': [{
                                type: 'time',
                                field: 'createtime',
                                title: '交易时间',
                                width: '160px',
                                formatter: formatterHtml.time,
                            }, {
                                type: 'text',
                                field: 'wallet',
                                title: '变动积分',
                                width: '120px',
                            }, {
                                type: 'text',
                                field: 'before',
                                title: '变更前',
                                width: '120px',
                            }, {
                                type: 'text',
                                field: 'after',
                                title: '剩余积分',
                                width: '120px',
                            }, {
                                type: 'htmls',
                                field: 'oper.type',
                                title: '操作人类型',
                                width: '120px',
                                formatter: formatterHtml.operType,
                            }, {
                                type: 'htmls',
                                field: 'oper',
                                title: '操作人',
                                width: '200px',
                                formatter: formatterHtml.operUser,
                            }, {
                                type: 'text',
                                field: 'memo',
                                title: '备注',
                                width: '400px',
                                align: 'left',
                            }],
                            'usable_log': [{
                                type: 'time',
                                field: 'createtime',
                                title: '交易时间',
                                width: '160px',
                                formatter: formatterHtml.time,
                            }, {
                                type: 'text',
                                field: 'wallet',
                                title: '变动剧场积分',
                                width: '120px',
                            }, {
                                type: 'text',
                                field: 'before',
                                title: '变更前',
                                width: '120px',
                            }, {
                                type: 'text',
                                field: 'after',
                                title: '剩余剧场积分',
                                width: '120px',
                            }, {
                                type: 'htmls',
                                field: 'oper.type',
                                title: '操作人类型',
                                width: '120px',
                                formatter: formatterHtml.operType,
                            }, {
                                type: 'htmls',
                                field: 'oper',
                                title: '操作人',
                                width: '200px',
                                formatter: formatterHtml.operUser,
                            }, {
                                type: 'text',
                                field: 'memo',
                                title: '备注',
                                width: '400px',
                                align: 'left',
                            }],
                            'vip_order_log': [{
                                type: 'time',
                                field: 'createtime',
                                title: '下单时间',
                                width: '160px',
                                formatter: formatterHtml.time,
                            }, {
                                type: 'vip_order',
                                field: 'order_sn',
                                title: '订单号',
                                width: '220px',
                            }, {
                                type: 'image',
                                field: 'platform',
                                title: '订单来源',
                                width: '100px',
                                formatter: formatterHtml.image,
                            }, {
                                type: 'price',
                                field: 'total_fee',
                                title: '金额小计',
                                width: '140px',
                            }, {
                                type: 'price',
                                field: 'pay_fee',
                                title: '实付金额',
                                width: '140px',
                            }, {
                                type: 'text',
                                field: 'status_text',
                                title: '订单状态',
                                width: '100px',
                            }],
                            'reseller_order_log': [{
                                type: 'time',
                                field: 'createtime',
                                title: '下单时间',
                                width: '160px',
                                formatter: formatterHtml.time,
                            }, {
                                type: 'reseller_order',
                                field: 'order_sn',
                                title: '订单号',
                                width: '220px',
                            }, {
                                type: 'image',
                                field: 'platform',
                                title: '订单来源',
                                width: '100px',
                                formatter: formatterHtml.image,
                            }, {
                                type: 'price',
                                field: 'total_fee',
                                title: '金额小计',
                                width: '140px',
                            }, {
                                type: 'price',
                                field: 'pay_fee',
                                title: '实付金额',
                                width: '140px',
                            }, {
                                type: 'text',
                                field: 'status_text',
                                title: '订单状态',
                                width: '100px',
                            }],
                            'share_log': [{
                                type: 'time',
                                field: 'createtime',
                                title: '分享时间',
                                width: '160px',
                                formatter: formatterHtml.time,
                            }, {
                                type: 'shareUser',
                                field: 'person',
                                title: '被分享用户',
                                width: '160px',
                                formatter: formatterHtml.shareUser,
                            }, {
                                type: 'image',
                                field: 'platform',
                                title: '平台',
                                width: '120px',
                                formatter: formatterHtml.image,
                            }],
                        },

                        page: 1,
                        limit: 10,
                        totalPage: 0,
                        currentPage: 1,
                        // 设置分销商
                        dialogListReseller: [],
                        // 设置vip
                        dialogListVip: [],
                        // 更换推荐人
                        dialogList: [],
                        agentDialogVisible: false,
                        vipDialogVisible: false,
                        resellerDialogVisible: false,
                        poffset: 0,
                        plimit: 5,
                        ptotalPage: 0,
                        pcurrentPage: 1,
                        parentFilterForm: {
                            status: "normal",
                            form_1_key: "id",
                            form_1_value: ""
                        },
                        parentFilterFormInit: {
                            status: "normal",
                            form_1_key: "id",
                            form_1_value: ""
                        },
                        parentFilterOp: {
                            status: "=",

                            id: "=",
                            nickname: "like",
                            mobile: "like",
                        },
                        selectParentAgentId: null,
                        noRecommendationChecked: false,
                        vip_id: '',
                        reseller_id: '',
                    }
                },
                created() {
                    this.data = JSON.parse(JSON.stringify(Config.row));
                    this.groupList = Config.groupList
                    this.getListData(this.activeStatus)
                },
                methods: {
                    getprofile() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/user/profile?id=' + Config.row.id,
                            loading: true,
                            type: 'GET',
                            data: {},
                        }, function (ret, res) {
                            Config.row = res.data;
                            that.data = JSON.parse(JSON.stringify(Config.row));
                            return false;
                        })
                    },
                    //列表
                    radioChange(val) {
                        this.logList = [];
                        this.activeStatus = val;
                        this.page = 1;
                        this.limit = 10;
                        this.currentPage = 1;
                        this.getListData(val)
                    },
                    getListData(val) {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/user/' + val + '?user_id=' + Config.row.id,
                            loading: true,
                            type: 'GET',
                            data: {
                                page: that.page,
                                limit: that.limit,
                            },
                        }, function (ret, res) {
                            that.logList = res.data.data;
                            that.totalPage = res.data.total;
                            return false;
                        })
                    },
                    operation(type, id) {
                        let that = this;
                        switch (type) {
                            case 'avatar':
                                parent.Fast.api.open("general/attachment/select?multiple=false", "选择头像", {
                                    callback: function (data) {
                                        that.data.avatar = data.url;
                                    }
                                });
                                break;
                            case 'money':
                                Fast.api.open('drama/user/money_recharge?id=' + Config.row.id, '余额充值', {
                                    callback(data) {
                                        that.getprofile();
                                        that.getListData(that.activeStatus);
                                    }
                                })
                                break;
                            case 'score':
                                Fast.api.open('drama/user/score_recharge?id=' + Config.row.id, '积分充值', {
                                    callback(data) {
                                        that.getprofile();
                                        that.getListData(that.activeStatus);
                                    }
                                })
                                break;
                            case 'usable':
                                Fast.api.open('drama/user/usable_recharge?id=' + Config.row.id, '剧场积分充值', {
                                    callback(data) {
                                        that.getprofile();
                                        that.getListData(that.activeStatus);
                                    }
                                })
                                break;
                            case 'reset':
                                that.data = JSON.parse(JSON.stringify(Config.row))
                                break;
                            case 'save':
                                subData = JSON.parse(JSON.stringify(that.data))
                                if (that.upPassword) {
                                    subData.password = that.upPassword
                                }
                                Fast.api.ajax({
                                    url: 'drama/user/update',
                                    loading: true,
                                    data: {
                                        data: JSON.stringify(subData)
                                    }
                                }, function (ret, res) {
                                    Config.row = res.data
                                    that.upPassword = ''
                                })
                                break;
                            case 'platformname':
                                let type = ''
                                switch (id) {
                                    case 'H5':
                                        type = 'H5'
                                        break;
                                    case 'wxOfficialAccount':
                                        type = '微信公众号'
                                        break;
                                    case 'wxMiniProgram':
                                        type = '微信小程序'
                                        break;
                                    case 'App':
                                        type = 'App'
                                        break;
                                }
                                return type
                                break;
                            case 'vipOrder':
                                Fast.api.open('drama/vip_order/detail?id=' + id, '查看订单')
                                break;
                            case 'resellerOrder':
                                Fast.api.open('drama/reseller/order_detail?id=' + id, '查看订单')
                                break;
                            case 'shareUser':
                                Fast.api.open('drama/user/profile?id=' + id, '查看')
                                break;
                        }
                    },
                    openAgentProfile(agent_id) {
                        let that = this;
                        Fast.api.open(`drama/user/profile?id=${agent_id}`, '详情', {
                            callback(data) {
                                that.getListData()
                            }
                        })
                    },

                    openDialogVip() {
                        this.getVipIndex();
                    },
                    closeDialogVip(opttype) {
                        if (opttype == true) {
                            this.reqUserChangeVip()
                        } else {
                            this.vipDialogVisible = false
                            this.vip_id = ''
                        }
                    },
                    getVipIndex() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/vip/select',
                            loading: false,
                            type: 'GET',
                        }, function (ret, res) {
                            that.dialogListVip = res.data.rows;
                            that.vipDialogVisible = true;
                            return false;
                        })
                    },
                    reqUserChangeVip() {
                        let that = this;
                        if (!that.vip_id) {
                            return false
                        }
                        Fast.api.ajax({
                            url: 'drama/user/changeVip?id=' + Config.row.id,
                            loading: false,
                            type: 'POST',
                            data: {
                                value: that.vip_id
                            },
                        }, function (ret, res) {
                            that.vip_id = '';
                            that.vipDialogVisible = false
                            that.getprofile();
                        }, function (ret, res) {
                            that.vip_id = '';
                            that.vipDialogVisible = false
                        })
                    },

                    openDialogReseller() {
                        this.getResellerIndex();
                    },
                    closeDialogReseller(opttype) {
                        if (opttype == true) {
                            this.reqUserChangeReseller()
                        } else {
                            this.resellerDialogVisible = false
                            this.reseller_id = ''
                        }
                    },
                    getResellerIndex() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/reseller/select',
                            loading: false,
                            type: 'GET',
                        }, function (ret, res) {
                            that.dialogListReseller = res.data.rows;
                            that.resellerDialogVisible = true;
                            return false;
                        })
                    },
                    reqUserChangeReseller() {
                        let that = this;
                        if (!that.reseller_id) {
                            return false
                        }
                        Fast.api.ajax({
                            url: 'drama/user/changeReseller?id=' + Config.row.id,
                            loading: false,
                            type: 'POST',
                            data: {
                                value: that.reseller_id
                            },
                        }, function (ret, res) {
                            that.reseller_id = '';
                            that.resellerDialogVisible = false
                            that.getprofile();
                        }, function (ret, res) {
                            that.reseller_id = '';
                            that.resellerDialogVisible = false
                        })
                    },

                    // 更换
                    openDialog() {
                        this.getAgentIndex();
                    },
                    closeDialog(opttype) {
                        if (opttype == true) {
                            this.reqUserChangeParentUser()
                        } else {
                            this.agentDialogVisible = false
                            this.noRecommendationChecked = false
                        }
                    },
                    reqUserChangeParentUser() {
                        let that = this;
                        if (!that.selectParentAgentId && that.selectParentAgentId != 0) {
                            return false
                        }
                        Fast.api.ajax({
                            url: 'drama/user/changeParentUser?id=' + Config.row.id,
                            loading: false,
                            type: 'POST',
                            data: {
                                value: that.selectParentAgentId
                            },
                        }, function (ret, res) {
                            that.selectParentAgentId = null;
                            that.agentDialogVisible = false
                            that.parentFilterForm.form_1_value = "";
                            that.parentFilterForm.form_1_key = "id";
                            that.noRecommendationChecked = false
                            that.getprofile();
                        }, function (ret, res) {
                            that.selectParentAgentId = null;
                            that.agentDialogVisible = false
                            that.parentFilterForm.form_1_value = "";
                            that.parentFilterForm.form_1_key = "id";
                            that.noRecommendationChecked = false
                        })
                    },
                    // init推荐人
                    initAgentData(id) {
                        if (id === true) {
                            this.selectParentAgentId = 0;
                        } else if (id === false) {
                            this.selectParentAgentId = null;
                        } else {
                            this.selectParentAgentId = id;
                            this.noRecommendationChecked = false;
                        }
                    },
                    // 推荐人列表
                    getAgentIndex() {
                        let that = this;
                        let filter = {}
                        let op = {}
                        for (key in that.parentFilterForm) {
                            if (key == 'form_1_value') {
                                if (that.parentFilterForm[key] != '') {
                                    filter[that.parentFilterForm.form_1_key] = that.parentFilterForm[key];
                                }
                            } else if (key == 'status') {
                                if (that.parentFilterForm[key] != '' && that.parentFilterForm[key] != 'all') {
                                    filter[key] = that.parentFilterForm[key];
                                }
                            }
                        }
                        for (key in filter) {
                            op[key] = that.parentFilterOp[key]
                        }
                        Fast.api.ajax({
                            url: 'drama/user/select',
                            loading: false,
                            type: 'GET',
                            data: {
                                offset: that.poffset,
                                limit: that.plimit,
                                filter: JSON.stringify(filter),
                                op: JSON.stringify(op)
                            },
                        }, function (ret, res) {
                            that.dialogList = res.data.rows;
                            that.ptotalPage = res.data.total;
                            that.agentDialogVisible = true;
                            return false;
                        })
                    },
                    parentDebounceFilter: debounce(function () {
                        this.getAgentIndex()
                    }, 1000),
                    phandleCurrentChange(val) {
                        this.pcurrentPage = val;
                        this.poffset = (val - 1) * this.plimit;
                        this.getAgentIndex()
                    },
                    handleSizeChange(val) {
                        this.page = 0
                        this.limit = val;
                        this.currentPage = 1;
                        this.getListData(this.activeStatus)
                    },
                    handleCurrentChange(val) {
                        this.currentPage = val;
                        this.page = val;
                        this.getListData(this.activeStatus)
                    },
                    tableRowClassName({
                        rowIndex
                    }) {
                        if (rowIndex % 2 == 1) {
                            return 'bg-color';
                        }
                        return '';
                    },
                },
            })
        },
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: '',
                }
            });

            var idArr = [];
            var selectArr = [];
            var table = $("#table");

            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function (e, row) {
                if (e.type == 'check' || e.type == 'uncheck') {
                    row = [row];
                } else {
                    idArr = [];
                    selectArr = []
                }
                $.each(row, function (i, j) {
                    if (e.type.indexOf("uncheck") > -1) {
                        var index = idArr.indexOf(j.id);
                        var indexall = idArr.indexOf(j);
                        if (index > -1) {
                            idArr.splice(index, 1);
                        }
                        if (indexall > -1) {
                            selectArr.splice(index, 1);
                        }
                    } else {
                        idArr.indexOf(j.id) == -1 && idArr.push(j.id);
                        selectArr.indexOf(j) == -1 && selectArr.push(j);
                    }
                });
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                showToggle: false,
                showExport: false,
                columns: [
                    [{
                        field: 'state',
                        checkbox: true,
                    },
                    {
                        field: 'title',
                        title: __('Title'),
                        align: 'left'
                    },
                    {
                        field: 'image',
                        title: __('Image'),
                        operate: false,
                        events: Table.api.events.image,
                        formatter: Table.api.formatter.image
                    },
                    {
                        field: 'status_text',
                        title: __('Status'),
                        // formatter: Table.api.formatter.status,
                    },
                    {
                        field: 'createtime',
                        title: __('Createtime'),
                        formatter: Table.api.formatter.datetime,
                        operate: 'RANGE',
                        addclass: 'datetimerange',
                        sortable: true
                    },
                    {
                        field: 'operate',
                        title: __('Operate'),
                        events: {
                            'click .btn-chooseone': function (e, value, row, index) {
                                var multiple = Backend.api.query('multiple');
                                multiple = multiple == 'true' ? true : false;
                                row.ids = row.id.toString()
                                Fast.api.close({
                                    data: row,
                                    multiple: multiple
                                });
                            },
                        },
                        formatter: function () {
                            return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                        }
                    }
                    ]
                ]
            });

            // 选中多个
            $(document).on("click", ".btn-choose-multi", function () {
                if (Backend.api.query('type') == 'activity') {
                    var multiple = Backend.api.query('multiple');
                    multiple = multiple == 'true' ? true : false;
                    Fast.api.close({
                        data: selectArr,
                        multiple: multiple
                    });
                } else {
                    let row = {}
                    var multiple = Backend.api.query('multiple');
                    multiple = multiple == 'true' ? true : false;
                    row.ids = idArr.join(",")
                    Fast.api.close({
                        data: row,
                        multiple: multiple
                    });
                }

            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // var options = table.bootstrapTable(tableOptions);
                var typeStr = $(this).attr("href").replace('#', '');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    // params.filter = JSON.stringify({type: typeStr});
                    params.type = typeStr;
                    params.status = typeStr.replace('t-', '');
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });
            require(['upload'], function (Upload) {
                Upload.api.plupload($("#toolbar .plupload"), function () {
                    $(".btn-refresh").trigger("click");
                });
            });

        },
        money_recharge: function () {
            Controller.rechangeInit('money')
        },
        score_recharge: function () {
            Controller.rechangeInit('score')
        },
        usable_recharge: function () {
            Controller.rechangeInit('usable')
        },
        rechangeInit: function (type) {
            function urlParmas(par) {
                let value = ""
                window.location.search.replace("?", '').split("&").forEach(i => {
                    if (i.split('=')[0] == par) {
                        value = JSON.parse(decodeURI(i.split('=')[1]))
                    }
                })
                return value
            }
            var recharge = new Vue({
                el: "#recharge",
                data() {
                    return {
                        rechargeType: type,
                        rechargeForm: {
                            user_id: urlParmas('id'),
                            money: '',
                            score: '',
                            usable: '',
                            remarks: '',
                        },
                        rechargeFormInit: {
                            user_id: urlParmas('id'),
                            money: '',
                            usable: '',
                            remarks: '',
                        },
                        rules: {
                            money: [{
                                required: true,
                                message: '请输入充值余额',
                                trigger: 'blur'
                            },],
                            score: [{
                                required: true,
                                message: '请输入充值积分',
                                trigger: 'blur'
                            },],
                            usable: [{
                                required: true,
                                message: '请输入充值剧场积分',
                                trigger: 'blur'
                            },],
                        }
                    }
                },
                mounted() { },
                methods: {
                    submitForm(type, check) {
                        let that = this;
                        if (type == 'yes') {
                            that.$refs[check].validate((valid) => {
                                if (valid) {
                                    let subData = JSON.parse(JSON.stringify(that.rechargeForm));
                                    if (that.rechargeType == 'score') {
                                        delete subData.money
                                        delete subData.usable
                                        Fast.api.ajax({
                                            url: 'drama/user/score_recharge',
                                            loading: true,
                                            data: subData
                                        }, function (ret, res) {
                                            that.rechargeType = null;
                                            that.rechargeForm = JSON.parse(JSON.stringify(that.rechargeFormInit))
                                            Fast.api.close();
                                        })
                                    } else if (that.rechargeType == 'usable') {
                                        delete subData.money
                                        delete subData.score
                                        Fast.api.ajax({
                                            url: 'drama/user/usable_recharge',
                                            loading: true,
                                            data: subData
                                        }, function (ret, res) {
                                            that.rechargeType = null;
                                            that.rechargeForm = JSON.parse(JSON.stringify(that.rechargeFormInit))
                                            Fast.api.close();
                                        })
                                    } else if (that.rechargeType == 'money') {
                                        delete subData.score
                                        delete subData.usable
                                        Fast.api.ajax({
                                            url: 'drama/user/money_recharge',
                                            loading: true,
                                            type: "POST",
                                            data: subData
                                        }, function (ret, res) {
                                            that.rechargeType = null;
                                            that.rechargeForm = JSON.parse(JSON.stringify(that.rechargeFormInit));
                                            Fast.api.close();
                                        })
                                    }
                                } else {
                                    return false;
                                }
                            });
                        } else {
                            that.rechargeForm = JSON.parse(JSON.stringify(that.rechargeFormInit))
                            that.rechargeType = null;
                        }
                    },
                }
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },

    };
    return Controller;
});