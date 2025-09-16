define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/component/index' + location.search,
                    add_url: 'bom/component/add',
                    edit_url: 'bom/component/edit',
                    del_url: 'bom/component/del',
                    multi_url: 'bom/component/multi',
                    import_url: 'bom/component/import',
                    table: 'components',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        {field: 'product.name', title: '所属产品', operate: 'LIKE'},
                        {field: 'parent.name', title: '父部件', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">顶级部件</span>';
                        }},
                        {field: 'quantity', title: '数量', operate: 'BETWEEN', sortable: true},
                        {field: 'length_formula', title: '长度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'width_formula', title: '宽度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'height_formula', title: '高度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'sequence', title: '排序', operate: 'BETWEEN', sortable: true},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 展开全部按钮
            $(document).on("click", ".btn-tree-expand", function () {
                $(".btn-node-sub.disabled").trigger("click");
            });

            // 收起全部按钮  
            $(document).on("click", ".btn-tree-collapse", function () {
                $(".btn-node-sub:not(.disabled)").trigger("click");
            });
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