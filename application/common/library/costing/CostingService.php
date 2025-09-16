<?php

namespace app\common\library\costing;

/**
 * 成本计算服务
 * 负责计算BOM树中所有节点的成本并汇总
 */
class CostingService
{
    /**
     * 计算产品总成本
     * @param object $product 已解析规格的产品对象
     * @param array $configParams 动态配置参数
     * @return array 成本摘要
     */
    public function calculate($product, array $configParams = [])
    {
        // 初始化成本汇总
        $costSummary = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'material_cost' => 0,
            'process_cost' => 0,
            'total_bom_cost' => 0,
            'packaging_cost' => $configParams['packaging_cost'] ?? 0,
            'labor_cost' => $configParams['labor_cost'] ?? 0,
            'waste_rate' => $configParams['waste_rate'] ?? 0,
            'small_batch_cost' => $configParams['small_batch_cost'] ?? 0,
            'total_cost' => 0,
            'cost_breakdown' => []
        ];

        // 计算顶层部件成本
        if (isset($product->components)) {
            foreach ($product->components as $component) {
                $componentCost = $this->calculateComponentCost($component);
                $costSummary['material_cost'] += $componentCost['material_cost'];
                $costSummary['process_cost'] += $componentCost['process_cost'];
                $costSummary['cost_breakdown'][] = $componentCost;
            }
        }

        // 计算BOM总成本
        $costSummary['total_bom_cost'] = $costSummary['material_cost'] + $costSummary['process_cost'];

        // 应用损耗率
        $wasteAmount = $costSummary['total_bom_cost'] * ($costSummary['waste_rate'] / 100);
        $costSummary['waste_amount'] = $wasteAmount;

        // 计算最终总成本
        $costSummary['total_cost'] = $costSummary['total_bom_cost'] + 
                                   $wasteAmount +
                                   $costSummary['packaging_cost'] + 
                                   $costSummary['labor_cost'] + 
                                   $costSummary['small_batch_cost'];

