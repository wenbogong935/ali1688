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
                sortName: 'sequence',
                sortOrder: 'asc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'component.name', title: '部件', operate: 'LIKE', 
                         formatter: function(value, row, index) {
                             if (row.component && row.component.product) {
                                 return '<strong>' + row.component.name + '</strong><br>' + 
                                        '<small class="text-muted">' + row.component.product.name + '</small>';
                             }
                             return value || '<span class="text-muted">-</span>';
                         }},
                        {field: 'rawMaterial.name', title: '原材料', operate: 'LIKE',
                         formatter: function(value, row, index) {
                             if (row.rawMaterial) {
                                 var badge = '';
                                 if (row.rawMaterial.type) {
                                     var typeMap = {
                                         'paper': 'label-info',
                                         'plastic': 'label-warning', 
                                         'metal': 'label-primary',
                                         'wood': 'label-success',
                                         'other': 'label-default'
                                     };
                                     badge = '<span class="label ' + (typeMap[row.rawMaterial.type] || 'label-default') + '">' + 
                                            row.rawMaterial.type + '</span> ';
                                 }
                                 return badge + value;
                             }
                             return '<span class="text-muted">-</span>';
                         }},
                        {field: 'length_formula', title: '长度公式', operate: 'LIKE', 
                         formatter: Controller.api.formatFormula},
                        {field: 'width_formula', title: '宽度公式', operate: 'LIKE', 
                         formatter: Controller.api.formatFormula},
                        {field: 'height_formula', title: '高度公式', operate: 'LIKE', 
                         formatter: Controller.api.formatFormula},
                        {field: 'panel', title: '拼版数', operate: 'BETWEEN', sortable: true, 
                         formatter: function(value, row, index) {
                             return value ? '<span class="label label-info">' + value + '</span>' : 
                                          '<span class="text-muted">1</span>';
                         }},
                        {field: 'gsm_override', title: '克重覆盖', operate: 'BETWEEN', sortable: true, 
                         formatter: function(value, row, index) {
                             return value ? '<strong>' + value + 'gsm</strong>' : 
                                          '<span class="text-muted">默认</span>';
                         }},
                        {field: 'thickness_override', title: '厚度覆盖', operate: 'BETWEEN', sortable: true, 
                         formatter: function(value, row, index) {
                             return value ? '<strong>' + value + 'mm</strong>' : 
                                          '<span class="text-muted">默认</span>';
                         }},
                        {field: 'sequence', title: '排序', operate: 'BETWEEN', sortable: true,
                         formatter: function(value, row, index) {
                             return '<span class="text-primary">' + (value || 0) + '</span>';
                         }},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', 
                         addclass:'datetimerange', autocomplete:false, 
                         formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, 
                         events: Table.api.events.operate, 
                         formatter: Table.api.formatter.operate,
                         buttons: [
                             {
                                 name: 'detail',
                                 text: '详情',
                                 title: '查看详情',
                                 classname: 'btn btn-xs btn-info btn-dialog',
                                 icon: 'fa fa-eye',
                                 url: 'bom/materialusage/detail'
                             }
                         ]}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 批量分配材料使用
            $(document).on("click", ".btn-batch-assign", function () {
                Fast.api.open("bom/materialusage/batch_assign", "批量分配材料", {
                    area: ["90%", "90%"],
                    callback: function(data) {
                        table.bootstrapTable('refresh');
                        Toastr.success("批量分配完成");
                    }
                });
            });

            // 成本预览
            $(document).on("click", ".btn-cost-preview", function () {
                var ids = Table.api.selectedids(table);
                if (ids.length === 0) {
                    Toastr.error("请先选择要预览成本的材料使用");
                    return;
                }
                Fast.api.open("bom/materialusage/cost_preview?ids=" + ids.join(','), "成本预览", {
                    area: ["80%", "70%"]
                });
            });
        },

        add: function () {
            Controller.api.bindevent();
            Controller.api.initFormulas();
        },

        edit: function () {
            Controller.api.bindevent();
            Controller.api.initFormulas();
        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                
                // 材料选择变化事件
                $(document).on('change', '#c-raw_material_id', function() {
                    Controller.api.loadMaterialInfo($(this).val());
                });

                // 部件选择变化事件  
                $(document).on('change', '#c-component_id', function() {
                    Controller.api.loadComponentInfo($(this).val());
                });

                // 公式输入提示
                $('.formula-input').on('focus', function() {
                    $(this).next('.formula-help').fadeIn();
                }).on('blur', function() {
                    $(this).next('.formula-help').fadeOut();
                });
            },

            // 格式化公式显示
            formatFormula: function(value, row, index) {
                if (!value || value === '0') {
                    return '<span class="text-muted">未设置</span>';
                }
                var isFormula = value.startsWith('=');
                var display = isFormula ? 
                    '<code class="text-primary">' + value + '</code>' :
                    '<strong class="text-success">' + value + '</strong>';
                return display;
            },

            // 初始化公式输入框
            initFormulas: function() {
                // 为公式输入框添加样式
                $('.formula-input').addClass('font-monospace');
                
                // 添加公式验证
                $('.formula-input').on('input', function() {
                    var value = $(this).val();
                    var isValid = Controller.api.validateFormula(value);
                    $(this).toggleClass('is-invalid', !isValid);
                });
            },

            // 验证公式格式
            validateFormula: function(formula) {
                if (!formula) return true;
                if (formula.match(/^\d+(\.\d+)?$/)) return true; // 纯数字
                if (formula.match(/^=[LWH\d\+\-\*\/\(\)\.\s]+$/)) return true; // 公式
                return false;
            },

            // 加载材料信息
            loadMaterialInfo: function(materialId) {
                if (!materialId) return;
                
                Fast.api.ajax({
                    url: "bom/rawmaterial/detail",
                    data: {id: materialId},
                    success: function(data) {
                        if (data.code === 1 && data.data) {
                            var material = data.data;
                            // 更新占位符提示
                            if (material.standard_length) {
                                $("#c-length_formula").attr('placeholder', '默认: ' + material.standard_length + 'cm');
                            }
                            if (material.standard_width) {
                                $("#c-width_formula").attr('placeholder', '默认: ' + material.standard_width + 'cm');
                            }
                            if (material.thickness_mm) {
                                $("#c-height_formula").attr('placeholder', '默认: ' + material.thickness_mm + 'mm');
                            }
                            
                            // 显示材料属性信息
                            Controller.api.showMaterialPreview(material);
                        }
                    }
                });
            },

            // 加载部件信息
            loadComponentInfo: function(componentId) {
                if (!componentId) return;
                
                Fast.api.ajax({
                    url: "bom/component/detail", 
                    data: {id: componentId},
                    success: function(data) {
                        if (data.code === 1 && data.data) {
                            var component = data.data;
                            // 显示部件层次信息
                            Controller.api.showComponentPreview(component);
                        }
                    }
                });
            },

            // 显示材料预览
            showMaterialPreview: function(material) {
                var preview = '<div class="material-preview alert alert-info">' +
                             '<strong>材料属性:</strong> ' +
                             '类型: ' + (material.type || '-') + ', ' +
                             '克重: ' + (material.std_grammage_gsm || '-') + 'gsm, ' +
                             '厚度: ' + (material.thickness_mm || '-') + 'mm' +
                             '</div>';
                $('.material-preview').remove();
                $('#c-raw_material_id').closest('.form-group').after(preview);
            },

            // 显示部件预览  
            showComponentPreview: function(component) {
                var preview = '<div class="component-preview alert alert-success">' +
                             '<strong>部件信息:</strong> ' +
                             '产品: ' + (component.product ? component.product.name : '-') + ', ' +
                             '层级: ' + (component.hierarchy_path || component.name) +
                             '</div>';
                $('.component-preview').remove();
                $('#c-component_id').closest('.form-group').after(preview);
            }
        }
    };
    
    return Controller;
});