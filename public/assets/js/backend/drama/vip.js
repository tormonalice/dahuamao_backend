define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/vip/index' + location.search,
                    add_url: 'drama/vip/add',
                    edit_url: 'drama/vip/edit',
                    del_url: 'drama/vip/del',
                    multi_url: 'drama/vip/multi',
                    import_url: 'drama/vip/import',
                    table: 'drama_vip',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'type', title: __('Type'), searchList: {"d":__('Type d'),"m":__('Type m'),"q":__('Type q'),"y":__('Type y')}, formatter: Table.api.formatter.normal},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'product_id', title: '虚拟支付道具ID', operate: 'LIKE'},
                        {field: 'desc', title: __('Desc'), operate: 'LIKE'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        // {field: 'first_price', title: __('First_price'), operate:'BETWEEN'},
                        {field: 'original_price', title: __('Original_price'), operate:'BETWEEN'},
                        {field: 'num', title: __('Num')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            $(document).on('click', '.btn-sync', function () {
                Layer.confirm('确认导入测试数据？', {icon: 3, title: __('导入')}, function () {
                    Fast.api.ajax({
                        url: 'drama/vip/sync',
                    }, function (data, ret) {
                        $(".btn-refresh").trigger("click");
                        Layer.closeAll();
                    });
                });
                return false;
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