        return $costSummary;
    }

    /**
     * 递归计算部件成本
     * @param object $component 部件对象
     * @return array 部件成本详情
     */
    private function calculateComponentCost($component)
    {
        $componentCost = [
            'component_id' => $component->id,
            'component_name' => $component->name,
            'quantity' => $component->resolved_quantity ?? 1,
            'material_cost' => 0,
            'process_cost' => 0,
            'total_cost' => 0,
            'children' => []
        ];

        // 计算材料成本
        if (isset($component->materialUsages)) {
            foreach ($component->materialUsages as $materialUsage) {
                $materialCost = $this->calculateMaterialCost($materialUsage);
                $componentCost['material_cost'] += $materialCost['total_cost'];
            }
        }

        // 计算工艺成本
        if (isset($component->processAssignments)) {
            foreach ($component->processAssignments as $processAssignment) {
                $processCost = $this->calculateProcessCost($processAssignment, $component);
                $componentCost['process_cost'] += $processCost['total_cost'];
            }
        }

        // 递归计算子部件成本
        if (isset($component->children)) {
            foreach ($component->children as $childComponent) {
                $childCost = $this->calculateComponentCost($childComponent);
                $componentCost['children'][] = $childCost;
                // 子部件成本乘以数量后加入总成本
                $componentCost['material_cost'] += $childCost['material_cost'] * $childCost['quantity'];
                $componentCost['process_cost'] += $childCost['process_cost'] * $childCost['quantity'];
            }
        }

        // 计算部件总成本
        $componentCost['total_cost'] = $componentCost['material_cost'] + $componentCost['process_cost'];

        return $componentCost;
    }

    /**
     * 计算材料成本
     * @param object $materialUsage 材料使用对象
     * @return array 材料成本详情
     */
    private function calculateMaterialCost($materialUsage)
    {
        $materialCost = [
            'material_usage_id' => $materialUsage->id,
            'material_name' => $materialUsage->rawMaterial->name ?? 'Unknown',
            'unit_cost' => $materialUsage->rawMaterial->unit_cost ?? 0,
            'unit_of_measure' => $materialUsage->rawMaterial->unit_of_measure ?? '',
            'usage_amount' => 0,
            'total_cost' => 0,
            'imposition_quantity' => $materialUsage->imposition_quantity ?? 1
        ];

        $unitCost = $materialUsage->rawMaterial->unit_cost ?? 0;
        $unitOfMeasure = $materialUsage->rawMaterial->unit_of_measure ?? '';

        // 根据计量单位计算使用量和成本
        switch (strtolower($unitOfMeasure)) {
            case '每公斤':
            case 'per_kg':
                $materialCost['usage_amount'] = $materialUsage->resolved_weight ?? 0;
                break;
            case '每平方米':
            case 'per_sqm':
                $materialCost['usage_amount'] = $materialUsage->resolved_area ?? 0;
                break;
            case '每立方米':
            case 'per_cbm':
                $materialCost['usage_amount'] = $materialUsage->resolved_volume ?? 0;
                break;
            case '每件':
            case 'per_piece':
                $materialCost['usage_amount'] = 1;
                break;
            default:
                $materialCost['usage_amount'] = $materialUsage->resolved_area ?? 0;
        }

        // 计算总成本，考虑拼版数
        $materialCost['total_cost'] = ($materialCost['usage_amount'] * $unitCost) / 
                                    max(1, $materialCost['imposition_quantity']);

        return $materialCost;
    }

    /**
     * 计算工艺成本
     * @param object $processAssignment 工艺分配对象
     * @param object $component 父部件对象
     * @return array 工艺成本详情
     */
    private function calculateProcessCost($processAssignment, $component)
    {
        $processCost = [
            'process_assignment_id' => $processAssignment->id,
            'process_name' => $processAssignment->process->name ?? 'Unknown',
            'cost_type' => $processAssignment->process->cost_type ?? '',
            'cost_rate' => $processAssignment->cost_override ?? $processAssignment->process->cost_rate ?? 0,
            'setup_cost' => $processAssignment->process->setup_cost ?? 0,
            'usage_amount' => 0,
            'variable_cost' => 0,
            'total_cost' => 0
        ];

        $costRate = $processCost['cost_rate'];
        $costType = $processCost['cost_type'];

        // 根据成本类型计算
        switch (strtolower($costType)) {
            case '每单位可变':
            case 'per_unit':
                $processCost['usage_amount'] = $component->resolved_quantity ?? 1;
                $processCost['variable_cost'] = $processCost['usage_amount'] * $costRate;
                break;
            case '每面积可变':
            case 'per_area':
                $processCost['usage_amount'] = $processAssignment->resolved_area ?? 0;
                $processCost['variable_cost'] = $processCost['usage_amount'] * $costRate;
                break;
            case '每时间可变':
            case 'per_time':
                // 基于吞吐率计算时间
                $throughputRate = $processAssignment->process->throughput_rate ?? 1;
                $quantity = $component->resolved_quantity ?? 1;
                $timeRequired = $quantity / max(1, $throughputRate);
                $processCost['usage_amount'] = $timeRequired;
                $processCost['variable_cost'] = $timeRequired * $costRate;
                break;
            case '固定':
            case 'fixed':
                $processCost['usage_amount'] = 1;
                $processCost['variable_cost'] = $costRate;
                break;
            default:
                $processCost['variable_cost'] = 0;
        }

        // 考虑拼版数影响（如果工艺受拼版数影响）
        if ($processAssignment->is_affected_by_imposition && 
            isset($processAssignment->materialUsage) && 
            $processAssignment->materialUsage->imposition_quantity > 1) {
            $processCost['variable_cost'] /= $processAssignment->materialUsage->imposition_quantity;
        }

        // 总成本 = 可变成本 + 设置成本
        $processCost['total_cost'] = $processCost['variable_cost'] + $processCost['setup_cost'];

        return $processCost;
    }
}