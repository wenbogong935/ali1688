<?php

namespace app\common\library\costing;

/**
 * 规格计算服务
 * 负责解析BOM树中所有节点的物理规格属性
 */
class SpecificationService
{
    private $parser;

    public function __construct()
    {
        $this->parser = new FormulaParser();
    }

    /**
     * 计算整个BOM树的规格属性
     * @param object $product 产品模型对象 (已通过with预加载整个BOM树)
     * @return object 注解了规格属性的产品对象
     */
    public function calculate($product)
    {
        // 顶层产品没有父节点，其尺寸是BOM树的起点
        $emptyContext = [];
        $product->resolved_l = $this->parser->evaluate($product->length_formula ?? '0', $emptyContext);
        $product->resolved_w = $this->parser->evaluate($product->width_formula ?? '0', $emptyContext);
        $product->resolved_h = $this->parser->evaluate($product->height_formula ?? '0', $emptyContext);

        // 将产品自身作为顶层父节点，将其解析后的尺寸传递给第一级部件
        if (isset($product->components)) {
            foreach ($product->components as $component) {
                $this->resolveComponentSpecs($component, $product);
            }
        }

        return $product;
    }

    /**
     * 递归解析部件及其子项的规格
     * @param object $component 部件对象
     * @param object $parent 父节点对象（产品或部件）
     */
    private function resolveComponentSpecs($component, $parent)
    {
        $context = [
            'L' => $parent->resolved_l ?? 0,
            'W' => $parent->resolved_w ?? 0,
            'H' => $parent->resolved_h ?? 0,
        ];

        // 解析当前部件的尺寸
        $component->resolved_l = $this->parser->evaluate($component->length_formula ?? '0', $context);
        $component->resolved_w = $this->parser->evaluate($component->width_formula ?? '0', $context);
        $component->resolved_h = $this->parser->evaluate($component->height_formula ?? '0', $context);

        // 计算实际数量（考虑相对父项的使用数量）
        $component->resolved_quantity = $component->quantity_per_parent ?? 1;

        // 解析此部件下的材料使用规格
        if (isset($component->materialUsages)) {
            foreach ($component->materialUsages as $materialUsage) {
                $this->resolveMaterialSpecs($materialUsage, $component);
            }
        }

        // 解析此部件下的工艺分配
        if (isset($component->processAssignments)) {
            foreach ($component->processAssignments as $processAssignment) {
                $this->resolveProcessSpecs($processAssignment, $component);
            }
        }

        // 递归解析子部件
        if (isset($component->children)) {
            foreach ($component->children as $childComponent) {
                $this->resolveComponentSpecs($childComponent, $component);
            }
        }
    }

    /**
     * 解析材料使用规格
     * @param object $materialUsage 材料使用对象
     * @param object $parentComponent 父部件对象
     */
    private function resolveMaterialSpecs($materialUsage, $parentComponent)
    {
        $context = [
            'L' => $parentComponent->resolved_l ?? 0,
            'W' => $parentComponent->resolved_w ?? 0,
            'H' => $parentComponent->resolved_h ?? 0,
        ];

        // 解析材料使用的尺寸
        $materialUsage->resolved_l = $this->parser->evaluate($materialUsage->length_formula ?? '0', $context);
        $materialUsage->resolved_w = $this->parser->evaluate($materialUsage->width_formula ?? '0', $context);

        // 计算面积和体积
        $materialUsage->resolved_area = $materialUsage->resolved_l * $materialUsage->resolved_w / 10000; // 转换为平方米
        $materialUsage->resolved_volume = $materialUsage->resolved_l * $materialUsage->resolved_w * 
            ($materialUsage->thickness_override_mm ?? $materialUsage->rawMaterial->thickness_mm ?? 0) / 1000000; // 转换为立方米

        // 计算重量（基于克重或密度）
        if ((isset($materialUsage->grammage_override) && $materialUsage->grammage_override) || (isset($materialUsage->rawMaterial) && $materialUsage->rawMaterial->std_grammage_gsm)) {
            $grammage = $materialUsage->grammage_override ?? $materialUsage->rawMaterial->std_grammage_gsm;
            $materialUsage->resolved_weight = $materialUsage->resolved_area * $grammage / 1000; // 转换为公斤
        } elseif (isset($materialUsage->rawMaterial) && $materialUsage->rawMaterial->density_kgm3) {
            $materialUsage->resolved_weight = $materialUsage->resolved_volume * $materialUsage->rawMaterial->density_kgm3;
        } else {
            $materialUsage->resolved_weight = 0;
        }
    }

    /**
     * 解析工艺分配规格
     * @param object $processAssignment 工艺分配对象
     * @param object $parentComponent 父部件对象
     */
    private function resolveProcessSpecs($processAssignment, $parentComponent)
    {
        $context = [
            'L' => $parentComponent->resolved_l ?? 0,
            'W' => $parentComponent->resolved_w ?? 0,
            'H' => $parentComponent->resolved_h ?? 0,
        ];

        // 如果工艺是转换性的，计算输出规格
        if ($processAssignment->is_transformative) {
            $processAssignment->resolved_output_l = $this->parser->evaluate(
                $processAssignment->output_length_formula ?? '0', $context
            );
            $processAssignment->resolved_output_w = $this->parser->evaluate(
                $processAssignment->output_width_formula ?? '0', $context
            );
            $processAssignment->resolved_output_h = $this->parser->evaluate(
                $processAssignment->output_height_formula ?? '0', $context
            );
            $processAssignment->resolved_output_quantity = $this->parser->evaluate(
                $processAssignment->output_quantity_formula ?? '1', $context
            );
        }

        // 计算工艺处理的面积（用于按面积计费的工艺）
        $processAssignment->resolved_area = ($parentComponent->resolved_l ?? 0) * 
            ($parentComponent->resolved_w ?? 0) / 10000; // 转换为平方米
    }
}