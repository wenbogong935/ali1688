-- BOM系统演示数据
-- 请先执行 bom_system_tables.sql 创建表结构

-- 插入演示产品
INSERT INTO `fa_products` (`name`, `description`, `length_formula`, `width_formula`, `height_formula`, `createtime`, `updatetime`) VALUES
('包装盒-A4尺寸', '标准A4尺寸的包装盒产品', '297', '210', '50', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('手机包装盒', '手机专用包装盒', '160', '80', '25', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入演示原材料
INSERT INTO `fa_raw_materials` (`name`, `type`, `unit_cost`, `unit_of_measure`, `std_length_cm`, `std_width_cm`, `std_grammage_gsm`, `thickness_mm`, `density_kgm3`, `notes`, `createtime`, `updatetime`) VALUES
('白卡纸300g', '纸类', 0.012000, '每平方米', 70.00, 100.00, 300.00, 0.3000, NULL, '高档白卡纸，适合印刷', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('瓦楞纸板3mm', '纸类', 0.008000, '每平方米', 120.00, 80.00, 450.00, 3.0000, NULL, '三层瓦楞纸板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('PET薄膜0.1mm', '塑料类', 0.025000, '每平方米', 100.00, 50.00, NULL, 0.1000, 1380.00, '透明PET薄膜', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('EVA泡棉5mm', '塑料类', 0.015000, '每平方米', 100.00, 50.00, NULL, 5.0000, 120.00, '缓冲泡棉材料', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入演示工艺
INSERT INTO `fa_processes` (`name`, `cost_type`, `unit_of_cost`, `cost_rate`, `setup_cost`, `throughput_rate`, `labor_rate_per_hour`, `waste_percentage`, `notes`, `createtime`, `updatetime`) VALUES
('四色印刷', '每面积可变', '元/平方米', 8.500000, 50.0000, NULL, 25.0000, 3.0000, '四色胶印工艺', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('覆膜', '每面积可变', '元/平方米', 2.800000, 20.0000, NULL, 20.0000, 2.0000, '表面覆膜处理', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('模切成型', '每单位可变', '元/件', 0.150000, 30.0000, 500.0000, 18.0000, 1.0000, '模切成型工艺', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('糊盒', '每单位可变', '元/件', 0.080000, 0.0000, 800.0000, 15.0000, 0.5000, '手工糊盒', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('质检包装', '每单位可变', '元/件', 0.050000, 0.0000, 1000.0000, 12.0000, 0.0000, '质量检查和包装', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的产品ID（假设ID为1和2）
-- 插入部件数据
INSERT INTO `fa_components` (`product_id`, `parent_component_id`, `name`, `quantity_per_parent`, `length_formula`, `width_formula`, `height_formula`, `sequence`, `createtime`, `updatetime`) VALUES
(1, NULL, '盒身', 1.0000, '=L', '=W', '=H*0.8', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, NULL, '盒盖', 1.0000, '=L+6', '=W+6', '=H*0.3', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, 1, '底板', 1.0000, '=L-4', '=W-4', '0', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, 1, '侧板', 4.0000, '=L-4', '=H*0.8', '0', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, NULL, '手机盒主体', 1.0000, '=L', '=W', '=H', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 5, '内托', 1.0000, '=L-4', '=W-4', '=H-5', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入材料使用数据（假设部件ID从1开始）
INSERT INTO `fa_material_usages` (`component_id`, `raw_material_id`, `alias`, `length_formula`, `width_formula`, `grammage_override`, `thickness_override_mm`, `imposition_quantity`, `sequence`, `createtime`, `updatetime`) VALUES
(1, 1, '盒身用纸', '=L', '=W', NULL, NULL, 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 1, '盒盖用纸', '=L', '=W', NULL, NULL, 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 2, '底板瓦楞纸', '=L', '=W', NULL, NULL, 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, 2, '侧板瓦楞纸', '=L', '=W', NULL, NULL, 8, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 1, '手机盒外层', '=L+20', '=W+20', NULL, NULL, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(6, 4, '内托泡棉', '=L', '=W', NULL, NULL, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入工艺分配数据
INSERT INTO `fa_process_assignments` (`component_id`, `material_usage_id`, `parent_assignment_id`, `process_id`, `alias`, `sequence`, `cost_override`, `is_affected_by_imposition`, `is_transformative`, `createtime`, `updatetime`) VALUES
(1, 1, NULL, 1, '盒身印刷', 1, NULL, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, 1, NULL, 2, '盒身覆膜', 2, NULL, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, NULL, NULL, 3, '盒身模切', 3, NULL, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 2, NULL, 1, '盒盖印刷', 1, NULL, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, NULL, NULL, 3, '盒盖模切', 2, NULL, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, NULL, NULL, 4, '糊盒工艺', 4, NULL, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, NULL, NULL, 5, '质检包装', 5, NULL, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 5, NULL, 1, '手机盒印刷', 1, NULL, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, NULL, NULL, 3, '手机盒模切', 2, NULL, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, NULL, NULL, 4, '手机盒糊盒', 3, NULL, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入菜单权限数据（可选，用于FastAdmin后台菜单）
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', 0, 'bom', 'BOM管理', 'fa fa-sitemap', '', 'BOM成本计算系统', 1, 100, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的BOM菜单ID
SET @bom_menu_id = LAST_INSERT_ID();

INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', @bom_menu_id, 'bom/product', '产品管理', 'fa fa-cube', '', '产品和BOM管理', 1, 95, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/rawmaterial', '原材料管理', 'fa fa-cubes', '', '原材料基础数据', 1, 90, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/process', '工艺管理', 'fa fa-cogs', '', '工艺基础数据', 1, 85, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/component', '部件管理', 'fa fa-puzzle-piece', '', '部件管理', 1, 80, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/materialusage', '材料使用', 'fa fa-tags', '', '材料使用管理', 1, 75, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/processassignment', '工艺分配', 'fa fa-tasks', '', '工艺分配管理', 1, 70, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());