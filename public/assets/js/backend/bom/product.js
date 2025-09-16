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

            // 初始化现代化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: 'ID', width: 60, align: 'center'},
                        {
                            field: 'image', 
                            title: '<i class="fa fa-image"></i> 产品图片', 
                            width: 100,
                            operate: false, 
                            events: Table.api.events.image, 
                            formatter: function(value, row, index) {
                                var template = $("#imagetpl").html();
                                return template ? template.replace(/\{\{\#\s*d\.image\s*\}\}/g, value || '') : '';
                            }
                        },
                        {
                            field: 'name', 
                            title: '<i class="fa fa-cube"></i> 产品名称', 
                            operate: 'LIKE',
                            formatter: function(value, row, index) {
                                return '<div style="font-weight: 600; color: #2c3e50;">' + value + '</div>' +
                                       '<small class="text-muted">' + (row.description || '无描述') + '</small>';
                            }
                        },
                        {
                            field: 'length_formula', 
                            title: '<i class="fa fa-arrows-h"></i> 长度公式', 
                            width: 120,
                            operate: 'LIKE', 
                            formatter: function(value, row, index) {
                                var template = $("#formulatpl").html();
                                return template ? template.replace(/\{\{\#\s*d\.FAT_FIELD_NAME\s*\}\}/g, value || '') : '';
                            }
                        },
                        {
                            field: 'width_formula', 
                            title: '<i class="fa fa-arrows-v"></i> 宽度公式', 
                            width: 120,
                            operate: 'LIKE', 
                            formatter: function(value, row, index) {
                                var template = $("#formulatpl").html();
                                return template ? template.replace(/\{\{\#\s*d\.FAT_FIELD_NAME\s*\}\}/g, value || '') : '';
                            }
                        },
                        {
                            field: 'height_formula', 
                            title: '<i class="fa fa-arrows"></i> 高度公式', 
                            width: 120,
                            operate: 'LIKE', 
                            formatter: function(value, row, index) {
                                var template = $("#formulatpl").html();
                                return template ? template.replace(/\{\{\#\s*d\.FAT_FIELD_NAME\s*\}\}/g, value || '') : '';
                            }
                        },
                        {
                            field: 'status',
                            title: '<i class="fa fa-toggle-on"></i> 状态',
                            width: 80,
                            searchList: {"active": "启用", "inactive": "禁用"},
                            formatter: function(value, row, index) {
                                var template = $("#statustpl").html();
                                return template ? template.replace(/\{\{\#\s*d\.status\s*\}\}/g, value || 'inactive') : '';
                            }
                        },
                        {
                            field: 'createtime', 
                            title: '<i class="fa fa-calendar"></i> 创建时间', 
                            width: 160,
                            operate:'RANGE', 
                            addclass:'datetimerange', 
                            autocomplete:false, 
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime', 
                            title: '<i class="fa fa-clock-o"></i> 更新时间', 
                            width: 160,
                            operate:'RANGE', 
                            addclass:'datetimerange', 
                            autocomplete:false, 
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate', 
                            title: '<i class="fa fa-cogs"></i> 操作', 
                            width: 180,
                            table: table, 
                            events: Table.api.events.operate, 
                            buttons: [
                                {
                                    name: 'preview',
                                    text: '<i class="fa fa-eye"></i>',
                                    title: '预览产品',
                                    classname: 'btn btn-xs btn-default',
                                    icon: 'fa fa-eye',
                                    click: function(options, row) {
                                        Controller.api.previewProduct(row.id);
                                        return false;
                                    }
                                },
                                {
                                    name: 'bom',
                                    text: '<i class="fa fa-sitemap"></i>',
                                    title: 'BOM管理',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-sitemap',
                                    url: 'bom/product/bom',
                                    extend: 'data-area=\'["95%","95%"]\' data-full=false',
                                    callback: function (data) {
                                        Toastr.success("BOM管理操作完成");
                                        table.bootstrapTable('refresh');
                                    }
                                },
                                {
                                    name: 'calculate', 
                                    text: '<i class="fa fa-calculator"></i>',
                                    title: '成本计算',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-calculator',
                                    url: 'bom/product/calculate',
                                    extend: 'data-area=\'["95%","95%"]\' data-full=false',
                                    callback: function (data) {
                                        Toastr.success("成本计算完成");
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                onLoadSuccess: function(data) {
                    // 更新统计数据
                    Controller.api.updateStatistics(data);
                    
                    // 初始化工具提示
                    $('[data-toggle="tooltip"]').tooltip();
                    
                    // 添加动画效果
                    $('.card-modern').addClass('animate-fade-in');
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            
            // 初始化现代化功能
            Controller.api.initModernFeatures();
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
            
            // 初始化现代化功能
            initModernFeatures: function() {
                // 优化按钮样式
                $('.btn-group .btn').each(function() {
                    $(this).addClass('transition-modern');
                });
                
                // 添加快捷键支持
                $(document).keydown(function(e) {
                    // Ctrl + N 添加产品
                    if (e.ctrlKey && e.keyCode === 78) {
                        e.preventDefault();
                        $('.btn-add').trigger('click');
                    }
                    // F5 刷新表格
                    if (e.keyCode === 116) {
                        e.preventDefault();
                        $('#table').bootstrapTable('refresh');
                    }
                });
                
                // 优化搜索框
                $('.fixed-table-toolbar .search input').attr('placeholder', '搜索产品名称、描述或公式...');
                
                // 显示使用提示
                setTimeout(function() {
                    if (!localStorage.getItem('hideProductTips')) {
                        Toastr.info('提示：双击行可快速编辑产品，Ctrl+N 可快速添加产品', '使用技巧', {
                            timeOut: 8000,
                            onclick: function() {
                                localStorage.setItem('hideProductTips', 'true');
                            }
                        });
                    }
                }, 3000);
            },
            
            // 更新统计数据
            updateStatistics: function(data) {
                var total = data.total || 0;
                var active = 0;
                var pending = 0;
                var calculated = 0;
                
                if (data.rows) {
                    data.rows.forEach(function(row) {
                        if (row.status === 'active') active++;
                        // 这里可以根据实际业务逻辑判断pending和calculated
                        if (!row.length_formula || !row.width_formula || !row.height_formula) {
                            pending++;
                        } else {
                            calculated++;
                        }
                    });
                }
                
                // 使用计数动画
                Controller.api.animateNumber($('#total-products'), total);
                Controller.api.animateNumber($('#active-products'), active);
                Controller.api.animateNumber($('#pending-products'), pending);
                Controller.api.animateNumber($('#calculated-products'), calculated);
            },
            
            // 数字动画
            animateNumber: function($element, targetNumber) {
                var currentNumber = parseInt($element.text()) || 0;
                var increment = Math.ceil((targetNumber - currentNumber) / 20);
                
                var timer = setInterval(function() {
                    currentNumber += increment;
                    if (currentNumber >= targetNumber) {
                        currentNumber = targetNumber;
                        clearInterval(timer);
                    }
                    $element.text(currentNumber);
                }, 50);
            },
            
            // 预览产品
            previewProduct: function(id) {
                $('#productPreviewContent').html('<div class="text-center"><div class="loading-modern"></div><p>加载中...</p></div>');
                $('#productPreviewModal').modal('show');
                
                $.ajax({
                    url: 'bom/product/preview',
                    type: 'GET',
                    data: {ids: id},
                    success: function(ret) {
                        if (ret.code === 1) {
                            $('#productPreviewContent').html(ret.data.html);
                        } else {
                            $('#productPreviewContent').html('<div class="alert alert-danger">' + ret.msg + '</div>');
                        }
                    },
                    error: function() {
                        $('#productPreviewContent').html('<div class="alert alert-danger">加载失败，请重试</div>');
                    }
                });
            }
        }
    };
    
    // 全局函数定义
    window.previewProduct = function(id) {
        Controller.api.previewProduct(id);
    };
    
    window.manageBom = function(id) {
        Fast.api.open('bom/product/bom/ids/' + id, 'BOM管理', {
            area: ['95%', '95%']
        });
    };
    
    window.calculateCost = function(id) {
        Fast.api.open('bom/product/calculate/ids/' + id, '成本计算', {
            area: ['95%', '95%']
        });
    };
    
    window.exportProducts = function() {
        var url = 'bom/product/export';
        var link = document.createElement('a');
        link.href = url;
        link.download = '产品列表.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        Toastr.success('正在导出产品列表...');
    };
    
    window.importProducts = function() {
        Fast.api.open('bom/product/import', '批量导入产品', {
            area: ['600px', '400px']
        });
    };
    
    window.editProductFromPreview = function() {
        $('#productPreviewModal').modal('hide');
        // 从模态框中获取产品ID并编辑
        // 这里需要根据实际情况实现
    };
    
    window.manageBomFromPreview = function() {
        $('#productPreviewModal').modal('hide');
        // 从模态框中获取产品ID并管理BOM
        // 这里需要根据实际情况实现
    };
    
    return Controller;
});