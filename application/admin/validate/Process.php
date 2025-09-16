<?php

namespace app\admin\validate;

use think\Validate;

class Process extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|工艺名称' => 'require|length:1,100',
        'description|工艺描述' => 'length:0,500',
        'cost_type|成本类型' => 'require|in:per_unit_variable,per_area_variable,per_time_variable,fixed',
        'unit_of_cost|成本单位' => 'require|in:yuan_per_piece,yuan_per_sqm,yuan_per_hour,yuan_per_batch,yuan_per_kg',
        'rate|费率' => 'require|float|egt:0',
        'setup_cost|设置费' => 'float|egt:0',
        'throughput_rate|吞吐率' => 'float|gt:0',
        'waste_rate|损耗率' => 'float|between:0,100'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'name.require' => '工艺名称不能为空',
        'name.length' => '工艺名称长度必须在1-100个字符之间',
        'description.length' => '工艺描述不能超过500个字符',
        'cost_type.require' => '必须选择成本类型',
        'cost_type.in' => '成本类型值不正确',
        'unit_of_cost.require' => '必须选择成本单位',
        'unit_of_cost.in' => '成本单位值不正确',
        'rate.require' => '费率不能为空',
        'rate.float' => '费率必须是数字',
        'rate.egt' => '费率不能小于0',
        'setup_cost.float' => '设置费必须是数字',
        'setup_cost.egt' => '设置费不能小于0',
        'throughput_rate.float' => '吞吐率必须是数字',
        'throughput_rate.gt' => '吞吐率必须大于0',
        'waste_rate.float' => '损耗率必须是数字',
        'waste_rate.between' => '损耗率必须在0-100之间'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['name', 'description', 'cost_type', 'unit_of_cost', 'rate', 'setup_cost', 'throughput_rate', 'waste_rate'],
        'edit' => ['name', 'description', 'cost_type', 'unit_of_cost', 'rate', 'setup_cost', 'throughput_rate', 'waste_rate']
    ];
}