<?php
$pageTitle = "后台管理";

// 主要内容
$mainContent = '
<!-- 页面标题 -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="m-0">新增财务记录</h4>
</div>

<!-- 新增记录表单 -->
<div class="card">
    <div class="card-body">
        <form id="recordForm" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">日期</label>
                <input type="date" class="form-control" id="date" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">分类</label>
                <select class="form-select" id="category" required>
                    <option value="">请选择分类</option>
                    <optgroup label="收入">
                        <option value="工资">工资</option>
                        <option value="投资">投资</option>
                    </optgroup>
                    <optgroup label="支出">
                        <option value="餐饮">餐饮</option>
                        <option value="房租">房租</option>
                    </optgroup>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">收入金额</label>
                <div class="input-group">
                    <span class="input-group-text">￥</span>
                    <input type="number" class="form-control" id="income" step="0.01">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">支出金额</label>
                <div class="input-group">
                    <span class="input-group-text">￥</span>
                    <input type="number" class="form-control" id="expense" step="0.01">
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">上传凭证</label>
                <div class="border rounded p-3">
                    <div class="mb-3">
                        <input type="file" class="form-control" id="imageUpload" accept="image/*">
                    </div>
                    <div id="imagePreview" class="upload-preview"></div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">备注</label>
                <textarea class="form-control" id="note" rows="2"></textarea>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16">
                        <path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1H2z"/>
                    </svg>
                    保存记录
                </button>
            </div>
        </form>
    </div>
</div>';

// 额外的 JavaScript
$extraJs = '
<script>
    // 页面加载完成后执行
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("recordForm").addEventListener("submit", handleSubmit);
        document.getElementById("imageUpload").addEventListener("change", handleImageUpload);
    });
</script>';

// 引入基础模板
include 'templates/base.html';
?> 