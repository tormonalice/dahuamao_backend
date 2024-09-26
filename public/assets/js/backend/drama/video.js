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
            var videoIndex = new Vue({
                el: "#videoIndex",
                data() {
                    return {
                        videoData: [],
                        multipleSelection: [],
                        chooseType: 0,
                        flagsList: [],
                        flagsType: 'all',
                        categoryList: [],
                        categoryType: 'all',
                        yearList: [],
                        yearType: 'all',
                        areaList: [],
                        areaType: 'all',
                        activeStatus: 'all',
                        searchKey: '',
                        priceFrist: '',
                        priceLast: '',
                        vpriceFrist: '',
                        vpriceLast: '',
                        sort: 'id',
                        order: 'desc',
                        offset: 0,
                        limit: 10,
                        totalPage: 0,
                        currentPage: 1,
                        rowDel: false,
                        allDel: false,

                        // 价格批量修改
                        priceDialogVisible: false,
                        vpriceFreeChecked: false,
                        video_price: '',
                        episode_top: '',
                        episode_price: '',
                        episode_price_last: '',
                        video_vprice: '',
                        episode_top_vip: '',
                        episode_vprice: '',
                        episode_vprice_last: '',

                        upStatus: true,
                        allAjax: true,
                        tableAjax: false
                    }
                },
                created() {
                    this.getData();
                },
                methods: {
                    getData() {
                        let that = this;
                        if (!that.allAjax) {
                            that.tableAjax = true;
                        }
                        let dataAc = {
                            search: that.searchKey,
                            status: that.activeStatus,
                            flags_type: that.flagsType,
                            category_type: that.categoryType,
                            area_type: that.areaType,
                            year_type: that.yearType,
                            min_price: that.priceFrist,
                            max_price: that.priceLast,
                            min_vprice: that.vpriceFrist,
                            max_vprice: that.vpriceLast,
                            offset: that.offset,
                            limit: that.limit,
                            sort: that.sort,
                            order: that.order,
                        };
                        that.flagsList = Config.flagsList;
                        that.categoryList = Config.categoryList;
                        that.yearList = Config.yearList;
                        that.areaList = Config.areaList;
                        Fast.api.ajax({
                            url: 'drama/video/index',
                            loading: false,
                            type: 'GET',
                            data: dataAc
                        }, function (ret, res) {
                            that.videoData = res.data.rows;
                            that.videoData.forEach(i => {
                                i.showFlag = false;
                                i.rowDel = false;
                            });
                            that.totalPage = res.data.total;
                            that.allAjax = false;
                            that.tableAjax = false;
                            return false;
                        }, function (ret, res) {
                            that.allAjax = false;
                            that.tableAjax = false;
                        });
                    },
                    openDialog() {
                        this.priceDialogVisible = true;
                    },
                    closeDialog(opttype) {
                        if (opttype == true) {
                            this.reqVideoChangePrice();
                        } else {
                            this.priceDialogVisible = false;
                            this.vpriceFreeChecked = false;
                        }
                    },
                    initVpriceData(id) {
                        this.video_vprice = '';
                        this.episode_top_vip = '';
                        this.episode_vprice = '';
                        this.episode_vprice_last = '';
                    },
                    reqVideoChangePrice() {
                        let that = this;
                        if (that.video_price == '' &&
                            that.episode_top == '' &&
                            that.episode_price == '' &&
                            that.episode_price_last == '' &&
                            that.video_vprice == '' &&
                            that.episode_top_vip == '' &&
                            that.episode_vprice == '' &&
                            that.episode_vprice_last == '' &&
                            that.vpriceFreeChecked == false) {
                            return false;
                        }
                        Fast.api.ajax({
                            url: 'drama/video/changePrice',
                            loading: false,
                            type: 'POST',
                            data: {
                                vpriceFreeChecked: that.vpriceFreeChecked,
                                video_price: that.video_price,
                                episode_top: that.episode_top,
                                episode_price: that.episode_price,
                                episode_price_last: that.episode_price_last,
                                video_vprice: that.video_vprice,
                                episode_top_vip: that.episode_top_vip,
                                episode_vprice: that.episode_vprice,
                                episode_vprice_last: that.episode_vprice_last
                            },
                        }, function (ret, res) {
                            that.priceDialogVisible = false;
                            that.vpriceFreeChecked = false;
                            that.video_price = '';
                            that.episode_top = '';
                            that.episode_price = '';
                            that.episode_price_last = '';
                            that.video_vprice = '';
                            that.episode_top_vip = '';
                            that.episode_vprice = '';
                            that.episode_vprice_last = '';
                            that.getData();
                        }, function (ret, res) {
                            that.priceDialogVisible = false;
                            that.vpriceFreeChecked = false;
                            that.video_price = '';
                            that.episode_top = '';
                            that.episode_price = '';
                            that.episode_price_last = '';
                            that.video_vprice = '';
                            that.episode_top_vip = '';
                            that.episode_vprice = '';
                            that.episode_vprice_last = '';
                        })
                    },

                    copyMsg(id) {
                        let that = this;
                        if (id) {
                            that.$confirm('此操作将复制短剧的页面链接, 是否继续?', '提示', {
                                confirmButtonText: '确定',
                                cancelButtonText: '取消',
                                type: 'warning'
                            }).then(() => {
                                Fast.api.ajax({
                                    url: 'drama/video/episodes/ids/' + id,
                                    loading: true,
                                    type: 'POST',
                                }, function (ret, res) {
                                    console.log(ret);
                                    navigator.clipboard.writeText(ret.url).then(function() {
                                        that.$message({
                                            message: '复制成功',
                                            type: 'success'
                                        });
                                    }, function() {
                                        that.$message.error('复制失败');
                                    });
                                    return false;
                                });
                            }).catch(() => {
                                that.$message({
                                    type: 'info',
                                    message: '复制失败'
                                });
                            });
                        }
                    },

                    videoOpt(type, id) {
                        let that = this;
                        switch (type) {
                            case 'create':
                                Fast.api.open('drama/video/add', '新增短剧', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                            case 'edit':
                                Fast.api.open('drama/video/edit/ids/' + id + "?id=" + id + "&type=edit", '编辑短剧', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                            case 'down':
                                let idArr = []
                                if (that.multipleSelection.length > 0) {
                                    that.multipleSelection.forEach(i => {
                                        idArr.push(i.id)
                                    })
                                    let idss = idArr.join(',')
                                    that.editStatus(idss, 'down')
                                }
                                break;
                            case 'up':
                                let idArrup = []
                                if (that.multipleSelection.length > 0) {
                                    that.multipleSelection.forEach(i => {
                                        idArrup.push(i.id)
                                    })
                                    let idup = idArrup.join(',')
                                    that.editStatus(idup, 'up')
                                }
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
                                    that.$confirm('此操作将删除短剧, 是否继续?', '提示', {
                                        confirmButtonText: '确定',
                                        cancelButtonText: '取消',
                                        type: 'warning'
                                    }).then(() => {
                                        Fast.api.ajax({
                                            url: 'drama/video/del/ids/' + ids,
                                            loading: true,
                                            type: 'POST',
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
                            case 'copy':
                                Fast.api.open('drama/video/edit/ids/' + id + "?id=" + id + "&type=copy", '短剧详情', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                            case 'filter':
                                that.offset = 0;
                                that.limit = 10;
                                that.currentPage = 1;
                                that.getData();
                                break;
                            case 'clear':
                                that.flagsType = 'all';
                                that.categoryType = 'all';
                                that.yearType = 'all';
                                that.areaType = 'all';
                                that.priceFrist = "";
                                that.priceLast = "";
                                that.vpriceFrist = "";
                                that.vpriceLast = "";
                                break;
                            case 'recycle':
                                Fast.api.open('drama/video/recyclebin', '查看回收站')
                                break;
                            default:
                                Fast.api.open('drama/video/edit/ids/' + type.id + "?id=" + type.id + "&type=edit", '编辑短剧', {
                                    callback() {
                                        that.getData();
                                    }
                                })
                                break;
                        }
                    },
                    videoSync(){
                        this.$confirm('导入测试数据将清空分类和短剧，确认导入测试数据?', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            Fast.api.ajax({
                                url: 'drama/video/sync',
                                loading: true,
                                type: 'POST',
                            }, function (ret, res) {
                                Layer.alert('数据导入成功,请刷新页面查看！');
                                return false;
                            });
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消导入'
                            });
                        });
                    },
                    videoAdd(){
                        this.$confirm('导入前请备份数据库！数据较多，请耐心等待！', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            Fast.api.ajax({
                                url: 'drama/video/sync_add',
                                loading: true,
                                type: 'POST',
                            }, function (ret, res) {
                                Layer.alert('数据导入成功,请刷新页面查看！');
                                return false;
                            });
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消导入'
                            });
                        });
                    },
                    videoDownload(){
                        this.$confirm('下载批量导入短剧剧集模板?', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            window.open('video/download', '_blank');
                            return false;
                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消模板下载'
                            });
                        });
                    },
                    videoImport(){
                        parent.Fast.api.open("general/attachment/select?multiple=false", "选择文件", {
                            callback: function (data) {
                                Fast.api.ajax({
                                    url: 'drama/video/import',
                                    loading: true,
                                    type: 'POST',
                                    data: {'url': data.url},
                                }, function (ret, res) {
                                    Layer.alert('数据导入成功！');
                                    return false;
                                });
                            }
                        });
                        return false;
                    },
                    hideup() {
                        for (key in this.selectedRowId) {
                            this.selectedRowId[key] = false;
                        }
                    },
                    sortOrder(sort, order) {
                        this.sort = sort;
                        this.order = order;
                        this.getData();
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
                    editStatus(id, type) {
                        let that = this;
                        Fast.api.ajax({
                            url: `drama/video/setStatus/ids/${id}/status/${type}`,
                            loading: true,
                        }, function (ret, res) {
                            that.getData();
                            return false;
                        })
                    },
                    chooseOpt(type, val) {
                        if(type == 'category'){
                            this.categoryType = val;
                        }else if(type == 'year'){
                            this.yearType = val;
                        }else if(type == 'area'){
                            this.areaType = val;
                        }else if(type == 'flags'){
                            this.flagsType = val;
                        }
                    },
                    isShoose() {
                        this.chooseType == 0 ? 1 : 0;
                        if (this.chooseType == 0) {
                            this.flagsType = 'all';
                            this.categoryType = 'all';
                            this.yearType = 'all';
                            this.areaType = 'all';
                            this.priceFrist = "";
                            this.priceLast = "";
                            this.vpriceFrist = "";
                            this.vpriceLast = "";
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
                        if (columnIndex == 2) {
                            return 'cell-left';
                        }
                        return '';
                    },
                    debounceFilter: debounce(function () {
                        this.getData()
                    }, 1000),
                    tbXcx(){

                        let that = this;

                        this.$confirm('同步小程序媒资库中的剧目，同步完之后需要等待程序自动更新剧目中每集视频的信息', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {

                            let idArr = []
                            let ids = ''
                            if (that.multipleSelection.length > 0) {
                                that.multipleSelection.forEach(i => {
                                    idArr.push(i.id)
                                })
                                ids = idArr.join(',')
                            }else{
                                ids = ''
                            }

                            Fast.api.ajax({
                                url: 'drama/video/tbXcx',
                                loading: true,
                                type: 'POST',
                                data: {ids:ids},
                            }, function (ret, res) {
                                Layer.alert('剧目同步成功,请补充剧目信息并等待程序自动更新剧目中每集视频的信息！');
                                return false;
                            });

                        }).catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消执行'
                            });
                        });
                    },
                },
                watch: {
                    activeStatus(newVal, oldVal) {
                        if (newVal != oldVal) {
                            this.offset = 0;
                            this.limit = 10;
                            this.currentPage = 1;
                            this.getData();
                        }
                    },
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
                url: 'drama/video/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'drama/video/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'drama/video/destroy',
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
            Controller.initAddEdit(null, null, [], []);
        },
        edit: function () {
            let id, type
            window.location.search.replace("?", '').split('&').forEach(i => {
                if (i.split('=')[0] == 'id') {
                    id = i.split('=')[1]
                }
                if (i.split('=')[0] == 'type') {
                    type = i.split('=')[1]
                }
            })
            Controller.initAddEdit(id, type, Config.videoPerformer, Config.videoEpisodes);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
        initAddEdit: function (id, type, videoPerformer, videoEpisodes) {
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
            //vue Sku添加页 添加规格和价格数据
            var videoDetail = new Vue({
                el: "#videoDetail",
                data() {
                    return {
                        editId: id,
                        type: type,
                        stepActive: 1,
                        videoDetail: {},
                        videoDetailInit: {
                            title: '',
                            subtitle: '',
                            flags: '',
                            status: 'up',
                            weigh: '',
                            image: '',
                            category_ids: '',
                            area_id: '',
                            year_id: '',
                            price: '',
                            vprice: '',
                            episodes: '',
                            score: '',
                            tags: '',
                            description: '',
                            content: '',
                        },
                        timeData: {
                            images_arr: [],
                            flags_arr: [], //标志
                            tags_arr: [],
                        },
                        rules: {
                            title: [{
                                required: true,
                                message: '请输入短剧标题',
                                trigger: 'blur'
                            }],
                            subtitle: [{
                                required: true,
                                message: '请输入短剧副标题',
                                trigger: 'blur'
                            }],
                            status: [{
                                required: true,
                                message: '请选择短剧状态',
                                trigger: 'blur'
                            }],
                            image: [{
                                required: true,
                                message: '请上传短剧封面',
                                trigger: 'change'
                            }],
                            category_ids: [{
                                required: true,
                                message: '请选择短剧分类',
                                trigger: 'change'
                            }],
                            area_id: [{
                                required: true,
                                message: '请选择短剧地区',
                                trigger: 'change'
                            }],
                            year_id: [{
                                required: true,
                                message: '请选择短剧年份',
                                trigger: 'change'
                            }],
                            price: [{
                                required: true,
                                message: '请输入价格',
                                trigger: 'blur'
                            }],
                            vprice: [{
                                required: true,
                                message: '请输入VIP价格',
                                trigger: 'blur'
                            }],
                            episodes: [{
                                required: true,
                                message: '请输入短剧总集数',
                                trigger: 'blur'
                            }],
                            score: [{
                                required: true,
                                message: '请输入短剧评分',
                                trigger: 'blur'
                            }],
                            tags: [{
                                required: true,
                                message: '请输入短剧标签',
                                trigger: 'blur'
                            }],
                            description: [{
                                required: true,
                                message: '请输入短剧描述',
                                trigger: 'blur'
                            }],
                        },
                        mustDel: [],

                        //选项
                        flagsOptions: [],
                        areaOptions: [],
                        yearOptions: [],

                        upload: Config.moduleurl,
                        editor: null,

                        //演员
                        videoPerformer: [],
                        videoEpisodes:[],
                        allEditPopover: {
                            price: false,
                            vprice: false,
                            fake_likes: false,
                            fake_views: false,
                            fake_favorites: false,
                            fake_shares: false,
                        },
                        allEditDatas: "",
                        allEditPopoverTags: {
                            tag: false,
                        },
                        allEditTags: "",
                        allEditPopoverItems: [],
                        allEditItems: "",

                        //选择分类
                        categoryOptions: [],
                        popperVisible: false,
                        tempTabsId: "",
                        tempCategory: {
                            idsArr: {},
                            label: {}
                        }
                    }
                },
                mounted() {
                    this.flagsOptions = Config.flagsList;
                    this.areaOptions = Config.areaList;
                    this.yearOptions = Config.yearList;
                    if (this.editId) {
                        this.videoDetail = JSON.parse(JSON.stringify(this.videoDetailInit));
                        this.getCategoryOptions(true);
                    } else {
                        this.getCategoryOptions();
                        this.videoDetail = JSON.parse(JSON.stringify(this.videoDetailInit));
                        this.getInit([], []);
                        this.$nextTick(() => {
                            Controller.api.bindevent();
                        });
                    }
                },
                methods: {
                    getInit(videoPerformer, videoEpisodes) {
                        this.videoPerformer = videoPerformer;
                        this.videoEpisodes = videoEpisodes;
                        for(i=0;i<videoPerformer.length;++i){
                            this.allEditPopoverItems.push({tag:false});
                        }
                        for(i=0;i<videoEpisodes.length;++i){
                            this.videoEpisodes[i].suffix = this.videoEpisodes[i].video.substring(this.videoEpisodes[i].video.lastIndexOf('.') + 1);
                        }

                        setTimeout(() => {
                            // 延迟触发更新下面列表
                            this.isEditInit = true;
                        }, 200);
                    },
                    getEditData() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/video/detail/ids/' + that.editId,
                            loading: true,
                        }, function (ret, res) {
                            for (key in that.videoDetail) {
                                if (typeof res.data.detail[key] !== 'undefined') {
                                    that.videoDetail[key] = res.data.detail[key];
                                }
                            }
                            for (key in that.timeData) {
                                if (typeof res.data.detail[key] !== 'undefined') {
                                    that.timeData[key] = res.data.detail[key];
                                }

                            }
                            that.handleCategoryIds(res.data.detail.category_ids_arr);

                            that.getInit(res.data.videoPerformer, res.data.videoEpisodes);

                            Controller.api.bindevent();
                            $('#c-content').html(res.data.detail.content);
                            return false;
                        })
                    },
                    // 处理 category_ids 显示 组合label数据
                    handleCategoryIds(ids_arr) {
                        if (ids_arr.length > 0) {
                            this.tempTabsId = ids_arr[0][0] + "";
                            ids_arr.forEach((cate) => {
                                if (!this.tempCategory.idsArr[cate[0]]) {
                                    this.tempCategory.idsArr[cate[0]] = [];
                                }
                                this.tempCategory.idsArr[cate[0]].push(cate[cate.length - 1]);
                            });
                        } else {
                            if (category.select.length) {
                                this.tempTabsId = category.select[0].id + "";
                            }
                        }
                        this.changeCategoryIds();
                    },
                    openCategory(type) {
                        if (type == 0) {
                            this.popperVisible = false
                        } else if (type == 1) {
                            this.popperVisible = true
                        } else {
                            this.popperVisible = !this.popperVisible
                        }
                    },
                    handleCategoryIdsLabel(data, id) {
                        let that = this;
                        for (var i = 0; i < data.length; i++) {
                            if (data[i] && data[i].id == id) {
                                return [data[i].name];
                            }
                            if (data[i] && data[i].children && data[i].children.length > 0) {
                                var far = that.handleCategoryIdsLabel(data[i].children, id);
                                if (far) {
                                    return far.concat(data[i].name);
                                }
                            }
                        }
                    },
                    changeCategoryIds() {
                        this.$nextTick(() => {
                            this.tempCategory.idsArr = {};
                            this.tempCategory.label = {};
                            for (var key in this.$refs) {
                                if (key.includes('categoryRef')) {
                                    let keyArr = key.split("-");
                                    if (this.$refs[key].length > 0) {
                                        if (this.$refs[key][0].checkedNodePaths.length > 0) {
                                            this.$refs[key][0].checkedNodePaths.forEach((row) => {
                                                row.forEach(k => {
                                                    if (k.checked) {
                                                        if (!this.tempCategory.idsArr[keyArr[1]]) {
                                                            this.tempCategory.idsArr[keyArr[1]] = [];
                                                        }
                                                        this.tempCategory.idsArr[keyArr[1]].push(k.value);
                                                        this.tempCategory.label[k.value] =
                                                            keyArr[2] + "/" + k.pathLabels.join("/");
                                                    }
                                                })
                                            });
                                        }
                                    }
                                }
                            }
                        });
                    },
                    deleteCategoryIds(id) {
                        delete this.tempCategory.label[id];
                        for (var key in this.$refs) {
                            if (key.includes('categoryRef')) {
                                if (this.$refs[key].length > 0) {
                                    if (this.$refs[key][0].checkedNodePaths.length > 0) {
                                        this.$refs[key][0].checkedNodePaths.forEach((row) => {
                                            row.forEach(k => {
                                                if (k.data.id == id) {
                                                    k.checked = false;
                                                    this.$refs[key][0].calculateMultiCheckedValue()
                                                }
                                            })
                                        });
                                    }
                                }
                            }
                        }
                    },
                    getCategoryOptions(form) {
                        let that = this;
                        Fast.api.ajax({
                            url: 'drama/category/index?type=video',
                            loading: false,
                        }, function (ret, res) {
                            that.categoryOptions = res.data;
                            // if (that.categoryOptions.length > 0 && !that.categoryTab) that.categoryTab = Number(that.categoryOptions[0].id);
                            if (form) {
                                that.getEditData()
                            }
                            return false;
                        })
                    },
                    createCategory() {
                        let that = this;
                        Fast.api.open("drama/category/index", "新建", {
                            callback(data) {
                                that.getCategoryOptions();
                            }
                        });
                    },
                    submitForm(formName) {
                        this.$refs[formName].validate((valid) => {
                            if (valid) {
                                let that = this;
                                let arrForm = JSON.parse(JSON.stringify(that.videoDetail));
                                arrForm.content = $("#c-content").val();

                                that.mustDel.forEach(i => {
                                    delete arrForm[i]
                                })
                                let submitVideoPerformer = []
                                let submitVideoEpisodes = []
                                submitVideoPerformer = JSON.parse(JSON.stringify(that.videoPerformer))
                                submitVideoEpisodes = JSON.parse(JSON.stringify(that.videoEpisodes))

                                let idsArr = [];
                                for (var key in this.tempCategory.idsArr) {
                                    this.tempCategory.idsArr[key].forEach((k) => {
                                        idsArr.push(Number(k));
                                    });
                                }
                                arrForm.category_ids = idsArr.join(",");
                                if (that.editId && that.type == 'edit') {
                                    Fast.api.ajax({
                                        url: 'drama/video/edit/ids/' + that.editId,
                                        loading: true,
                                        data: {
                                            row: arrForm,
                                            other: {
                                                performerData: JSON.stringify(submitVideoPerformer),
                                                episodesData: JSON.stringify(submitVideoEpisodes)
                                            }
                                        }
                                    }, function (ret, res) {
                                        Fast.api.close();
                                    })
                                } else {
                                    if (this.type == 'copy') {
                                        delete arrForm.id
                                    }
                                    Fast.api.ajax({
                                        url: 'drama/video/add',
                                        loading: true,
                                        data: {
                                            row: arrForm,
                                            other: {
                                                performerData: JSON.stringify(submitVideoPerformer),
                                                episodesData: JSON.stringify(submitVideoEpisodes)
                                            }
                                        }
                                    }, function (ret, res) {
                                        Fast.api.close();
                                    })
                                }

                            } else {
                                return false;
                            }
                        });
                    },
                    resetForm(formName) {
                        this.$refs[formName].resetFields();
                    },
                    addImg(type, index, multiple) {
                        let that = this;
                        parent.Fast.api.open("general/attachment/select?multiple=" + multiple, "选择图片", {
                            callback: function (data) {
                                switch (type) {
                                    case "image":
                                        that.videoDetail.image = data.url;
                                        break;
                                    case "images":
                                        that.videoDetail.images = that.videoDetail.images ? that.videoDetail.images + ',' + data.url : data.url;
                                        let arrs = that.videoDetail.images.split(',');
                                        if (arrs.length > 9) {
                                            that.timeData.images_arr = arrs.slice(-9)
                                        } else {
                                            that.timeData.images_arr = arrs
                                        }
                                        that.videoDetail.images = that.timeData.images_arr.join(',');
                                        break;
                                    case "performer":
                                        that.videoPerformer[index].avatar = data.url;
                                        break;
                                    case "episodes_image":
                                        that.videoEpisodes[index].image = data.url;
                                        break;
                                    case "episodes_video":
                                        that.videoEpisodes[index].video = data.url;
                                        that.videoEpisodes[index].suffix = data.url.substring(data.url.lastIndexOf('.') + 1);
                                        var video = document.createElement('video');
                                        video.src = data.url;
                                        video.addEventListener('loadedmetadata', function() {
                                            that.videoEpisodes[index].duration = parseInt(video.duration);
                                        });
                                        break;
                                }
                            }
                        });
                        return false;
                    },
                    delImg(type, index) {
                        let that = this;
                        switch (type) {
                            case "image":
                                that.videoDetail.image = '';
                                break;
                            case "images":
                                that.timeData.images_arr.splice(index, 1);
                                that.videoDetail.images = that.timeData.images_arr.join(",");
                                break;
                            case "performer":
                                that.videoPerformer[index].avatar = '';
                                break;
                            case "episodes_image":
                                that.videoEpisodes[index].image = '';
                                break;
                            case "episodes_video":
                                that.videoEpisodes[index].video = '';
                                that.videoEpisodes[index].suffix = '';
                                that.videoEpisodes[index].duration = 0;
                                break;
                        }
                    },
                    // 动态获取视频时长
                    getVideoDuration(index) {
                        let that = this;
                        var video = document.createElement('video');
                        video.src = Fast.api.cdnurl(that.videoEpisodes[index].video, true);
                        video.addEventListener('loadedmetadata', function() {
                            that.videoEpisodes[index].duration = parseInt(video.duration);
                        });
                    },
                    categoryChange(val) {
                        this.videoDetail.category_ids = val.join(',');
                    },
                    flagsChange(val) {
                        this.videoDetail.flags = val.join(',');
                    },
                    gotoback(formName) {
                        this.$refs[formName].validate((valid) => {
                            if (valid) {
                                this.stepActive++;
                            } else {
                                return false;
                            }
                        });
                    },
                    gonextback() {
                        this.stepActive--;
                    },

                    //添加演员
                    addVideoPerformer() {
                        this.videoPerformer.push({
                            id: 0,
                            avatar: '',
                            name: '',
                            en_name: '',
                            tags: '',
                            tags_arr: [],
                            profile: '',
                            type: '',
                            play: '',
                            weigh: '',
                        })
                        this.allEditPopoverItems.push({tag:false})
                    },
                    //删除演员
                    delVideoPerformer(k) {
                        // 删除演员
                        this.videoPerformer.splice(k, 1)
                        this.allEditPopoverItems.splice(k, 1);
                    },
                    // 排序
                    changeVideoPerformer(){
                        this.videoPerformer.sort(function(a, b) {
                            if (parseInt(a.weigh) < parseInt(b.weigh)) return 1;
                            if (parseInt(a.weigh) > parseInt(b.weigh)) return -1;
                            if (parseInt(a.id) < parseInt(b.id)) return -1;
                            if (parseInt(a.id) > parseInt(b.id)) return 1;
                            return 0;
                        });
                    },

                    //添加剧集
                    addVideoEpisodes() {
                        this.videoEpisodes.push({
                            id: 0,
                            name: '',
                            image: '',
                            video: '',
                            duration: '',
                            suffix: '',
                            price: '',
                            vprice: '',
                            weigh: '',
                            status: 'normal',
                            fake_likes: '',
                            fake_views: '',
                            fake_favorites: '',
                            fake_shares: '',
                        })
                    },
                    //删除剧集
                    deleteVideoEpisodes(i) {
                        this.videoEpisodes.splice(i, 1)
                    },
                    // 排序
                    changeVideoEpisodes(){
                        this.videoEpisodes.sort(function(a, b) {
                            if (parseInt(a.weigh) < parseInt(b.weigh)) return 1;
                            if (parseInt(a.weigh) > parseInt(b.weigh)) return -1;
                            if (parseInt(a.id) < parseInt(b.id)) return -1;
                            if (parseInt(a.id) > parseInt(b.id)) return 1;
                            return 0;
                        });
                    },
                    editStatus(i) {
                        if (this.videoEpisodes[i].status == 'normal') {
                            this.videoEpisodes[i].status = 'hidden'
                        } else {
                            this.videoEpisodes[i].status = 'normal'
                        }

                    },
                    allEditData(type, opt) {
                        switch (opt) {
                            case 'define':
                                this.videoEpisodes.forEach(i => {
                                    i[type] = this.allEditDatas;
                                })
                                this.allEditDatas = ''
                                this.allEditPopover[type] = false;
                                break;
                            case 'cancel':
                                this.allEditDatas = ''
                                this.allEditPopover[type] = false;
                                break;
                        }
                    },
                    allEditTag(type, opt) {
                        switch (opt) {
                            case 'define':
                                this.timeData.tags_arr.push(this.allEditTags)
                                this.videoDetail.tags = this.timeData.tags_arr.join(',');
                                this.allEditTags = ''
                                this.allEditPopoverTags[type] = false;
                                break;
                            case 'cancel':
                                this.allEditTags = ''
                                this.allEditPopoverTags[type] = false;
                                break;
                        }
                    },
                    tagClose(tag) {
                        let index = this.timeData.tags_arr.indexOf(tag)
                        this.timeData.tags_arr.splice(index, 1)
                        this.videoDetail.tags = this.timeData.tags_arr.join(',');
                    },
                    allEditItem(type, opt, key) {
                        switch (opt) {
                            case 'define':
                                this.videoPerformer[key].tags_arr.push(this.allEditItems)
                                this.videoPerformer[key].tags = this.videoPerformer[key].tags_arr.join(',');
                                this.allEditItems = ''
                                this.allEditPopoverItems[key][type] = false;
                                break;
                            case 'cancel':
                                this.allEditItems = ''
                                this.allEditPopoverItems[key][type] = false;
                                break;
                        }
                    },
                    tagCloseItem(key, tag) {
                        let index = this.videoPerformer[key].tags_arr.indexOf(tag)
                        this.videoPerformer[key].tags_arr.splice(index, 1)
                        this.videoPerformer[key].tags = this.videoPerformer[key].tags_arr.join(',');
                    },
                },
                watch: {
                    stepActive(newVal) {
                        this.editor = null;
                    },
                },
            })
        }
    };
    return Controller;
});
