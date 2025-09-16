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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'length_formula', title: '长度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'width_formula', title: '宽度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'height_formula', title: '高度公式', operate: 'LIKE', formatter: function(value, row, index) {
                            return value || '<span class="text-muted">未设置</span>';
                        }},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, 
                         buttons: [
                             {
                                 name: 'bom',
                                 text: 'BOM管理',
                                 title: 'BOM管理',
                                 classname: 'btn btn-xs btn-success btn-dialog',
                                 icon: 'fa fa-sitemap',
                                 url: 'bom/product/bom',
                                 callback: function (data) {
                                     Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                 }
                             },
                             {
                                 name: 'calculate',
                                 text: '成本计算',
                                 title: '成本计算',
                                 classname: 'btn btn-xs btn-info btn-dialog',
                                 icon: 'fa fa-calculator',
                                 url: 'bom/product/calculate',
                                 callback: function (data) {
                                     Layer.alert("计算完成", {title: "成本计算"});
                                 }
                             }
                         ],
                         formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            
            // BOM管理按钮事件
            $(document).on('click', '.btn-bom', function () {
                var ids = Table.api.selectedids(table);
                if (ids.length === 0) {
                    Toastr.error("请先选择一个产品");
                    return false;
                }
                if (ids.length > 1) {
                    Toastr.error("只能选择一个产品进行BOM管理");
                    return false;
                }
                var url = 'bom/product/bom/ids/' + ids.join(',');
                Fast.api.open(url, 'BOM管理', {
                    area: ['90%', '90%']
                });
                return false;
            });

            // 成本计算按钮事件
            $(document).on('click', '.btn-calculate', function () {
                var ids = Table.api.selectedids(table);
                if (ids.length === 0) {
                    Toastr.error("请先选择一个产品");
                    return false;
                }
                if (ids.length > 1) {
                    Toastr.error("只能选择一个产品进行成本计算");
                    return false;
                }
                var url = 'bom/product/calculate/ids/' + ids.join(',');
                Fast.api.open(url, '成本计算', {
                    area: ['90%', '90%']
                });
                return false;
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        bom: function () {
            Controller.api.bindevent();
            
            // 初始化BOM树
            Controller.api.initBomTree();
            
            // 绑定树节点事件
            Controller.api.bindTreeEvents();
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
            
            // 初始化BOM树
            initBomTree: function() {
                var $bomTree = $("#bom-tree");
                if ($bomTree.length === 0) return;
                
                // 加载BOM树数据
                Fast.api.ajax({
                    url: "bom/product/getBomTree",
                    data: {id: Fast.api.query('ids')},
                    success: function(data) {
                        if (data.code === 1) {
                            Controller.api.renderBomTree(data.data);
                        }
                    }
                });
            },
            
            // 渲染BOM树
            renderBomTree: function(treeData) {
                var $container = $("#bom-tree");
                $container.empty();
                
                function renderNode(node, level) {
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
                    
                    var nodeHtml = '<div class="' + nodeClass + '" style="margin-left: ' + indent + 'px;" data-id="' + node.id + '" data-type="' + node.type + '">' +
                        '<i class="fa ' + nodeIcon + ' node-icon ' + node.type + '"></i>' +
                        '<span class="node-title">' + node.name + '</span>' +
                        '<span class="node-info text-muted">' + (node.description || '') + '</span>' +
                        '</div>';
                    
                    $container.append(nodeHtml);
                    
                    if (node.children && node.children.length > 0) {
                        node.children.forEach(function(child) {
                            renderNode(child, level + 1);
                        });
                    }
                }
                
                if (treeData.components) {
                    treeData.components.forEach(function(component) {
                        renderNode(component, 0);
                    });
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
            
            // 加载节点详情
            loadNodeDetails: function(nodeId, nodeType) {
                var $detailPanel = $("#bom-detail");
                if ($detailPanel.length === 0) return;
                
                var url = "bom/" + nodeType + "/detail";
                Fast.api.ajax({
                    url: url,
                    data: {id: nodeId},
                    success: function(data) {
                        if (data.code === 1) {
                            Controller.api.renderNodeDetails(data.data, nodeType);
                        }
                    }
                });
            },
            
            // 渲染节点详情
            renderNodeDetails: function(nodeData, nodeType) {
                var $detailPanel = $("#bom-detail");
                var detailHtml = '<div class="bom-detail-panel">' +
                    '<div class="bom-detail-header">' + nodeData.name + ' (' + nodeType + ')</div>' +
                    '<div class="bom-detail-body">';
                
                if (nodeType === 'component') {
                    detailHtml += '<p><strong>描述:</strong> ' + (nodeData.description || '无') + '</p>' +
                        '<p><strong>数量:</strong> ' + (nodeData.quantity || 1) + '</p>' +
                        '<p><strong>长度公式:</strong> ' + (nodeData.length_formula || '未设置') + '</p>' +
                        '<p><strong>宽度公式:</strong> ' + (nodeData.width_formula || '未设置') + '</p>' +
                        '<p><strong>高度公式:</strong> ' + (nodeData.height_formula || '未设置') + '</p>';
                } else if (nodeType === 'material') {
                    detailHtml += '<p><strong>类型:</strong> ' + (nodeData.type || '未设置') + '</p>' +
                        '<p><strong>单位成本:</strong> ￥' + (nodeData.unit_cost || 0) + '</p>' +
                        '<p><strong>计量单位:</strong> ' + (nodeData.unit_of_measure || '未设置') + '</p>';
                } else if (nodeType === 'process') {
                    detailHtml += '<p><strong>成本类型:</strong> ' + (nodeData.cost_type || '未设置') + '</p>' +
                        '<p><strong>费率:</strong> ￥' + (nodeData.rate || 0) + '</p>' +
                        '<p><strong>设置费:</strong> ￥' + (nodeData.setup_cost || 0) + '</p>';
                }
                
                detailHtml += '</div></div>';
                $detailPanel.html(detailHtml);
            },
            
            // 初始化成本计算界面
            initCostCalculation: function() {
                var $calculateBtn = $("#calculate-btn");
                
                $calculateBtn.on('click', function() {
                    var productId = Fast.api.query('ids');
                    var params = {
                        id: productId,
                        packaging_cost: parseFloat($("#packaging_cost").val()) || 0,
                        labor_cost: parseFloat($("#labor_cost").val()) || 0,
                        waste_rate: parseFloat($("#waste_rate").val()) || 0,
                        small_batch_cost: parseFloat($("#small_batch_cost").val()) || 0
                    };
                    
                    Fast.api.ajax({
                        url: "bom/product/calculateCost",
                        data: params,
                        success: function(data) {
                            if (data.code === 1) {
                                Controller.api.renderCostResults(data.data);
                            }
                        }
                    });
                });
            },
            
            // 渲染成本计算结果
            renderCostResults: function(costData) {
                var $resultsPanel = $("#cost-results");
                if ($resultsPanel.length === 0) return;
                
                var resultsHtml = '<div class="cost-summary-card">' +
                    '<div class="cost-summary-title">成本计算结果</div>' +
                    '<div class="cost-breakdown">' +
                    '<div class="cost-item"><div class="cost-item-label">材料成本</div><div class="cost-item-value">￥' + costData.material_cost.toFixed(2) + '</div></div>' +
                    '<div class="cost-item"><div class="cost-item-label">工艺成本</div><div class="cost-item-value">￥' + costData.process_cost.toFixed(2) + '</div></div>' +
                    '<div class="cost-item"><div class="cost-item-label">包装费</div><div class="cost-item-value">￥' + costData.packaging_cost.toFixed(2) + '</div></div>' +
                    '<div class="cost-item"><div class="cost-item-label">人工费</div><div class="cost-item-value">￥' + costData.labor_cost.toFixed(2) + '</div></div>' +
                    '</div>' +
                    '<div class="total-cost">' +
                    '<div class="cost-item-label">总成本</div>' +
                    '<div class="total-cost-value">￥' + costData.total_cost.toFixed(2) + '</div>' +
                    '</div>' +
                    '</div>';
                
                $resultsPanel.html(resultsHtml);
            }
        }
    };
    return Controller;
});