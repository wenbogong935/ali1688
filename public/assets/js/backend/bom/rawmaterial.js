define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数
            Table.api.init({
                extend: {
                    index_url: 'bom/rawmaterial/index' + location.search,
                    add_url: 'bom/rawmaterial/add',
                    edit_url: 'bom/rawmaterial/edit',
                    del_url: 'bom/rawmaterial/del',
                    multi_url: 'bom/rawmaterial/multi',
                    import_url: 'bom/rawmaterial/import',
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
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: '原材料名称', operate: 'LIKE'},
                        {field: 'type', title: '材料类型', searchList: {"paper":"纸类","plastic":"塑料类","metal":"金属类","wood":"木材类","fabric":"织物类","other":"其他"}, formatter: Table.api.formatter.normal},
                        {field: 'unit_cost', title: '单位成本', operate: 'BETWEEN'},
                        {field: 'unit_of_measure', title: '计量单位', searchList: {"sqm":"平方米","kg":"千克","meter":"米","piece":"件","roll":"卷"}, formatter: Table.api.formatter.normal},
                        {field: 'std_length_cm', title: '标准长度(cm)', operate: 'BETWEEN'},
                        {field: 'std_width_cm', title: '标准宽度(cm)', operate: 'BETWEEN'},
                        {field: 'std_grammage_gsm', title: '标准克重(g/m²)', operate: 'BETWEEN'},
                        {field: 'thickness_mm', title: '厚度(mm)', operate: 'BETWEEN'},
                        {field: 'density_kgm3', title: '密度(kg/m³)', operate: 'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, 
                         buttons: [
                             {
                                 name: 'import',
                                 text: '批量导入',
                                 title: '批量导入',
                                 classname: 'btn btn-xs btn-success btn-dialog',
                                 icon: 'fa fa-upload',
                                 url: 'bom/rawmaterial/import'
                             },
                             {
                                 name: 'exportTemplate',
                                 text: '导出模板',
                                 title: '导出模板',
                                 classname: 'btn btn-xs btn-info',
                                 icon: 'fa fa-download',
                                 url: 'bom/rawmaterial/exportTemplate'
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
        },
        edit: function () {
            Controller.api.bindevent();
        },
        import: function () {
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