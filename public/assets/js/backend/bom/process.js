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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        {field: 'cost_type', title: '成本类型', operate: '=', searchList: {"per_unit_variable":"每单位可变","per_area_variable":"每面积可变","per_time_variable":"每时间可变","fixed":"固定"}, formatter: function(value, row, index) {
                            var types = {
                                'per_unit_variable': '<span class="label label-primary">每单位可变</span>',
                                'per_area_variable': '<span class="label label-success">每面积可变</span>',
                                'per_time_variable': '<span class="label label-info">每时间可变</span>',
                                'fixed': '<span class="label label-warning">固定</span>'
                            };
                            return types[value] || value;
                        }},
                        {field: 'unit_of_cost', title: '成本单位', operate: '=', searchList: {"yuan_per_piece":"元/件","yuan_per_sqm":"元/平方米","yuan_per_hour":"元/小时","yuan_per_batch":"元/批次","yuan_per_kg":"元/公斤"}},
                        {field: 'rate', title: '费率', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return '￥' + parseFloat(value).toFixed(2);
                        }},
                        {field: 'setup_cost', title: '设置费', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return value ? '￥' + parseFloat(value).toFixed(2) : '-';
                        }},
                        {field: 'throughput_rate', title: '吞吐率', operate: 'BETWEEN', sortable: true},
                        {field: 'waste_rate', title: '损耗率(%)', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return value ? parseFloat(value).toFixed(2) + '%' : '0%';
                        }},
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
                Fast.api.open("bom/process/import", "批量导入工艺", {
                    area: ["90%", "90%"],
                    callback: function(data) {
                        table.bootstrapTable('refresh');
                    }
                });
            });

            // 导出模板
            $(document).on("click", ".btn-export-template", function () {
                Fast.api.open("bom/process/export_template", "导出模板", {
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
            },
            updateCostFields: function(costType) {
                // 根据成本类型更新相关字段的显示状态
                var $setupCost = $("#c-setup_cost").closest('.form-group');
                var $throughputRate = $("#c-throughput_rate").closest('.form-group');
                var $wasteRate = $("#c-waste_rate").closest('.form-group');
                
                if (costType === 'fixed') {
                    $setupCost.hide();
                    $throughputRate.hide();
                } else {
                    $setupCost.show();
                    $throughputRate.show();
                }
            }
        }
    };
    return Controller;
});