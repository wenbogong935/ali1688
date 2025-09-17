-- BOM成本计算系统数据表结构
-- 适用于FastAdmin框架

-- ----------------------------
-- 1. 产品表 (products)
-- ----------------------------
CREATE TABLE `fa_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '产品名称',
  `description` text COMMENT '产品描述',
  `image` varchar(255) DEFAULT NULL COMMENT '产品图片路径',
  `length_formula` varchar(255) DEFAULT NULL COMMENT '长度(cm)，可以是数字或公式',
  `width_formula` varchar(255) DEFAULT NULL COMMENT '宽度(cm)，可以是数字或公式',
  `height_formula` varchar(255) DEFAULT NULL COMMENT '高度(cm)，可以是数字或公式',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `createtime` int(10) DEFAULT NULL,
  `updatetime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品表';

-- ----------------------------
-- 2. 部件表 (components)
-- ----------------------------
CREATE TABLE `fa_components` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL COMMENT '所属产品ID',
  `parent_component_id` int(10) unsigned DEFAULT NULL COMMENT '父部件ID (用于层级结构)',
  `name` varchar(255) NOT NULL COMMENT '部件名称',
  `quantity_per_parent` decimal(10,4) NOT NULL DEFAULT '1.0000' COMMENT '相对父项的使用数量',
  `length_formula` varchar(255) DEFAULT NULL COMMENT '长度(cm)，可以是数字或公式',
  `width_formula` varchar(255) DEFAULT NULL COMMENT '宽度(cm)，可以是数字或公式',
  `height_formula` varchar(255) DEFAULT NULL COMMENT '高度(cm)，可以是数字或公式',
  `sequence` int(10) DEFAULT '0' COMMENT '排序',
  `createtime` int(10) DEFAULT NULL,
  `updatetime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_parent_id` (`parent_component_id`),
  CONSTRAINT `fk_comp_product` FOREIGN KEY (`product_id`) REFERENCES `fa_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comp_parent` FOREIGN KEY (`parent_component_id`) REFERENCES `fa_components` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='BOM部件表';

-- ----------------------------
-- 3. 原材料表 (raw_materials)
-- ----------------------------
CREATE TABLE `fa_raw_materials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '原材料名称',
  `type` varchar(100) DEFAULT NULL COMMENT '材料类型 (如: 纸类, 塑料类)',
  `unit_cost` decimal(12,6) NOT NULL COMMENT '单位成本',
  `unit_of_measure` varchar(50) NOT NULL COMMENT '计量单位 (如: 每公斤, 每平方米)',
  `std_length_cm` decimal(10,2) DEFAULT NULL COMMENT '标准长度(cm)',
  `std_width_cm` decimal(10,2) DEFAULT NULL COMMENT '标准宽度(cm)',
  `std_grammage_gsm` decimal(10,2) DEFAULT NULL COMMENT '标准克重(g/m²)',
  `thickness_mm` decimal(10,4) DEFAULT NULL COMMENT '厚度(mm)',
  `density_kgm3` decimal(10,2) DEFAULT NULL COMMENT '密度(kg/m³)',
  `notes` text,
  `createtime` int(10) DEFAULT NULL,
  `updatetime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_material_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='原材料表';

-- ----------------------------
-- 4. 材料使用表 (material_usages)
-- ----------------------------
CREATE TABLE `fa_material_usages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component_id` int(10) unsigned NOT NULL COMMENT '所属部件ID',
  `raw_material_id` int(10) unsigned NOT NULL COMMENT '原材料ID',
  `alias` varchar(255) DEFAULT NULL COMMENT '别名 (用于区分同一部件下的相同材料)',
  `length_formula` varchar(255) DEFAULT NULL COMMENT '使用长度(cm)，数字或公式',
  `width_formula` varchar(255) DEFAULT NULL COMMENT '使用宽度(cm)，数字或公式',
  `grammage_override` decimal(10,2) DEFAULT NULL COMMENT '克重覆盖(g/m²)',
  `thickness_override_mm` decimal(10,4) DEFAULT NULL COMMENT '厚度覆盖(mm)',
  `density_override` decimal(10,2) DEFAULT NULL COMMENT '密度覆盖(kg/m³)',
  `imposition_quantity` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '拼版数',
  `sequence` int(10) DEFAULT '0' COMMENT '排序',
  `createtime` int(10) DEFAULT NULL,
  `updatetime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mu_component_id` (`component_id`),
  CONSTRAINT `fk_mu_component` FOREIGN KEY (`component_id`) REFERENCES `fa_components` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='BOM材料使用表';

-- ----------------------------
-- 5. 工艺表 (processes)
-- ----------------------------
CREATE TABLE `fa_processes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '工艺名称',
  `cost_type` varchar(50) NOT NULL COMMENT '成本类型 (如: 每单位可变, 每面积可变, 每时间可变, 固定)',
  `unit_of_cost` varchar(50) NOT NULL COMMENT '成本单位',
  `cost_rate` decimal(12,6) NOT NULL COMMENT '成本费率',
  `setup_cost` decimal(12,4) DEFAULT '0.0000' COMMENT '设置/开机成本',
  `throughput_rate` decimal(12,4) DEFAULT NULL COMMENT '吞吐率 (用于按时间计费)',
  `labor_rate_per_hour` decimal(12,4) DEFAULT NULL COMMENT '人工费率/小时',
  `waste_percentage` decimal(8,4) DEFAULT '0.0000' COMMENT '该工艺特定损耗率(%)',
  `notes` text,
  `createtime` int(10) DEFAULT NULL,
  `updatetime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_process_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工艺表';

-- ----------------------------
-- 6. 工艺分配表 (process_assignments)
-- ----------------------------
CREATE TABLE `fa_process_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component_id` int(10) unsigned DEFAULT NULL COMMENT '关联的部件ID',
  `material_usage_id` int(10) unsigned DEFAULT NULL COMMENT '关联的材料使用ID',
  `parent_assignment_id` int(10) unsigned DEFAULT NULL COMMENT '父工艺ID (用于子工艺)',
  `process_id` int(10) unsigned NOT NULL COMMENT '工艺ID',
  `alias` varchar(255) DEFAULT NULL COMMENT '别名',
  `sequence` int(10) NOT NULL DEFAULT '0' COMMENT '工艺顺序',
  `cost_override` decimal(12,6) DEFAULT NULL COMMENT '特定成本覆盖',
  `is_affected_by_imposition` tinyint(1) DEFAULT '1' COMMENT '成本是否受父项拼版数影响',
  `is_transformative` tinyint(1) DEFAULT '0' COMMENT '是否转换尺寸/数量',
  `output_length_formula` varchar(255) DEFAULT NULL,
  `output_width_formula` varchar(255) DEFAULT NULL,
  `output_height_formula` varchar(255) DEFAULT NULL,
  `output_quantity_formula` varchar(255) DEFAULT NULL,
  `createtime` int(10) DEFAULT NULL,
  `updatetime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pa_component_id` (`component_id`),
  KEY `idx_pa_material_usage_id` (`material_usage_id`),
  KEY `idx_pa_parent_assignment_id` (`parent_assignment_id`),
  CONSTRAINT `fk_pa_component` FOREIGN KEY (`component_id`) REFERENCES `fa_components` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_material_usage` FOREIGN KEY (`material_usage_id`) REFERENCES `fa_material_usages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_parent_assignment` FOREIGN KEY (`parent_assignment_id`) REFERENCES `fa_process_assignments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='BOM工艺分配表';