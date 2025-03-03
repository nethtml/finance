<?php
/**
 * 路径处理助手函数
 * 
 * @version 1.0
 * @date 2024-03-xx
 */

/**
 * 获取资源文件的相对路径
 * @param string $path 资源路径
 * @return string 返回相对于当前页面的正确路径
 */
function getAssetPath($path) {
    // 获取当前脚本相对于网站根目录的深度
    $depth = substr_count(trim($_SERVER['PHP_SELF'], '/'), '/');
    return str_repeat('../', $depth) . 'assets/' . ltrim($path, '/');
}

/**
 * 获取页面的相对路径
 * @param string $path 页面路径
 * @return string 返回相对于当前页面的正确路径
 */
function getPagePath($path) {
    $depth = substr_count(trim($_SERVER['PHP_SELF'], '/'), '/');
    return str_repeat('../', $depth) . ltrim($path, '/');
}

/**
 * 获取上传文件的相对路径
 * @param string $path 文件路径
 * @return string 返回相对于当前页面的正确路径
 */
function getUploadPath($path) {
    // 移除开头的斜杠
    $path = ltrim($path, '/');
    
    // 获取当前脚本相对于网站根目录的深度
    $depth = substr_count(trim($_SERVER['PHP_SELF'], '/'), '/');
    
    // 如果路径已经包含uploads，则不再添加
    if (strpos($path, 'uploads/') === 0) {
        return str_repeat('../', $depth) . $path;
    }
    
    // 添加uploads路径
    return str_repeat('../', $depth) . 'uploads/' . $path;
} 