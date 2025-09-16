define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/product/index' + location.search,
                    add_url: 'bom/product/add',
                    edit_url: 'bom/product/edit',
                    del_url: 'bom/product/del',
                    multi_url: 'bom/product/multi',
                    import_url: 'bom/product/import',
                    table: 'products',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE', 
                         formatter: function(value, row, index) {
                             var status = row.status === 'active' ? 
                                '<span class="label label-success">启用</span>' : 
                                '<span class="label label-default">禁用</span>';
                             return '<strong>' + value + '</strong> ' + status;
                         }},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, 
                         events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'length_formula', title: '长度公式', operate: 'LIKE', 
                         formatter: Controller.api.formatFormula},
                        {field: 'width_formula', title: '宽度公式', operate: 'LIKE', 
                         formatter: Controller.api.formatFormula},
                        {field: 'height_formula', title: '高度公式', operate: 'LIKE', 
                         formatter: Controller.api.formatFormula},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', 
                         addclass:'datetimerange', autocomplete:false, 
                         formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, 
                         events: Table.api.events.operate, 
                         buttons: [
                             {
                                 name: 'bom',
                                 text: 'BOM管理',
                                 title: 'BOM管理',
                                 classname: 'btn btn-xs btn-success btn-dialog',
                                 icon: 'fa fa-sitemap',
                                 url: 'bom/product/bom',
                                 extend: 'data-area=\'["90%","90%"]\''
                             },
                             {
                                 name: 'calculate',
                                 text: '成本计算',
                                 title: '成本计算',
                                 classname: 'btn btn-xs btn-info btn-dialog',
                                 icon: 'fa fa-calculator',
                                 url: 'bom/product/calculate',
                                 extend: 'data-area=\'["90%","90%"]\''
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
            Controller.api.initFormulas();
        },
        
        edit: function () {
            Controller.api.bindevent();
            Controller.api.initFormulas();
        },
        
        bom: function () {
            Controller.api.bindevent();
            
            // 初始化BOM树
            Controller.api.initBomTree();
            
            // 绑定树节点事件
            Controller.api.bindTreeEvents();
            
            // 绑定工具栏事件
            Controller.api.bindBomToolbar();
        },
        
        calculate: function () {
            Controller.api.bindevent();
            
            // 初始化成本计算界面
            Controller.api.initCostCalculation();
        },
        
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            
            // 格式化公式显示
            formatFormula: function(value, row, index) {
                if (!value || value === '0') {
                    return '<span class="text-muted">未设置</span>';
                }
                var isFormula = value.startsWith('=');
                return isFormula ? 
                    '<code class="text-primary">' + value + '</code>' :
                    '<strong class="text-success">' + value + '</strong>';
            },
            
            // 初始化公式输入
            initFormulas: function() {
                $('.formula-input').addClass('font-monospace');
                $('.formula-input').on('input', function() {
                    var isValid = Controller.api.validateFormula($(this).val());
                    $(this).toggleClass('is-invalid', !isValid);
                });
            },
            
            // 验证公式
            validateFormula: function(formula) {
                if (!formula) return true;
                if (formula.match(/^\d+(\.\d+)?$/)) return true;
                if (formula.match(/^=[LWH\d\+\-\*\/\(\)\.\s]+$/)) return true;
                return false;
            },
            
            // 初始化BOM树
            initBomTree: function() {
                var $bomTree = $("#bom-tree");
                if ($bomTree.length === 0) return;
                
                var productId = Fast.api.query('ids');
                if (!productId) return;
                
                Fast.api.ajax({
                    url: "bom/product/getBomTree",
                    data: {id: productId},
                    success: function(data) {
                        if (data.code === 1) {
                            Controller.api.renderBomTree(data.data);
                        } else {
                            $bomTree.html('<div class="bom-error"><i class="fa fa-warning"></i> ' + 
                                        (data.msg || '加载BOM树失败') + '</div>');
                        }
                    },
                    error: function() {
                        $bomTree.html('<div class="bom-error"><i class="fa fa-warning"></i> 网络错误</div>');
                    }
                });
            },
            
            // 渲染BOM树
            renderBomTree: function(treeData) {
                var $container = $("#bom-tree");
                $container.empty();
                
                function renderNode(node, level) {
                    level = level || 0;
                    var indent = level * 20;
                    var nodeClass = 'tree-node';
                    var nodeIcon = 'fa-cube';
                    
                    if (node.type === 'component') {
                        nodeClass += ' component';
                        nodeIcon = 'fa-cubes';
                    } else if (node.type === 'material') {
                        nodeClass += ' material';
                        nodeIcon = 'fa-industry';
                    } else if (node.type === 'process') {
                        nodeClass += ' process';
                        nodeIcon = 'fa-cogs';
                    }
                    
                    var nodeHtml = '<div class="' + nodeClass + '" style="margin-left: ' + indent + 'px;" ' +
                        'data-id="' + node.id + '" data-type="' + node.type + '">' +
                        '<i class="fa ' + nodeIcon + ' node-icon ' + node.type + '"></i>' +
                        '<span class="node-title">' + node.name + '</span>';
                        
                    if (node.description) {
                        nodeHtml += '<span class="node-info text-muted"> - ' + node.description + '</span>';
                    }
                    
                    nodeHtml += '</div>';
                    $container.append(nodeHtml);
                    
                    if (node.children && node.children.length > 0) {
                        node.children.forEach(function(child) {
                            renderNode(child, level + 1);
                        });
                    }
                }
                
                if (treeData && treeData.components) {
                    treeData.components.forEach(function(component) {
                        renderNode(component, 0);
                    });
                } else {
                    $container.html('<div class="text-center text-muted"><i class="fa fa-info-circle"></i> 暂无BOM数据</div>');
                }
            },
            
            // 绑定树节点事件
            bindTreeEvents: function() {
                $(document).on('click', '.tree-node', function() {
                    $('.tree-node').removeClass('selected');
                    $(this).addClass('selected');
                    
                    var nodeId = $(this).data('id');
                    var nodeType = $(this).data('type');
                    
                    Controller.api.loadNodeDetails(nodeId, nodeType);
                });
            },
            
            // 绑定BOM工具栏事件
            bindBomToolbar: function() {
                // 展开全部
                $(document).on('click', '.btn-tree-expand', function() {
                    $('.tree-node').show();
                });
                
                // 收起全部  
                $(document).on('click', '.btn-tree-collapse', function() {
                    $('.tree-node').each(function() {
                        if ($(this).css('margin-left') !== '0px') {
                            $(this).hide();
                        }
                    });
                });
            },
            
            // 加载节点详情
            loadNodeDetails: function(nodeId, nodeType) {
                var $detailPanel = $("#bom-detail");
                if ($detailPanel.length === 0) return;
                
                $detailPanel.html('<div class="bom-loading"><i class="fa fa-spinner fa-spin"></i><br>加载中...</div>');
                
                var urlMap = {
                    'component': 'bom/component/detail',
                    'material': 'bom/rawmaterial/detail', 
                    'process': 'bom/process/detail'
                };
                
                var url = urlMap[nodeType];
                if (!url) return;
                
                Fast.api.ajax({
                    url: url,
                    data: {id: nodeId},
                    success: function(data) {
                        if (data.code === 1) {
                            Controller.api.renderNodeDetails(data.data, nodeType);
                        } else {
                            $detailPanel.html('<div class="bom-error"><i class="fa fa-warning"></i> ' + 
                                            (data.msg || '加载详情失败') + '</div>');
                        }
                    }
                });
            },
            
            // 渲染节点详情
            renderNodeDetails: function(nodeData, nodeType) {
                var $detailPanel = $("#bom-detail");
                var detailHtml = '<div class="bom-detail-panel">' +
                    '<div class="bom-detail-header">' + 
                    '<i class="fa fa-info-circle"></i> ' + nodeData.name + 
                    ' <small class="text-muted">(' + nodeType + ')</small>' +
                    '</div><div class="bom-detail-body">';
                
                if (nodeType === 'component') {
                    detailHtml += '<div class="material-properties">' +
                        '<div class="property-item">' +
                        '<div class="property-label">描述</div>' +
                        '<div class="property-value">' + (nodeData.description || '无') + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">数量</div>' +
                        '<div class="property-value">' + (nodeData.quantity_per_parent || 1) + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">长度公式</div>' +
                        '<div class="property-value">' + Controller.api.formatFormula(nodeData.length_formula) + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">宽度公式</div>' +
                        '<div class="property-value">' + Controller.api.formatFormula(nodeData.width_formula) + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">高度公式</div>' +
                        '<div class="property-value">' + Controller.api.formatFormula(nodeData.height_formula) + '</div>' +
                        '</div>' +
                        '</div>';
                } else if (nodeType === 'material') {
                    detailHtml += '<div class="material-properties">' +
                        '<div class="property-item">' +
                        '<div class="property-label">类型</div>' +
                        '<div class="property-value">' + (nodeData.type || '未设置') + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">单位成本</div>' +
                        '<div class="property-value">￥' + (nodeData.unit_cost || 0) + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">计量单位</div>' +
                        '<div class="property-value">' + (nodeData.unit_of_measure || '未设置') + '</div>' +
                        '</div>' +
                        '</div>';
                } else if (nodeType === 'process') {
                    detailHtml += '<div class="material-properties">' +
                        '<div class="property-item">' +
                        '<div class="property-label">成本类型</div>' +
                        '<div class="property-value">' + (nodeData.cost_type || '未设置') + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">费率</div>' +
                        '<div class="property-value">￥' + (nodeData.rate || 0) + '</div>' +
                        '</div>' +
                        '<div class="property-item">' +
                        '<div class="property-label">设置费</div>' +
                        '<div class="property-value">￥' + (nodeData.setup_cost || 0) + '</div>' +
                        '</div>' +
                        '</div>';
                }
                
                detailHtml += '</div></div>';
                $detailPanel.html(detailHtml);
            },
            
            // 初始化成本计算界面
            initCostCalculation: function() {
                var $calculateBtn = $("#calculate-btn");
                if ($calculateBtn.length === 0) return;
                
                $calculateBtn.on('click', function() {
                    var productId = Fast.api.query('ids');
                    if (!productId) {
                        Toastr.error('缺少产品ID参数');
                        return;
                    }
                    
                    var params = {
                        id: productId,
                        packaging_cost: parseFloat($("#packaging_cost").val()) || 0,
                        labor_cost: parseFloat($("#labor_cost").val()) || 0,
                        waste_rate: parseFloat($("#waste_rate").val()) || 0,
                        small_batch_cost: parseFloat($("#small_batch_cost").val()) || 0
                    };
                    
                    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 计算中...');
                    
                    Fast.api.ajax({
                        url: "bom/product/calculateCost",
                        data: params,
                        success: function(data) {
                            if (data.code === 1) {
                                Controller.api.renderCostResults(data.data);
                                Toastr.success('成本计算完成');
                            } else {
                                Toastr.error(data.msg || '计算失败');
                            }
                        },
                        complete: function() {
                            $calculateBtn.prop('disabled', false).html('<i class="fa fa-calculator"></i> 开始计算');
                        }
                    });
                });
            },
            
            // 渲染成本计算结果
            renderCostResults: function(costData) {
                var $resultsPanel = $("#cost-results");
                if ($resultsPanel.length === 0) return;
                
                var resultsHtml = '<div class="cost-summary-card">' +
                    '<div class="cost-summary-title"><i class="fa fa-calculator"></i> 成本计算结果</div>' +
                    '<div class="cost-breakdown">' +
                    '<div class="cost-item">' +
                    '<div class="cost-item-label">材料成本</div>' +
                    '<div class="cost-item-value">￥' + (costData.material_cost || 0).toFixed(2) + '</div>' +
                    '</div>' +
                    '<div class="cost-item">' +
                    '<div class="cost-item-label">工艺成本</div>' +
                    '<div class="cost-item-value">￥' + (costData.process_cost || 0).toFixed(2) + '</div>' +
                    '</div>' +
                    '<div class="cost-item">' +
                    '<div class="cost-item-label">包装费</div>' +
                    '<div class="cost-item-value">￥' + (costData.packaging_cost || 0).toFixed(2) + '</div>' +
                    '</div>' +
                    '<div class="cost-item">' +
                    '<div class="cost-item-label">人工费</div>' +
                    '<div class="cost-item-value">￥' + (costData.labor_cost || 0).toFixed(2) + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="total-cost">' +
                    '<div class="cost-item-label">总成本</div>' +
                    '<div class="total-cost-value">￥' + (costData.total_cost || 0).toFixed(2) + '</div>' +
                    '</div>' +
                    '</div>';
                
                // 如果有详细的成本明细，也显示出来
                if (costData.breakdown && costData.breakdown.length > 0) {
                    resultsHtml += '<div class="cost-breakdown-details">' +
                        '<h5><i class="fa fa-list"></i> 成本明细</h5>' +
                        '<div class="table-responsive">' +
                        '<table class="table table-striped table-condensed">' +
                        '<thead><tr><th>项目</th><th>用量</th><th>单价</th><th>小计</th></tr></thead>' +
                        '<tbody>';
                    
                    costData.breakdown.forEach(function(item) {
                        resultsHtml += '<tr>' +
                            '<td>' + item.name + '</td>' +
                            '<td>' + (item.quantity || 0) + ' ' + (item.unit || '') + '</td>' +
                            '<td>￥' + (item.unit_price || 0).toFixed(2) + '</td>' +
                            '<td>￥' + (item.subtotal || 0).toFixed(2) + '</td>' +
                            '</tr>';
                    });
                    
                    resultsHtml += '</tbody></table></div></div>';
                }
                
                $resultsPanel.html(resultsHtml);
            }
        }
    };
    
    return Controller;
});