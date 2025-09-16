define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/processassignment/index' + location.search,
                    add_url: 'bom/processassignment/add',
                    edit_url: 'bom/processassignment/edit',
                    del_url: 'bom/processassignment/del',
                    multi_url: 'bom/processassignment/multi',
                    import_url: 'bom/processassignment/import',
                    table: 'process_assignments',
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
                        {field: 'component.product.name', title: '产品', operate: 'LIKE'},
                        {field: 'component.name', title: '部件', operate: 'LIKE'},
                        {field: 'materialUsage.rawMaterial.name', title: '材料', operate: 'LIKE'},
                        {field: 'process.name', title: '工艺', operate: 'LIKE'},
                        {field: 'alias', title: '别名', operate: 'LIKE'},
                        {field: 'sequence', title: '排序', operate: 'BETWEEN'},
                        {field: 'cost_override', title: '成本覆盖', operate: 'BETWEEN'},
                        {field: 'is_affected_by_imposition', title: '受拼版影响', searchList: {"0":"否","1":"是"}, formatter: Table.api.formatter.toggle},
                        {field: 'is_transformative', title: '转换性工艺', searchList: {"0":"否","1":"是"}, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, 
                         buttons: [
                             {
                                 name: 'copy',
                                 text: '复制工艺分配',
                                 title: '复制工艺分配',
                                 classname: 'btn btn-xs btn-warning btn-dialog',
                                 icon: 'fa fa-copy',
                                 url: 'bom/processassignment/copy'
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
        copy: function () {
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