define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/raw_material/index' + location.search,
                    add_url: 'bom/raw_material/add',
                    edit_url: 'bom/raw_material/edit',
                    del_url: 'bom/raw_material/del',
                    multi_url: 'bom/raw_material/multi',
                    import_url: 'bom/raw_material/import',
                    table: 'raw_materials',
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
                        {field: 'id', title: __('Id'), width: 60},
                        {field: 'name', title: __('Name'), operate: 'LIKE',
                         formatter: function(value, row, index) {
                             var typeClass = row.type ? 'label-info' : 'label-default';
                             var typeText = row.type || '未分类';
                             return '<div>' +
                                    '<strong>' + value + '</strong>' +
                                    '<div><span class="label ' + typeClass + '">' + typeText + '</span></div>' +
                                    '</div>';
                         }},
                        {field: 'description', title: __('Description'), operate: 'LIKE',
                         formatter: function(value, row, index) {
                             return value ? 
                                 '<span class="text-muted" title="' + value + '">' + 
                                 (value.length > 40 ? value.substring(0, 40) + '...' : value) + '</span>' : 
                                 '<span class="text-muted">无描述</span>';
                         }},
                        {field: 'type', title: '材料类型', operate: '=', searchList: {"paper":"纸类","plastic":"塑料类","metal":"金属类","wood":"木材类","glass":"玻璃类","ceramic":"陶瓷类","textile":"纺织品","chemical":"化学品","other":"其他"}},
                        {field: 'unit_of_measure', title: '计量单位', operate: '=', searchList: {"per_kg":"每公斤","per_sqm":"每平方米","per_cbm":"每立方米","per_piece":"每件","per_meter":"每米","per_liter":"每升","per_roll":"每卷","per_sheet":"每张"}},
                        {field: 'unit_cost', title: '单位成本', operate: 'BETWEEN', sortable: true, 
                         formatter: function(value, row, index) {
                            var cost = parseFloat(value) || 0;
                            return '<span class="text-success"><strong>¥' + cost.toFixed(4) + '</strong></span>';
                        }},
                        {field: 'standard_length', title: '标准长度(cm)', operate: 'BETWEEN', sortable: true},
                        {field: 'standard_width', title: '标准宽度(cm)', operate: 'BETWEEN', sortable: true},
                        {field: 'gsm', title: '克重(gsm)', operate: 'BETWEEN', sortable: true},
                        {field: 'thickness', title: '厚度(mm)', operate: 'BETWEEN', sortable: true},
                        {field: 'density', title: '密度(g/cm³)', operate: 'BETWEEN', sortable: true},
                        {field: 'supplier', title: '供应商', operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 批量导入
            $(document).on("click", ".btn-import", function () {
                Fast.api.open("bom/raw_material/import", "批量导入原材料", {
                    area: ["90%", "90%"],
                    callback: function(data) {
                        table.bootstrapTable('refresh');
                    }
                });
            });

            // 导出模板
            $(document).on("click", ".btn-export-template", function () {
                Fast.api.open("bom/raw_material/export_template", "导出模板", {
                    area: ["50%", "50%"]
                });
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