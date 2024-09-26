define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'drama/video_favorite/index' + location.search,
                    add_url: 'drama/video_favorite/add',
                    edit_url: 'drama/video_favorite/edit',
                    del_url: 'drama/video_favorite/del',
                    multi_url: 'drama/video_favorite/multi',
                    import_url: 'drama/video_favorite/import',
                    table: 'drama_video_favorite',
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
                        {field: 'site_id', title: __('Site_id')},
                        {field: 'type', title: __('Type'), searchList: {"like":__('Type like'),"favorite":__('Type favorite')}, formatter: Table.api.formatter.normal},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'vid', title: __('Vid')},
                        {field: 'episode_id', title: __('Episode_id')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'video.title', title: __('Video.title'), operate: 'LIKE'},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
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
