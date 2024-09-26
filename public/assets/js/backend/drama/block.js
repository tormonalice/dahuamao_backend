define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/block/index' + location.search,
                    add_url: 'drama/block/add',
                    edit_url: 'drama/block/edit',
                    del_url: 'drama/block/del',
                    multi_url: 'drama/block/multi',
                    import_url: 'drama/block/import',
                    table: 'drama_block',
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
                        {field: 'type', title: __('Type'), searchList: {"focus":__('Type focus'),"side":__('Type side')}, formatter: Table.api.formatter.normal},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'url', title: __('Url'), operate: 'LIKE', formatter: Table.api.formatter.url},
                        // {field: 'parsetpl', title: __('Parsetpl'), searchList: {"0":__('Parsetpl 0'),"1":__('Parsetpl 1')}, formatter: Table.api.formatter.normal},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            $(document).on('click', '.btn-sync', function () {
                Layer.confirm('确认导入测试数据？', {icon: 3, title: __('导入')}, function () {
                    Fast.api.ajax({
                        url: 'drama/block/sync',
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
                $(document).on("change", "#c-select-name", function () {
                    $("#c-name").val($(this).val());
                });
                $(document).on("click", ".btn-select-link", function () {
                    var url = $(this).data("url");
                    parent.Fast.api.open(url, "选择链接", {
                        callback: function (data) {
                            $("#c-url").val(typeof data === 'string' ? data : data.url);
                        }
                    });
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
