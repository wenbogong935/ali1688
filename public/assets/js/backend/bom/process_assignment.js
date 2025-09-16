define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/process_assignment/index' + location.search,
                    add_url: 'bom/process_assignment/add',
                    edit_url: 'bom/process_assignment/edit',
                    del_url: 'bom/process_assignment/del',
                    multi_url: 'bom/process_assignment/multi',
                    import_url: 'bom/process_assignment/import',
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
                        {field: 'assignment_type', title: '分配类型', operate: '=', searchList: {"component":"部件","material":"材料"}, formatter: function(value, row, index) {
                            var types = {
                                'component': '<span class="label label-primary">部件</span>',
                                'material': '<span class="label label-success">材料</span>'
                            };
                            return types[value] || value;
                        }},
                        {field: 'component.name', title: '部件', operate: 'LIKE'},
                        {field: 'materialUsage.rawMaterial.name', title: '材料', operate: 'LIKE'},
                        {field: 'process.name', title: '工艺', operate: 'LIKE'},
                        {field: 'process.cost_type', title: '工艺类型', operate: '=', searchList: {"per_unit_variable":"每单位可变","per_area_variable":"每面积可变","per_time_variable":"每时间可变","fixed":"固定"}, formatter: function(value, row, index) {
                            var types = {
                                'per_unit_variable': '<span class="label label-primary">每单位可变</span>',
                                'per_area_variable': '<span class="label label-success">每面积可变</span>',
                                'per_time_variable': '<span class="label label-info">每时间可变</span>',
                                'fixed': '<span class="label label-warning">固定</span>'
                            };
                            return types[value] || value;
                        }},
                        {field: 'process_order', title: '工艺顺序', operate: 'BETWEEN', sortable: true},
                        {field: 'rate_override', title: '费率覆盖', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return value ? '￥' + parseFloat(value).toFixed(2) : '<span class="text-muted">默认</span>';
                        }},
                        {field: 'setup_cost_override', title: '设置费覆盖', operate: 'BETWEEN', sortable: true, formatter: function(value, row, index) {
                            return value ? '￥' + parseFloat(value).toFixed(2) : '<span class="text-muted">默认</span>';
                        }},
                        {field: 'panel_affects_cost', title: '拼版影响', operate: '=', searchList: {"1":"是","0":"否"}, formatter: function(value, row, index) {
                            return value == 1 ? '<span class="label label-success">是</span>' : '<span class="label label-default">否</span>';
                        }},
                        {field: 'transform_attribute', title: '转换属性', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">无</span>';
                        }},
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
                Fast.api.open("bom/process_assignment/batch_assign", "批量分配工艺", {
                    area: ["90%", "90%"],
                    callback: function(data) {
                        table.bootstrapTable('refresh');
                    }
                });
            });

            // 工艺流程
            $(document).on("click", ".btn-workflow", function () {
                Fast.api.open("bom/process_assignment/workflow", "工艺流程图", {
                    area: ["95%", "90%"]
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
            toggleAssignmentFields: function(assignmentType) {
                var $componentField = $("#component-field");
                var $materialField = $("#material-field");
                var $componentId = $("#c-component_id");
                var $materialUsageId = $("#c-material_usage_id");
                
                if (assignmentType === 'component') {
                    $componentField.show();
                    $materialField.hide();
                    $componentId.attr('data-rule', 'required');
                    $materialUsageId.removeAttr('data-rule');
                } else if (assignmentType === 'material') {
                    $componentField.hide();
                    $materialField.show();
                    $componentId.removeAttr('data-rule');
                    $materialUsageId.attr('data-rule', 'required');
                } else {
                    $componentField.hide();
                    $materialField.hide();
                    $componentId.removeAttr('data-rule');
                    $materialUsageId.removeAttr('data-rule');
                }
            },
            loadProcessInfo: function(processId) {
                if (!processId) return;
                
                // 获取工艺信息并预填充相关字段
                Fast.api.ajax({
                    url: "bom/process/detail",
                    data: {id: processId},
                    success: function(data) {
                        if (data.code === 1 && data.data) {
                            var process = data.data;
                            // 可以在这里预填充一些默认值
                            if (process.rate) {
                                $("#c-rate_override").attr('placeholder', '默认: ￥' + process.rate);
                            }
                            if (process.setup_cost) {
                                $("#c-setup_cost_override").attr('placeholder', '默认: ￥' + process.setup_cost);
                            }
                        }
                    }
                });
            }
        }
    };
    return Controller;
});