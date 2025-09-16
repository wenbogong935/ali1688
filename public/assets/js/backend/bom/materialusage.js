define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/materialusage/index' + location.search,
                    add_url: 'bom/materialusage/add',
                    edit_url: 'bom/materialusage/edit',
                    del_url: 'bom/materialusage/del',
                    multi_url: 'bom/materialusage/multi',
                    import_url: 'bom/materialusage/import',
                    table: 'material_usages',
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
                        {field: 'rawMaterial.name', title: '原材料', operate: 'LIKE'},
                        {field: 'alias', title: '别名', operate: 'LIKE'},
                        {field: 'length_formula', title: '长度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'width_formula', title: '宽度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'grammage_override', title: '克重覆盖', operate: 'BETWEEN'},
                        {field: 'thickness_override_mm', title: '厚度覆盖(mm)', operate: 'BETWEEN'},
                        {field: 'imposition_quantity', title: '拼版数量', operate: 'BETWEEN'},
                        {field: 'sequence', title: '排序', operate: 'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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