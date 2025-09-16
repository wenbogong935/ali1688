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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});