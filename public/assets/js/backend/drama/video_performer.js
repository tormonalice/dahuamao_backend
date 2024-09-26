define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/video_performer/index' + location.search,
                    add_url: 'drama/video_performer/add',
                    edit_url: 'drama/video_performer/edit',
                    del_url: 'drama/video_performer/del',
                    multi_url: 'drama/video_performer/multi',
                    import_url: 'drama/video_performer/import',
                    table: 'drama_video_performer',
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
                        {field: 'site_id', title: __('Site_id')},
                        {field: 'vid', title: __('Vid')},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'en_name', title: __('En_name'), operate: 'LIKE'},
                        {field: 'avatar', title: __('Avatar'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'tags', title: __('Tags'), operate: 'LIKE', formatter: Table.api.formatter.flag},
                        {field: 'play', title: __('Play'), operate: 'LIKE'},
                        {field: 'profile', title: __('Profile'), operate: false, formatter: Table.api.formatter.file},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'video.title', title: __('Video.title'), operate: 'LIKE'},
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
