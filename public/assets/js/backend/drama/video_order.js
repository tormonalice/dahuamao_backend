define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/video_order/index' + location.search,
                    //add_url: 'drama/video_order/add',
                    //edit_url: 'drama/video_order/edit',
                    del_url: 'drama/video_order/del',
                    //multi_url: 'drama/video_order/multi',
                    //import_url: 'drama/video_order/import',
                    table: 'drama_video_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        //{field: 'site_id', title: __('Site_id')},
                        {field: 'video.title', title: __('剧目'), operate: 'LIKE',formatter: function (value, row, index) {
                                if(value){
                                    return value;
                                }else{
                                    return '已删除或为空';
                                }
                            }},
                        {field: 'episodes.name', title: __('剧集'), operate: 'LIKE',formatter: function (value, row, index) {
                                if(value){
                                    return value;
                                }else{
                                    return '已删除或为空';
                                }
                            }},
                        {field: 'vid', title: __('剧目ID')},
                        {field: 'episode_id', title: __('剧集ID')},
                        {field: 'order_sn', title: __('Order_sn'), operate: 'LIKE'},
                        {field: 'user.nickname', title: __('用户')},
                        {field: 'user_id', title: __('用户ID')},
                        {field: 'total_fee', title: __('Total_fee')},
                        {field: 'platform', title: __('Platform'), searchList: {"H5":__('Platform h5'),"wxOfficialAccount":__('Platform wxofficialaccount'),"wxMiniProgram":__('Platform wxminiprogram'),"App":__('Platform app'),"douyinxcx":"抖音小程序"}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
                url: 'drama/video_order/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
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
                                    url: 'drama/video_order/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'drama/video_order/destroy',
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
