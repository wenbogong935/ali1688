define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/process/index' + location.search,
                    add_url: 'bom/process/add',
                    edit_url: 'bom/process/edit',
                    del_url: 'bom/process/del',
                    multi_url: 'bom/process/multi',
                    import_url: 'bom/process/import',
                    table: 'processes',
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
                        {field: 'name', title: '工艺名称', operate: 'LIKE'},
                        {field: 'cost_type', title: '成本类型', searchList: {"fixed":"固定成本","rate":"费率成本","formula":"公式成本"}, formatter: Table.api.formatter.normal},
                        {field: 'unit_of_cost', title: '成本单位', searchList: {"per_item":"每件","per_sqm":"每平方米","per_hour":"每小时","per_setup":"每次设置"}, formatter: Table.api.formatter.normal},
                        {field: 'cost_rate', title: '成本费率', operate: 'BETWEEN'},
                        {field: 'setup_cost', title: '设置成本', operate: 'BETWEEN'},
                        {field: 'throughput_rate', title: '产能效率', operate: 'BETWEEN'},
                        {field: 'labor_rate_per_hour', title: '人工费率/小时', operate: 'BETWEEN'},
                        {field: 'waste_percentage', title: '损耗百分比(%)', operate: 'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, 
                         buttons: [
                             {
                                 name: 'copy',
                                 text: '复制工艺',
                                 title: '复制工艺',
                                 classname: 'btn btn-xs btn-warning btn-dialog',
                                 icon: 'fa fa-copy',
                                 url: 'bom/process/copy'
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