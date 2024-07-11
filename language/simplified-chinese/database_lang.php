<?php
/**
 * 数据库 错误信息
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// 插入 缺少数据
$lang['database_missing_insert_data'] = '创建 {field} 失败';
// 插入 失败
$lang['database_insert_failed'] = '创建 {field} 失败';
// 更新 失败
$lang['database_update_failed'] = '更新 {field} 失败';
// 查询 失败
$lang['database_select_failed'] = '查询 {field} 失败';
// 查询 找到多条记录(记录不唯一)
$lang['database_multiple_records_found'] = '查询 {field} 失败';
// 查询 记录未找到
$lang['database_records_not_found'] = '{field} 不存在';
// DB删除 失败
$lang['database_delete_failed'] = '删除 {field} 失败';
// 模型 未找到
$lang['database_model_not_found'] = '{field} 不存在';
// 模型 缺少属性
$lang['database_model_missing_attribute'] = '服务器错误';
// 模型 缺少属性值
$lang['database_model_missing_attribute_value'] = '服务器错误';
// 模型 JSON编码失败
$lang['database_model_json_encoding_failure'] = '服务器错误';

// 自定义 模型类名称
$lang['database_models'] = [
	'custom-model-class-name' => 'custom-label'
];
