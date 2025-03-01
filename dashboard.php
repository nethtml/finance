<?php
$pageTitle = "仪表盘";

// 额外的JavaScript
$extraJs = '
<script src="assets/js/echarts.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        loadStatistics();
        initCharts();
        // 默认隐藏流水列表
        document.getElementById("recordsSection").style.display = "none";
    });

    // 切换显示内容
    function toggleView(view) {
        const dashboardBtn = document.getElementById("dashboardBtn");
        const recordsBtn = document.getElementById("recordsBtn");
        const chartsSection = document.getElementById("chartsSection");
        const recordsSection = document.getElementById("recordsSection");

        // 移除两个按钮的焦点状态
        dashboardBtn.blur();
        recordsBtn.blur();

        if (view === "dashboard") {
            chartsSection.style.display = "block";
            recordsSection.style.display = "none";
            dashboardBtn.classList.add("btn-primary");
            dashboardBtn.classList.remove("btn-outline-primary");
            recordsBtn.classList.add("btn-outline-primary");
            recordsBtn.classList.remove("btn-primary");
        } else {
            chartsSection.style.display = "none";
            recordsSection.style.display = "block";
            recordsBtn.classList.add("btn-primary");
            recordsBtn.classList.remove("btn-outline-primary");
            dashboardBtn.classList.add("btn-outline-primary");
            dashboardBtn.classList.remove("btn-primary");
            loadRecords(); // 加载流水记录
        }
    }
</script>';

// 主要内容
$mainContent = '
<!-- 页面标题和切换按钮 -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="btn-group">
        <button id="dashboardBtn" class="btn btn-primary px-4" onclick="toggleView(\'dashboard\')" style="min-width: 100px;">仪表盘</button>
        <button id="recordsBtn" class="btn btn-outline-primary px-4" onclick="toggleView(\'records\')" style="min-width: 100px;">全部流水</button>
    </div>
</div>

<!-- 仪表盘内容 -->
<div id="chartsSection">
    <!-- 统计卡片 -->
    <div class="row">
        <!-- 总收入卡片 -->
        <div class="col-md-4">
            <div class="card stat-card income">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-wallet2 me-2"></i>
                        <h6 class="card-title mb-0">总收入</h6>
                    </div>
                    <div class="stat-number" id="totalIncome">计算中...</div>
                </div>
            </div>
        </div>

        <!-- 总支出卡片 -->
        <div class="col-md-4">
            <div class="card stat-card expense">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-credit-card me-2"></i>
                        <h6 class="card-title mb-0">总支出</h6>
                    </div>
                    <div class="stat-number" id="totalExpense">计算中...</div>
                </div>
            </div>
        </div>

        <!-- 当前结余卡片 -->
        <div class="col-md-4">
            <div class="card stat-card balance">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-piggy-bank me-2"></i>
                        <h6 class="card-title mb-0">当前结余</h6>
                    </div>
                    <div class="stat-number" id="totalBalance">计算中...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 图表区域 -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">收支趋势</h5>
                    <div id="trendChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">分类统计</h5>
                    <div id="categoryChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 流水记录内容 -->
<div id="recordsSection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead style="background-color: #e9ecef;">
                        <tr>
                            <th>日期</th>
                            <th>分类</th>
                            <th>金额</th>
                            <th>凭证</th>
                            <th>备注</th>
                        </tr>
                    </thead>
                    <tbody id="recordsBody">
                        <!-- 表格内容由 JavaScript 动态生成 -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';

// 引入基础模板
include 'templates/base.html';
?> 