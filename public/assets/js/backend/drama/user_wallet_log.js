define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/user_wallet_log/index' + location.search,
                    add_url: 'drama/user_wallet_log/add',
                    edit_url: 'drama/user_wallet_log/edit',
                    del_url: 'drama/user_wallet_log/del',
                    multi_url: 'drama/user_wallet_log/multi',
                    import_url: 'drama/user_wallet_log/import',
                    table: 'drama_user_wallet_log',
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
                        {field: 'user_id', title: __('User_id'),
                            visible: false,
                            addclass: 'selectpage',
                            extend: 'data-source="drama/user/index" data-field="nickname"',
                            operate: '=',
                            formatter: Table.api.formatter.search
                        },
                        {field: 'user.nickname', title: __('会员昵称'), operate: 'LIKE'},
                        {field: 'wallet', title: __('Wallet'), operate:'BETWEEN'},
                        {field: 'wallet_type', title: __('Wallet_type'), searchList: {"money":__('Wallet_type money'),"score":__('Wallet_type score'),"usable":__('Wallet_type usable')}, formatter: Table.api.formatter.normal},
                        {field: 'type', title: __('Type'), searchList: Config.typeList, formatter: Table.api.formatter.normal},
                        {field: 'before', title: __('Before'), operate:'BETWEEN'},
                        {field: 'after', title: __('After'), operate:'BETWEEN'},
                        {field: 'memo', title: __('Memo'), operate: 'LIKE'},
                        {field: 'oper_type', title: __('Oper_type'), searchList: {"user":__('User'),"store":__('Store'),"admin":__('Admin'),"system":__('System')}, formatter: Table.api.formatter.normal},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
