define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/search_log/index' + location.search,
                    //add_url: 'drama/search_log/add',
                    //edit_url: 'drama/search_log/edit',
                    del_url: 'drama/search_log/del',
                    multi_url: 'drama/search_log/multi',
                    import_url: 'drama/search_log/import',
                    table: 'drama_search_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},

                        //{field: 'site_id', title: __('Site_id')},
                        {field: 'search_content', title: __('Search_content'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'ip', title: __('Ip'), operate: 'LIKE'},
                        {field: 'user_type', title: __('User_type'), searchList: {"1":__('User_type 1'),"2":__('User_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'platform', title: __('Platform'), operate: 'LIKE'},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
