define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

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
            var indexPage = new Vue({
                el: "#indexPage",
                data() {
                    return {
                        data: [],
                        multipleSelection: [],
                        screenType: false,
                        screenList: {},
                        offset: 0,
                        limit: 10,
                        totalPage: 0,
                        currentPage: 1,
                        // form搜索
                        searchForm: {
                            type: "all",
                            item_id: "",
                            createtime: [],
                            // createtime: [moment().startOf('day').format('YYYY-MM-DD HH:mm:ss'), moment().endOf('day').format('YYYY-MM-DD HH:mm:ss')],
                            status: '',
                            remark: "",
                            name: "",
                        },
                        searchFormInit: {
                            type: "all",
                            item_id: "",
                            createtime: [],
                            status: '',
                            remark: "",
                            name: "",
                        },
                        searchOp: {
                            type: "=",
                            createtime: "range",
                            name: "like",
                            remark: "like",
                            item_id: "=",
                            status: "=",
                        },
                    }
                },
                created() {
                    this.getData();
                    this.reqScreenList()
                },
                methods: {
                    getData(offset, limit) {
                        let that = this;
                        if (offset == 0 && limit == 10) {
                            that.offset = offset;
                            that.limit = limit;
                        }
                        let filter = {}
                        let op = {}
                        for (key in that.searchForm) {
                            if (key == 'name' || key == 'remark') {
                                if (that.searchForm[key] && that.searchForm[key] != '') {
                                    filter[key] = that.searchForm[key];
                                }
                            } else if (key == 'createtime') {
                                if (that.searchForm[key]) {
                                    if (that.searchForm[key].length > 0) {
                                        filter[key] = that.searchForm[key].join(' - ');
                                    }
                                }
                            } else if (key == 'status') {
                                if (that.searchForm[key] && that.searchForm[key] != '') {
                                    filter[key] = that.searchForm[key];
                                }
                            } else if (key == 'type') {
                                if (that.searchForm[key] && that.searchForm[key] != '' && that.searchForm[key] != 'all') {
                                    filter[key] = that.searchForm[key];
                                }
                            } else if (key == 'item_id') {
                                console.log(that.searchForm[key]);
                                if (that.searchForm[key] && that.searchForm[key] != '') {
                                    filter[key] = that.searchForm[key];
                                }
                            }
                        }
                        for (key in filter) {
                            op[key] = that.searchOp[key]
                        }

                        Fast.api.ajax({
                            url: 'drama/cryptocard/index',
                            loading: true,
                            type: 'GET',
                            data: {
                                filter: JSON.stringify(filter),
                                op: JSON.stringify(op),
                                offset: that.offset,
                                limit: that.limit,
                            },
                        }, function (ret, res) {
                            that.data = res.data.rows;
                            that.totalPage = res.data.total;
                            return false;
                        })
                    },
                    operation(type, id) {
                        let that = this;
                        switch (type) {
                            case 'create':
                                Fast.api.open('drama/cryptocard/add', '新增', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                            case 'del':
                                let ids;
                                if (id) {
                                    ids = id;
                                } else {
                                    let idArr = []
                                    if (that.multipleSelection.length > 0) {
                                        that.multipleSelection.forEach(i => {
                                            idArr.push(i.id)
                                        })
                                        ids = idArr.join(',')
                                    }
                                }
                                if (ids) {
                                    that.$confirm('此操作将删除卡密, 是否继续?', '提示', {
                                        confirmButtonText: '确定',
                                        cancelButtonText: '取消',
                                        type: 'warning'
                                    }).then(() => {
                                        Fast.api.ajax({
                                            url: 'drama/cryptocard/del/ids/' + ids,
                                            loading: true,
                                            type: 'POST'
                                        }, function (ret, res) {
                                            that.getData();
                                            return false;
                                        })
                                    }).catch(() => {
                                        that.$message({
                                            type: 'info',
                                            message: '已取消删除'
                                        });
                                    });
                                }
                                break;
                            case 'recyclebin':
                                Fast.api.open('drama/cryptocard/recyclebin', '查看回收站', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                        }
                    },
                    goExport() {
                        var that = this;
                        let filter = {}
                        let op = {}
                        for (key in that.searchForm) {
                            if (key == 'name' || key == 'remark') {
                                if (that.searchForm[key] && that.searchForm[key] != '') {
                                    filter[key] = that.searchForm[key];
                                }
                            } else if (key == 'createtime') {
                                if (that.searchForm[key]) {
                                    if (that.searchForm[key].length > 0) {
                                        filter[key] = that.searchForm[key].join(' - ');
                                    }
                                }
                            } else if (key == 'status') {
                                if (that.searchForm[key] && that.searchForm[key] != '') {
                                    filter[key] = that.searchForm[key];
                                }
                            } else if (key == 'type') {
                                if (that.searchForm[key] && that.searchForm[key] != '' && that.searchForm[key] != 'all') {
                                    filter[key] = that.searchForm[key];
                                }
                            } else if (key == 'item_id') {
                                console.log(that.searchForm[key]);
                                if (that.searchForm[key] && that.searchForm[key] != '') {
                                    filter[key] = that.searchForm[key];
                                }
                            }
                        }
                        for (key in filter) {
                            op[key] = that.searchOp[key]
                        }
                        window.location.href = Config.moduleurl + "/drama/cryptocard/export" + "?filter=" + JSON.stringify(filter) + "&op=" + JSON.stringify(op);
                    },
                    //筛选
                    changeSwitch() {
                        this.screenType = !this.screenType;
                    },
                    screenEmpty() {
                        this.searchForm = JSON.parse(JSON.stringify(this.searchFormInit));
                        this.getData();
                    },
                    reqScreenList() {
                        var that = this;

                        Fast.api.ajax({
                            url: 'drama/cryptocard/selectSearch?type='+that.searchForm.type,
                            loading: true,
                            type: "GET",
                        }, function (ret, res) {
                            that.screenList = res.data;
                            return false;
                        })
                    },

                    handleSelectionChange(val) {
                        this.multipleSelection = val;
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
                        if (columnIndex == 1 || columnIndex == 2 || columnIndex == 9) {
                            return 'cell-left';
                        }
                        return '';
                    },
                    debounceFilter: debounce(function () {
                        this.getData()
                    }, 1000),
                },
            })
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'drama/cryptocard/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [{
                        checkbox: true
                    },
                        {
                            field: 'id',
                            title: __('Id')
                        },
                        {
                            field: 'name',
                            title: __('Name'),
                            align: 'left'
                        },
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                name: 'Restore',
                                text: __('Restore'),
                                classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                icon: 'fa fa-rotate-left',
                                url: 'drama/cryptocard/restore',
                                refresh: true
                            },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'drama/cryptocard/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.detailInit('add');
        },
        detailInit: function (type) {
            Vue.directive('enterNumber', {
                inserted: function (el) {
                    let changeValue = (el, type) => {
                        const e = document.createEvent('HTMLEvents')
                        e.initEvent(type, true, true)
                        el.dispatchEvent(e)
                    }
                    el.addEventListener("keyup", function (e) {
                        let input = e.target;
                        let reg = new RegExp('^((?:(?:[1-9]{1}\\d*)|(?:[0]{1}))(?:\\.(?:\\d){0,2})?)(?:\\d*)?$');
                        let matchRes = input.value.match(reg);
                        if (matchRes === null) {
                            input.value = "";
                        } else {
                            if (matchRes[1] !== matchRes[0]) {
                                input.value = matchRes[1];
                            }
                        }
                        changeValue(input, 'input')
                    });
                }
            });
            Vue.directive('positiveInteger', {
                inserted: function (el) {
                    el.addEventListener("keypress", function (e) {
                        e = e || window.event;
                        let charcode = typeof e.charCode == 'number' ? e.charCode : e.keyCode;
                        let re = /\d/;
                        if (!re.test(String.fromCharCode(charcode)) && charcode > 9 && !e.ctrlKey) {
                            if (e.preventDefault) {
                                e.preventDefault();
                            } else {
                                e.returnValue = false;
                            }
                        }
                    });
                }
            });
            var pageDetail = new Vue({
                el: "#pageDetail",
                data() {
                    return {
                        optType: type,
                        detailData: {},
                        detailDataInit: {
                            item_id: "",
                            name: "",
                            stock: 1,
                            pwd_type: 'alnum',
                            pwd_len: 4,
                            usetime: "",
                            type: 'usable',
                            remark: '',
                        },
                        rules: {
                            name: [{
                                required: true,
                                message: '请输入卡密名称',
                                trigger: 'blur'
                            }],
                            type: [{
                                required: true,
                                message: '请选择套餐类型',
                                trigger: 'blur'
                            }],
                            usetime: [{
                                required: true,
                                message: '请选择使用时间',
                                trigger: 'change'
                            }],
                            item_id: [{
                                required: true,
                                message: '请选择套餐',
                                trigger: 'change'
                            }],
                            stock: [{
                                required: true,
                                message: '请输入发行总量',
                                trigger: 'blur'
                            }],
                            pwd_type: [{
                                required: true,
                                message: '请选择卡密类型',
                                trigger: 'blur'
                            }],
                            pwd_len: [{
                                required: true,
                                message: '请输入卡密长度',
                                trigger: 'blur'
                            }],
                        },
                        options: []
                    }
                },
                created() {
                    this.detailData = JSON.parse(JSON.stringify(this.detailDataInit));
                    this.operation();
                },
                methods: {
                    operation() {
                        let that = this;
                        that.options = [];
                        that.detailData.item_id = '';
                        let url = '';
                        if(that.detailData.type == 'vip'){
                            url = 'drama/vip/select'
                        }else if(that.detailData.type == 'reseller'){
                            url = 'drama/reseller/select'
                        }else if(that.detailData.type == 'usable'){
                            url = 'drama/usable/select'
                        }
                        if(that.detailData.type && url){
                            Fast.api.ajax({
                                url: url,
                                loading: true,
                                type: "POST",
                            }, function (ret, res) {
                                that.options = res.data.rows;
                                return false;
                            })
                        }
                    },
                    submit(type, check) {
                        let that = this;
                        if (type == 'yes') {
                            this.$refs[check].validate((valid) => {
                                if (valid) {
                                    let subData = JSON.parse(JSON.stringify(that.detailData));
                                    subData.usetime = subData.usetime.join(' - ');
                                    Fast.api.ajax({
                                        url: 'drama/cryptocard/add',
                                        loading: true,
                                        type: "POST",
                                        data: {
                                            data: JSON.stringify(subData)
                                        }
                                    }, function (ret, res) {
                                        Fast.api.close({
                                            data: true
                                        })
                                    })
                                } else {
                                    return false;
                                }
                            });
                        } else {
                            Fast.api.close()
                            that.storeForm = JSON.parse(JSON.stringify(that.storeFormInit[that.store_type]));
                        }
                    },
                },
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});