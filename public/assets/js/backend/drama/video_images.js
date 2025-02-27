define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/video_images/index' + location.search,
                    add_url: 'drama/video_images/add',
                    edit_url: 'drama/video_images/edit',
                    del_url: 'drama/video_images/del',
                    multi_url: 'drama/video_images/multi',
                    import_url: 'drama/video_images/import',
                    table: 'drama_video_images',
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
                        {field: 'vid', title: __('Vid'),
                            visible: false,
                            addclass: 'selectpage',
                            extend: 'data-source="drama/video/index" data-field="title"',
                            operate: '=',
                            formatter: Table.api.formatter.search
                        },
                        {field: 'video.title', title: __('短剧'), operate: false},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'views', title: __('Views'), operate: false},
                        {field: 'downloads', title: __('Downloads'), operate: false},
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
