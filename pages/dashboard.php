<?php
/**
 * 仪表盘页面
 * 
 * @version 1.4
 * @date 2024-03-xx
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/path_helper.php';

try {
// 获取总收入和支出
$sql = "SELECT 
                SUM(CASE WHEN rc.type = :income_type THEN r.amount ELSE 0 END) as total_income,
                SUM(CASE WHEN rc.type = :expense_type THEN r.amount ELSE 0 END) as total_expense
            FROM records r
            JOIN records_categories rc ON r.category_id = rc.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':income_type' => '收入',
        ':expense_type' => '支出'
    ]);
$totals = $stmt->fetch();

// 设置不显示操作按钮
$showActions = false;
    
    // 初始化默认值
    if (!$totals) {
        $totals = [
            'total_income' => 0,
            'total_expense' => 0
        ];
    }

// 获取月度统计数据
$sql = "SELECT 
            DATE_FORMAT(r.date, '%Y-%m') as month,
            DATE_FORMAT(r.date, '%Y年%m月') as month_label,
            SUM(CASE WHEN rc.type = :income_type THEN r.amount ELSE 0 END) as income,
            SUM(CASE WHEN rc.type = :expense_type THEN r.amount ELSE 0 END) as expense
        FROM records r
        JOIN records_categories rc ON r.category_id = rc.id
        GROUP BY DATE_FORMAT(r.date, '%Y-%m'), DATE_FORMAT(r.date, '%Y年%m月')
        ORDER BY month ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':income_type' => '收入',
    ':expense_type' => '支出'
]);
$monthlyData = $stmt->fetchAll();

// 获取最近一个月的日趋势数据
$sql = "SELECT 
            DATE_FORMAT(r.date, '%Y-%m-%d') as date,
            DATE_FORMAT(r.date, '%d日') as date_label,
            SUM(CASE WHEN rc.type = :income_type THEN r.amount ELSE 0 END) as income,
            SUM(CASE WHEN rc.type = :expense_type THEN r.amount ELSE 0 END) as expense
        FROM records r
        JOIN records_categories rc ON r.category_id = rc.id
        WHERE r.date >= DATE_SUB(LAST_DAY(NOW()), INTERVAL 1 MONTH)
        GROUP BY DATE_FORMAT(r.date, '%Y-%m-%d'), DATE_FORMAT(r.date, '%d日')
        ORDER BY date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':income_type' => '收入',
    ':expense_type' => '支出'
]);
$dailyData = $stmt->fetchAll();

// 获取收支类型统计
$sql = "SELECT 
            rc.type,
            rc.name as category_name,
            COUNT(*) as count,
            SUM(r.amount) as total
        FROM records r
        JOIN records_categories rc ON r.category_id = rc.id
        GROUP BY rc.type, rc.name
        ORDER BY rc.type DESC, total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categoryStats = $stmt->fetchAll();

// 准备饼图数据
$incomeCategories = [];
$expenseCategories = [];
foreach ($categoryStats as $stat) {
    if ($stat['type'] === '收入') {
        $incomeCategories[] = [
            'name' => $stat['category_name'],
            'value' => floatval($stat['total'])
        ];
    } else {
        $expenseCategories[] = [
            'name' => $stat['category_name'],
            'value' => floatval($stat['total'])
        ];
    }
}

// 准备图表数据
$chartData = [
    'months' => [],
    'monthLabels' => [],
    'income' => [],
    'expense' => []
];

if (!empty($monthlyData)) {
    foreach ($monthlyData as $data) {
        $chartData['months'][] = $data['month'];
        $chartData['monthLabels'][] = $data['month_label'];
        $chartData['income'][] = floatval($data['income']);
        $chartData['expense'][] = floatval($data['expense']);
    }
}

// 准备日趋势图数据
$dailyChartData = [
    'dates' => [],
    'dateLabels' => [],
    'income' => [],
    'expense' => []
];

if (!empty($dailyData)) {
    foreach ($dailyData as $data) {
        $dailyChartData['dates'][] = $data['date'];
        $dailyChartData['dateLabels'][] = $data['date_label'];
        $dailyChartData['income'][] = floatval($data['income']);
        $dailyChartData['expense'][] = floatval($data['expense']);
    }
}

// 设置子页面标记
$isSubPage = true;
    require_once __DIR__ . '/../includes/header.php';
} catch (PDOException $e) {
    // 记录详细错误信息
    error_log("仪表盘数据查询失败: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("Error Line: " . $e->getLine());
    
    // 设置默认值
    $totals = ['total_income' => 0, 'total_expense' => 0];
    $monthlyData = [];
    $dailyData = [];
    $categoryStats = [];
    $incomeCategories = [];
    $expenseCategories = [];
    $chartData = ['months' => [], 'income' => [], 'expense' => []];
    $dailyChartData = ['dates' => [], 'dateLabels' => [], 'income' => [], 'expense' => []];
    
    // 显示错误信息
    $error = "数据加载失败，请稍后再试 (错误代码: " . $e->getCode() . ")";
    
    // 设置子页面标记并加载头部
    $isSubPage = true;
    require_once __DIR__ . '/../includes/header.php';
}
?>

<?php if (isset($error)): ?>
<div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-4">仪表盘</h2>
    </div>
</div>

<!-- 总体统计卡片 -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">总收入</h5>
                <h3 class="card-text">￥<?php echo number_format($totals['total_income'], 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h5 class="card-title">总支出</h5>
                <h3 class="card-text">￥<?php echo number_format($totals['total_expense'], 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card <?php echo ($totals['total_income'] - $totals['total_expense'] >= 0) ? 'bg-success' : 'bg-warning'; ?> text-white h-100">
            <div class="card-body">
                <h5 class="card-title">结余</h5>
                <h3 class="card-text">￥<?php echo number_format($totals['total_income'] - $totals['total_expense'], 2); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- 图表区域 -->
<div class="row">
    <!-- 趋势图和收支占比图 -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary active" id="dailyTrendBtn">日收支趋势</button>
                    <button type="button" class="btn btn-outline-primary" id="monthlyTrendBtn">月度收支趋势</button>
                </div>
                <div class="d-flex align-items-center" id="monthControl">
                    <span class="me-3" id="selectedMonth"></span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="prevMonth">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="currentMonth">本月</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="nextMonth">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="dailyTrend" style="height: 400px;"></div>
                <div id="monthlyTrend" style="height: 400px; display: none;"></div>
            </div>
        </div>
    </div>
    
    <!-- 收支占比图 -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">收支分类占比</h5>
            </div>
            <div class="card-body">
                <div id="typeDistribution" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- 图片预览模态框 -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">图片预览</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" class="img-fluid" id="previewImage" alt="预览图片">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        if (typeof echarts === 'undefined') {
            console.error('ECharts 未加载');
            return;
        }

        // 初始化图表
        const initCharts = function() {
            // 月度趋势图
            const monthlyChart = echarts.init(document.getElementById('monthlyTrend'));
            const monthlyOption = {
                backgroundColor: '#ffffff',
        title: {
                    text: '月度收支趋势',
                    left: 'center',
                    top: 10,
                    textStyle: {
                        fontSize: 18,
                        fontWeight: 'bold',
                        color: '#333'
                    }
        },
        tooltip: {
            trigger: 'axis',
                    backgroundColor: 'rgba(50, 50, 50, 0.9)',
                    borderColor: '#333',
                    borderWidth: 0,
                    textStyle: {
                        color: '#fff'
                    },
            formatter: function(params) {
                        let result = `<div style="font-weight:bold;margin-bottom:5px;">${params[0].axisValue}</div>`;
                params.forEach(param => {
                            const color = param.seriesName === '收入' ? '#4CAF50' : '#FF5252';
                            const value = param.value || 0;
                            result += `<div style="color:${color};font-size:14px;line-height:20px;">
                                ${param.seriesName}: ￥${value.toFixed(2)}
                            </div>`;
                });
                return result;
            }
        },
        legend: {
                    data: ['收入', '支出'],
                    bottom: 10,
                    icon: 'circle',
                    itemWidth: 10,
                    itemHeight: 10,
                    textStyle: {
                        fontSize: 12,
                        color: '#666'
                    }
                },
                grid: {
                    left: '5%',
                    right: '5%',
                    bottom: '15%',
                    top: '15%',
                    containLabel: true
        },
        xAxis: {
            type: 'category',
                    boundaryGap: true,
                    data: <?php echo json_encode($chartData['monthLabels']); ?>,
                    axisLabel: {
                        interval: 0,
                        rotate: 30,
                        fontSize: 12,
                        color: '#666'
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#ddd'
                        }
                    },
                    axisTick: {
                        show: false
                    }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                        formatter: '￥{value}',
                        fontSize: 12,
                        color: '#666'
                    },
                    splitLine: {
                        lineStyle: {
                            color: '#eee',
                            type: 'dashed'
                        }
                    },
                    axisLine: {
                        show: false
                    },
                    axisTick: {
                        show: false
            }
        },
        series: [
            {
                name: '收入',
                type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 8,
                data: <?php echo json_encode($chartData['income']); ?>,
                itemStyle: {
                            color: '#4CAF50'
                        },
                        lineStyle: {
                            width: 3,
                            shadowColor: 'rgba(76, 175, 80, 0.3)',
                            shadowBlur: 10
                        },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(76, 175, 80, 0.3)' },
                                { offset: 1, color: 'rgba(76, 175, 80, 0.1)' }
                            ])
                },
                emphasis: {
                    itemStyle: {
                        borderWidth: 3,
                        shadowColor: 'rgba(76, 175, 80, 0.5)',
                        shadowBlur: 10
                    }
                }
            },
            {
                name: '支出',
                type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 8,
                data: <?php echo json_encode($chartData['expense']); ?>,
                itemStyle: {
                            color: '#FF5252'
                        },
                        lineStyle: {
                            width: 3,
                            shadowColor: 'rgba(255, 82, 82, 0.3)',
                            shadowBlur: 10
                        },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(255, 82, 82, 0.3)' },
                                { offset: 1, color: 'rgba(255, 82, 82, 0.1)' }
                            ])
                        },
                        emphasis: {
                            itemStyle: {
                                borderWidth: 3,
                                shadowColor: 'rgba(255, 82, 82, 0.5)',
                                shadowBlur: 10
                            }
                        }
                    }
                ]
            };
            monthlyChart.setOption(monthlyOption);
    
    // 日趋势图
    const dailyChart = echarts.init(document.getElementById('dailyTrend'));
            const dailyOption = {
                backgroundColor: '#ffffff',
                title: {
                    text: '日收支趋势',
                    left: 'center',
                    top: 10,
                    textStyle: {
                        fontSize: 18,
                        fontWeight: 'bold',
                        color: '#333'
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(50, 50, 50, 0.9)',
                    borderWidth: 0,
                    textStyle: {
                        color: '#fff'
                    },
                    formatter: function(params) {
                        let result = `<div style="font-weight:bold;margin-bottom:5px;">${params[0].axisValue}</div>`;
                        params.forEach(param => {
                            const color = param.seriesName === '收入' ? '#4CAF50' : '#FF5252';
                            const value = param.value || 0;
                            result += `<div style="color:${color};font-size:14px;line-height:20px;">
                                ${param.seriesName}: ￥${value.toFixed(2)}
                            </div>`;
                        });
                        return result;
                    }
                },
                legend: {
                    data: ['收入', '支出'],
                    bottom: 10,
                    icon: 'circle',
                    itemWidth: 10,
                    itemHeight: 10,
                    textStyle: {
                        fontSize: 12,
                        color: '#666'
                    }
                },
                grid: {
                    left: '5%',
                    right: '5%',
                    bottom: '15%',
                    top: '15%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: <?php echo json_encode($dailyChartData['dateLabels']); ?>,
                    axisLabel: {
                        interval: 0,
                        fontSize: 12,
                        color: '#666'
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#ddd'
                        }
                    },
                    axisTick: {
                        show: false
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '￥{value}',
                        fontSize: 12,
                        color: '#666'
                    },
                    splitLine: {
                        lineStyle: {
                            color: '#eee',
                            type: 'dashed'
                        }
                    },
                    axisLine: {
                        show: false
                    },
                    axisTick: {
                        show: false
                    }
                },
                series: [
                    {
                        name: '收入',
                        type: 'bar',
                        barWidth: '20%',
                        data: <?php echo json_encode($dailyChartData['income']); ?>,
                        itemStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: '#4CAF50' },
                                { offset: 1, color: '#81C784' }
                            ])
                        },
                        emphasis: {
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    { offset: 0, color: '#66BB6A' },
                                    { offset: 1, color: '#A5D6A7' }
                                ])
                            }
                        }
                    },
                    {
                        name: '支出',
                        type: 'bar',
                        barWidth: '20%',
                        data: <?php echo json_encode($dailyChartData['expense']); ?>,
                        itemStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: '#FF5252' },
                                { offset: 1, color: '#FF8A80' }
                            ])
                        },
                        emphasis: {
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    { offset: 0, color: '#FF6E6E' },
                                    { offset: 1, color: '#FFAB9D' }
                                ])
                            }
                        }
                    }
                ]
            };
            dailyChart.setOption(dailyOption);

            // 当前选中的月份
            let currentSelectedMonth = '';
            
            // 获取最近有记录的月份
            const monthLabels = <?php echo json_encode($chartData['monthLabels']); ?>;
            if (monthLabels.length > 0) {
                currentSelectedMonth = monthLabels[monthLabels.length - 1];
                document.getElementById('selectedMonth').textContent = currentSelectedMonth;
                const month = currentSelectedMonth.replace('年', '-').replace('月', '');
                updateDailyChart(month);
            } else {
                // 如果没有记录，显示当前月
                const now = new Date();
                const currentMonthStr = now.toISOString().slice(0, 7);
                const currentMonthLabel = now.getFullYear() + '年' + 
                    String(now.getMonth() + 1).padStart(2, '0') + '月';
                currentSelectedMonth = currentMonthLabel;
                document.getElementById('selectedMonth').textContent = currentMonthLabel;
                updateDailyChart(currentMonthStr);
            }

            // 切换按钮事件
            document.getElementById('dailyTrendBtn').addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('monthlyTrendBtn').classList.remove('active');
                document.getElementById('monthlyTrendBtn').classList.remove('btn-primary');
                document.getElementById('monthlyTrendBtn').classList.add('btn-outline-primary');
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                document.getElementById('dailyTrend').style.display = 'block';
                document.getElementById('monthlyTrend').style.display = 'none';
                document.getElementById('monthControl').style.display = 'flex';
                dailyChart.resize();
            });

            document.getElementById('monthlyTrendBtn').addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('dailyTrendBtn').classList.remove('active');
                document.getElementById('dailyTrendBtn').classList.remove('btn-primary');
                document.getElementById('dailyTrendBtn').classList.add('btn-outline-primary');
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                document.getElementById('monthlyTrend').style.display = 'block';
                document.getElementById('dailyTrend').style.display = 'none';
                document.getElementById('monthControl').style.display = 'none';
                monthlyChart.resize();
            });

            // 月度图表点击事件
            monthlyChart.on('click', function(params) {
                const monthLabel = params.name; // 例如: "2024年03月"
                const month = monthLabel.replace('年', '-').replace('月', ''); // 转换为 "2024-03" 格式
                currentSelectedMonth = monthLabel;
                document.getElementById('selectedMonth').textContent = monthLabel;
                // 切换到日趋势图
                document.getElementById('dailyTrendBtn').click();
                updateDailyChart(month);
            });

            // 更新日趋势图数据
            function updateDailyChart(month) {
                // 显示加载状态
                dailyChart.showLoading({
                    text: '加载中...',
                    color: '#4CAF50',
                    textColor: '#000',
                    maskColor: 'rgba(255, 255, 255, 0.8)',
                    zlevel: 0
                });

                fetch(`/api/daily_trend.php?month=${month}`)
                    .then(response => response.json())
                    .then(response => {
                        if (response.success && response.data) {
                            dailyOption.xAxis.data = response.data.dateLabels;
                            dailyOption.series[0].data = response.data.income;
                            dailyOption.series[1].data = response.data.expense;
                            dailyChart.setOption(dailyOption);
                        } else {
                            throw new Error(response.message || '获取数据失败');
                        }
                    })
                    .catch(error => {
                        console.error('获取日趋势数据失败:', error);
                        // 显示错误提示
                        document.getElementById('dailyTrend').innerHTML = 
                            '<div class="alert alert-danger">数据加载失败，请重试</div>';
                    })
                    .finally(() => {
                        dailyChart.hideLoading();
                    });
            }

            // 月份切换按钮事件
            document.getElementById('prevMonth').addEventListener('click', function() {
                if (!currentSelectedMonth) return;
                const month = currentSelectedMonth.replace('年', '-').replace('月', '');
                const prevMonth = new Date(month + '-01');
                prevMonth.setMonth(prevMonth.getMonth() - 1);
                const prevMonthStr = prevMonth.toISOString().slice(0, 7);
                const prevMonthLabel = prevMonth.getFullYear() + '年' + 
                    String(prevMonth.getMonth() + 1).padStart(2, '0') + '月';
                currentSelectedMonth = prevMonthLabel;
                document.getElementById('selectedMonth').textContent = prevMonthLabel;
                updateDailyChart(prevMonthStr);
            });

            document.getElementById('nextMonth').addEventListener('click', function() {
                if (!currentSelectedMonth) return;
                const month = currentSelectedMonth.replace('年', '-').replace('月', '');
                const nextMonth = new Date(month + '-01');
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                const nextMonthStr = nextMonth.toISOString().slice(0, 7);
                const nextMonthLabel = nextMonth.getFullYear() + '年' + 
                    String(nextMonth.getMonth() + 1).padStart(2, '0') + '月';
                currentSelectedMonth = nextMonthLabel;
                document.getElementById('selectedMonth').textContent = nextMonthLabel;
                updateDailyChart(nextMonthStr);
            });

            document.getElementById('currentMonth').addEventListener('click', function() {
                const now = new Date();
                const currentMonthStr = now.toISOString().slice(0, 7);
                const currentMonthLabel = now.getFullYear() + '年' + 
                    String(now.getMonth() + 1).padStart(2, '0') + '月';
                currentSelectedMonth = currentMonthLabel;
                document.getElementById('selectedMonth').textContent = currentMonthLabel;
                updateDailyChart(currentMonthStr);
            });

            // 收支占比图
            const typeChart = echarts.init(document.getElementById('typeDistribution'));
            const typeOption = {
                backgroundColor: '#ffffff',
        title: {
            text: '收支分类占比',
                    left: 'center',
                    top: 10,
                    textStyle: {
                        fontSize: 18,
                        fontWeight: 'bold',
                        color: '#333'
                    }
        },
        tooltip: {
            trigger: 'item',
                    backgroundColor: 'rgba(50, 50, 50, 0.9)',
                    borderWidth: 0,
                    textStyle: {
                        color: '#fff'
                    },
                    formatter: function(params) {
                        return `<div style="font-weight:bold;margin-bottom:5px;">${params.name}</div>
                            <div style="font-size:14px;line-height:20px;">
                                金额: ￥${params.value.toFixed(2)}<br/>
                                占比: ${params.percent}%
                            </div>`;
                    }
        },
        legend: {
                    orient: 'vertical',
                    right: '5%',
                    top: 'center',
                    icon: 'circle',
                    itemWidth: 10,
                    itemHeight: 10,
                    textStyle: {
                        fontSize: 12,
                        color: '#666'
                    }
        },
        series: [
            {
                name: '收入分类',
                type: 'pie',
                radius: ['20%', '40%'],
                center: ['40%', '50%'],
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                },
                data: <?php echo json_encode($incomeCategories); ?>,
                color: ['#4CAF50', '#66BB6A', '#81C784', '#A5D6A7', '#C8E6C9']
            },
            {
                name: '支出分类',
                type: 'pie',
                radius: ['50%', '70%'],
                center: ['40%', '50%'],
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                },
                data: <?php echo json_encode($expenseCategories); ?>,
                color: ['#FF5252', '#FF8A80', '#FF8A65', '#FFAB91', '#FFCCBC']
            }
        ]
    };
            typeChart.setOption(typeOption);

            // 监听窗口大小变化
    window.addEventListener('resize', function() {
        if (document.getElementById('monthlyTrend').style.display !== 'none') {
            monthlyChart.resize();
        }
        if (document.getElementById('dailyTrend').style.display !== 'none') {
            dailyChart.resize();
        }
        typeChart.resize();
    });
        };

        // 初始化图表
        initCharts();

        // 图片预览功能
        document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target^="#imageModal"]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const imgSrc = this.getAttribute('href');
                document.getElementById('previewImage').src = imgSrc;
                const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                modal.show();
            });
        });

    } catch (error) {
        console.error('图表初始化失败:', error);
        document.querySelectorAll('#monthlyTrend, #dailyTrend, #typeDistribution').forEach(container => {
            container.innerHTML = '<div class="alert alert-danger">图表加载失败，请刷新页面重试</div>';
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 