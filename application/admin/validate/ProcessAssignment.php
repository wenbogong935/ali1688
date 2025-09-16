<?php

namespace app\admin\validate;

use think\Validate;

class ProcessAssignment extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'assignment_type|分配类型' => 'require|in:component,material',
        'component_id|部件ID' => 'requireIf:assignment_type,component|integer|gt:0',
        'material_usage_id|材料使用ID' => 'requireIf:assignment_type,material|integer|gt:0',
        'process_id|工艺ID' => 'require|integer|gt:0',
        'process_order|工艺顺序' => 'require|integer|egt:1',
        'rate_override|费率覆盖' => 'float|egt:0',
        'setup_cost_override|设置费覆盖' => 'float|egt:0',
        'panel_affects_cost|拼版影响' => 'in:0,1',
        'transform_attribute|转换属性' => 'length:0,100'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'assignment_type.require' => '必须选择分配类型',
        'assignment_type.in' => '分配类型值不正确',
        'component_id.requireIf' => '选择部件工艺时必须选择部件',
        'component_id.integer' => '部件ID必须是整数',
        'component_id.gt' => '部件ID必须大于0',
        'material_usage_id.requireIf' => '选择材料工艺时必须选择材料使用',
        'material_usage_id.integer' => '材料使用ID必须是整数',
        'material_usage_id.gt' => '材料使用ID必须大于0',
        'process_id.require' => '必须选择工艺',
        'process_id.integer' => '工艺ID必须是整数',
        'process_id.gt' => '工艺ID必须大于0',
        'process_order.require' => '工艺顺序不能为空',
        'process_order.integer' => '工艺顺序必须是整数',
        'process_order.egt' => '工艺顺序不能小于1',
        'rate_override.float' => '费率覆盖必须是数字',
        'rate_override.egt' => '费率覆盖不能小于0',
        'setup_cost_override.float' => '设置费覆盖必须是数字',
        'setup_cost_override.egt' => '设置费覆盖不能小于0',
        'panel_affects_cost.in' => '拼版影响值不正确',
        'transform_attribute.length' => '转换属性不能超过100个字符'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['assignment_type', 'component_id', 'material_usage_id', 'process_id', 'process_order', 'rate_override', 'setup_cost_override', 'panel_affects_cost', 'transform_attribute'],
        'edit' => ['assignment_type', 'component_id', 'material_usage_id', 'process_id', 'process_order', 'rate_override', 'setup_cost_override', 'panel_affects_cost', 'transform_attribute']
    ];
}