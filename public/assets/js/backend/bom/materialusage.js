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
                        {field: 'component.name', title: '部件', operate: 'LIKE'},
                        {field: 'component.product.name', title: '所属产品', operate: 'LIKE'},
                        {field: 'rawMaterial.name', title: '原材料', operate: 'LIKE'},
                        {field: 'rawMaterial.type', title: '材料类型', operate: '=', searchList: {"paper":"纸类","plastic":"塑料类","metal":"金属类","wood":"木材类","glass":"玻璃类","ceramic":"陶瓷类","textile":"纺织品","chemical":"化学品","other":"其他"}},
                        {field: 'length_formula', title: '长度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'width_formula', title: '宽度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'height_formula', title: '高度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'panel', title: '拼版数', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return value ? '<span class="label label-info">' + value + '</span>' : '<span class="text-muted">-</span>';
                        }},
                        {field: 'gsm_override', title: '克重覆盖', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return value ? value + 'gsm' : '<span class="text-muted">默认</span>';
                        }},
                        {field: 'thickness_override', title: '厚度覆盖', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return value ? value + 'mm' : '<span class="text-muted">默认</span>';
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

            // 批量分配
            $(document).on("click", ".btn-batch-assign", function () {
                Fast.api.open("bom/materialusage/batch_assign", "批量分配材料", {
                    area: ["90%", "90%"],
                    callback: function(data) {
                        table.bootstrapTable('refresh');
                    }
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
            },
            loadMaterialInfo: function(materialId) {
                if (!materialId) return;
                
                // 获取材料信息并预填充相关字段
                Fast.api.ajax({
                    url: "bom/rawmaterial/detail",
                    data: {id: materialId},
                    success: function(data) {
                        if (data.code === 1 && data.data) {
                            var material = data.data;
                            // 可以在这里预填充一些默认值
                            if (material.standard_length) {
                                $("#c-length_formula").attr('placeholder', '默认: ' + material.standard_length + 'cm');
                            }
                            if (material.standard_width) {
                                $("#c-width_formula").attr('placeholder', '默认: ' + material.standard_width + 'cm');
                            }
                            if (material.standard_height) {
                                $("#c-height_formula").attr('placeholder', '默认: ' + material.standard_height + 'cm');
                            }
                        }
                    }
                });
            }
        }
    };
    return Controller;
});