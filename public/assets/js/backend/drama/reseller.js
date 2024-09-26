define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/reseller/index' + location.search,
                    add_url: 'drama/reseller/add',
                    edit_url: 'drama/reseller/edit',
                    del_url: 'drama/reseller/del',
                    multi_url: 'drama/reseller/multi',
                    import_url: 'drama/reseller/import',
                    table: 'drama_reseller',
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
                        {field: 'level', title: __('等级'), operate: false},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'product_id', title: '虚拟支付道具ID', operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'price', title: __('Price'), operate: false},
                        {field: 'original_price', title: __('Original_price'), operate: false},
                        {field: 'direct', title: __('Direct'), operate: false, formatter: function (value, row, index) {
                                return value+'%';
                            }},
                        {field: 'indirect', title: __('Indirect'), operate: false, formatter: function (value, row, index) {
                                return value+'%';
                            }},
                        {field: 'expire', title: __('Expire'), operate: false, formatter: function (value, row, index) {
                                if(value == 0){
                                    return '永久';
                                }else if(value % (86400 * 365) == 0){
                                    return Math.floor(value/(86400 * 365))+'年';
                                }else if(value % (86400 * 30) == 0){
                                    return Math.floor(value/(86400 * 30))+'月';
                                }else{
                                    return Math.floor(value/86400)+'天';
                                }
                            }},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });
            $(document).on('click', '.btn-sync', function () {
                Layer.confirm('确认导入测试数据？', {icon: 3, title: __('导入')}, function () {
                    Fast.api.ajax({
                        url: 'drama/reseller/sync',
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
                url: 'drama/reseller/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), align: 'left'},
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
                                    url: 'drama/reseller/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'drama/reseller/destroy',
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
                //选择规则
                $(document).on("click", ".rulelist > li > a", function () {
                    $("#c-expire_type").val($(this).data("value"));
                    $(this).parent().addClass("active").siblings().removeClass("active");
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
