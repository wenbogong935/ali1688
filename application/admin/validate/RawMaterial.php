<?php

namespace app\admin\validate;

use think\Validate;

class RawMaterial extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|材料名称' => 'require|length:1,100',
        'description|材料描述' => 'length:0,500',
        'type|材料类型' => 'require|in:paper,plastic,metal,wood,glass,ceramic,textile,chemical,other',
        'unit_of_measure|计量单位' => 'require|in:per_kg,per_sqm,per_cbm,per_piece,per_meter,per_liter,per_roll,per_sheet',
        'unit_cost|单位成本' => 'require|float|egt:0',
        'standard_length|标准长度' => 'float|egt:0',
        'standard_width|标准宽度' => 'float|egt:0',
        'standard_height|标准高度' => 'float|egt:0',
        'gsm|克重' => 'float|egt:0',
        'thickness|厚度' => 'float|egt:0',
        'density|密度' => 'float|egt:0',
        'supplier|供应商' => 'length:0,100',
        'notes|备注' => 'length:0,1000'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'name.require' => '材料名称不能为空',
        'name.length' => '材料名称长度必须在1-100个字符之间',
        'description.length' => '材料描述不能超过500个字符',
        'type.require' => '必须选择材料类型',
        'type.in' => '材料类型值不正确',
        'unit_of_measure.require' => '必须选择计量单位',
        'unit_of_measure.in' => '计量单位值不正确',
        'unit_cost.require' => '单位成本不能为空',
        'unit_cost.float' => '单位成本必须是数字',
        'unit_cost.egt' => '单位成本不能小于0',
        'standard_length.float' => '标准长度必须是数字',
        'standard_length.egt' => '标准长度不能小于0',
        'standard_width.float' => '标准宽度必须是数字',
        'standard_width.egt' => '标准宽度不能小于0',
        'standard_height.float' => '标准高度必须是数字',
        'standard_height.egt' => '标准高度不能小于0',
        'gsm.float' => '克重必须是数字',
        'gsm.egt' => '克重不能小于0',
        'thickness.float' => '厚度必须是数字',
        'thickness.egt' => '厚度不能小于0',
        'density.float' => '密度必须是数字',
        'density.egt' => '密度不能小于0',
        'supplier.length' => '供应商名称不能超过100个字符',
        'notes.length' => '备注不能超过1000个字符'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['name', 'description', 'type', 'unit_of_measure', 'unit_cost', 'standard_length', 'standard_width', 'standard_height', 'gsm', 'thickness', 'density', 'supplier', 'notes'],
        'edit' => ['name', 'description', 'type', 'unit_of_measure', 'unit_cost', 'standard_length', 'standard_width', 'standard_height', 'gsm', 'thickness', 'density', 'supplier', 'notes']
    ];
}