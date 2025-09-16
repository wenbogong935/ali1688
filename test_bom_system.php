<?php
/**
 * BOM系统功能测试脚本
 * 用于验证核心计算逻辑是否正常工作
 */

// 简单的自动加载函数
spl_autoload_register(function ($class) {
    $prefix = 'app\\common\\library\\costing\\';
    $base_dir = __DIR__ . '/application/common/library/costing/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use app\common\library\costing\FormulaParser;
use app\common\library\costing\SpecificationService;
use app\common\library\costing\CostingService;

echo "BOM系统核心功能测试\n";
echo "==================\n\n";

// 测试公式解析器
echo "1. 测试公式解析器\n";
echo "-----------------\n";

$parser = new FormulaParser();

// 测试用例
$testCases = [
    ['100', []],
    ['=50', []],
    ['=L*0.8', ['L' => 100]],
    ['=L+W', ['L' => 100, 'W' => 50]],
    ['=(L+W)*0.5', ['L' => 100, 'W' => 50]],
    ['=L*W/10000', ['L' => 297, 'W' => 210]]
];

foreach ($testCases as $i => $case) {
    $formula = $case[0];
    $context = $case[1];
    $result = $parser->evaluate($formula, $context);
    
    echo "测试 " . ($i + 1) . ": 公式='$formula'";
    if (!empty($context)) {
        $contextStr = json_encode($context);
        echo ", 上下文=$contextStr";
    }
    echo " => 结果=$result\n";
}

echo "\n";

// 测试规格计算服务
echo "2. 测试规格计算服务\n";
echo "------------------\n";

// 模拟产品数据结构
$mockProduct = (object)[
    'id' => 1,
    'name' => '测试包装盒',
    'length_formula' => '297',
    'width_formula' => '210', 
    'height_formula' => '50',
    'components' => [
        (object)[
            'id' => 1,
            'name' => '盒身',
            'quantity_per_parent' => 1,
            'length_formula' => '=L',
            'width_formula' => '=W',
            'height_formula' => '=H*0.8',
            'materialUsages' => [
                (object)[
                    'id' => 1,
                    'length_formula' => '=L+20',
                    'width_formula' => '=W+20',
                    'rawMaterial' => (object)[
                        'thickness_mm' => 0.3,
                        'std_grammage_gsm' => 300
                    ]
                ]
            ],
            'processAssignments' => [],
            'children' => []
        ]
    ]
];

$specService = new SpecificationService();
$calculatedProduct = $specService->calculate($mockProduct);

echo "产品解析结果:\n";
echo "- 长度: {$calculatedProduct->resolved_l}cm\n";
echo "- 宽度: {$calculatedProduct->resolved_w}cm\n";
echo "- 高度: {$calculatedProduct->resolved_h}cm\n";

echo "\n部件解析结果:\n";
foreach ($calculatedProduct->components as $component) {
    echo "- {$component->name}: L={$component->resolved_l}, W={$component->resolved_w}, H={$component->resolved_h}\n";
    
    foreach ($component->materialUsages as $material) {
        echo "  材料: L={$material->resolved_l}, W={$material->resolved_w}, 面积={$material->resolved_area}m²\n";
    }
}

echo "\n";

// 测试成本计算服务（简化版本）
echo "3. 测试成本计算逻辑\n";
echo "------------------\n";

// 为了测试，我们需要为材料添加成本信息
$calculatedProduct->components[0]->materialUsages[0]->rawMaterial->unit_cost = 0.012;
$calculatedProduct->components[0]->materialUsages[0]->rawMaterial->unit_of_measure = '每平方米';
$calculatedProduct->components[0]->materialUsages[0]->imposition_quantity = 2;

$costService = new CostingService();
$configParams = [
    'packaging_cost' => 5.0,
    'labor_cost' => 10.0,
    'waste_rate' => 5.0,
    'small_batch_cost' => 2.0
];

$costSummary = $costService->calculate($calculatedProduct, $configParams);

echo "成本计算结果:\n";
echo "- 材料成本: ¥" . number_format($costSummary['material_cost'], 2) . "\n";
echo "- 工艺成本: ¥" . number_format($costSummary['process_cost'], 2) . "\n";
echo "- BOM总成本: ¥" . number_format($costSummary['total_bom_cost'], 2) . "\n";
echo "- 损耗金额: ¥" . number_format($costSummary['waste_amount'], 2) . "\n";
echo "- 包装费用: ¥" . number_format($costSummary['packaging_cost'], 2) . "\n";
echo "- 人工费用: ¥" . number_format($costSummary['labor_cost'], 2) . "\n";
echo "- 最终总成本: ¥" . number_format($costSummary['total_cost'], 2) . "\n";

echo "\n测试完成！\n";
?>