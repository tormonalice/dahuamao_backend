define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/sou/index' + location.search,
                    //add_url: 'drama/sou/add',
                    //edit_url: 'drama/sou/edit',
                    //del_url: 'drama/sou/del',
                    multi_url: 'drama/sou/multi',
                    //import_url: 'drama/sou/import',
                    table: 'drama_sou',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                //fixedColumns: true,
                //fixedRightNumber: 1,
                searchFormTemplate: 'customformtpl',
                commonsearch: true,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        //{field: 'id', title: __('Id')},
                        //{field: 'video_id', title: __('Video_id')},
                        {field: 'content', title: __('搜索内容'), operate: 'LIKE'},
                        /*
                        {field: 'site_id', title: __('Site_id')},
                        {field: 'video_id', title: __('Video_id')},
                        {field: 'H5_user', title: __('H5_user')},
                        {field: 'H5_user_view', title: __('H5_user_view')},
                        {field: 'H5_visitor_view', title: __('H5_visitor_view')},
                        {field: 'H5_total_view', title: __('H5_total_view')},
                        {field: 'wxOfficialAccount_user', title: __('Wxofficialaccount_user')},
                        {field: 'wxOfficialAccount_user_view', title: __('Wxofficialaccount_user_view')},
                        {field: 'wxOfficialAccount_visitor_view', title: __('Wxofficialaccount_visitor_view')},
                        {field: 'wxOfficialAccount_total_view', title: __('Wxofficialaccount_total_view')},
                        {field: 'wxMiniProgram_user', title: __('Wxminiprogram_user')},
                        {field: 'wxMiniProgram_user_view', title: __('Wxminiprogram_user_view')},
                        {field: 'wxMiniProgram_visitor_view', title: __('Wxminiprogram_visitor_view')},
                        {field: 'wxMiniProgram_total_view', title: __('Wxminiprogram_total_view')},
                        {field: 'App_user', title: __('App_user')},
                        {field: 'App_user_view', title: __('App_user_view')},
                        {field: 'App_visitor_view', title: __('App_visitor_view')},
                        {field: 'App_total_view', title: __('App_total_view')},
                        {field: 'douyinxcx_user', title: __('Douyinxcx_user')},
                        {field: 'douyinxcx_user_view', title: __('Douyinxcx_user_view')},
                        {field: 'douyinxcx_visitor_view', title: __('Douyinxcx_visitor_view')},
                        {field: 'douyinxcx_total_view', title: __('Douyinxcx_total_view')},
                        */
                        {field: 'total_user', title: '搜索用户量'},
                        {field: 'total_user_view', title: '用户搜索量'},
                        {field: 'total_visitor_view', title: '游客搜索量'},
                        {field: 'total_view', title: '总搜索量'},

                        /*
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        */
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
