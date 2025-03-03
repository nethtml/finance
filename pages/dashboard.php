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
                SUM(CASE WHEN rc.type = :income_type THEN r.amount ELSE 0 END) as income,
                SUM(CASE WHEN rc.type = :expense_type THEN r.amount ELSE 0 END) as expense
            FROM records r
            JOIN records_categories rc ON r.category_id = rc.id
            GROUP BY DATE_FORMAT(r.date, '%Y-%m')
        ORDER BY month";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':income_type' => '收入',
        ':expense_type' => '支出'
    ]);
$monthlyData = $stmt->fetchAll();

// 获取收支类型统计
    $sql = "SELECT rc.type, COUNT(*) as count, SUM(r.amount) as total
            FROM records r
            JOIN records_categories rc ON r.category_id = rc.id
            GROUP BY rc.type";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
$typeStats = $stmt->fetchAll();

// 准备图表数据
$chartData = [
    'months' => [],
    'income' => [],
    'expense' => []
];

    if (!empty($monthlyData)) {
foreach ($monthlyData as $data) {
    $chartData['months'][] = $data['month'];
    $chartData['income'][] = floatval($data['income']);
    $chartData['expense'][] = floatval($data['expense']);
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
    $typeStats = [];
    $chartData = ['months' => [], 'income' => [], 'expense' => []];
    
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
        <h2 class="mb-4">财务仪表盘</h2>
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
    <!-- 月度趋势图 -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">月度收支趋势</h5>
            </div>
            <div class="card-body">
                <div id="monthlyTrend" style="height: 400px;"></div>
            </div>
        </div>
    </div>
    
    <!-- 收支占比图 -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">收支占比</h5>
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
// 等待页面和所有资源加载完成
document.addEventListener('DOMContentLoaded', function() {
    try {
        // 确保 ECharts 已加载
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
                        let result = `<div style="font-weight:bold;margin-bottom:5px;">${params[0].name}</div>`;
                params.forEach(param => {
                            const color = param.seriesName === '收入' ? '#4CAF50' : '#FF5252';
                            result += `<div style="color:${color};font-size:14px;line-height:20px;">
                                ${param.seriesName}: ￥${param.value.toFixed(2)}
                            </div>`;
                });
                return result;
            }
        },
        legend: {
                    data: ['收入', '支出'],
                    bottom: 0,
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
                    bottom: '10%',
                    top: '15%',
                    containLabel: true
        },
        xAxis: {
            type: 'category',
                    boundaryGap: false,
                    data: <?php echo json_encode($chartData['months']); ?>,
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
                        symbolSize: 10,
                data: <?php echo json_encode($chartData['income']); ?>,
                itemStyle: {
                            color: '#4CAF50'
                        },
                        lineStyle: {
                            width: 4,
                            shadowColor: 'rgba(76, 175, 80, 0.3)',
                            shadowBlur: 10
                        },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(76, 175, 80, 0.5)' },
                                { offset: 1, color: 'rgba(76, 175, 80, 0.1)' }
                            ])
                }
            },
            {
                name: '支出',
                type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 10,
                data: <?php echo json_encode($chartData['expense']); ?>,
                itemStyle: {
                            color: '#FF5252'
                        },
                        lineStyle: {
                            width: 4,
                            shadowColor: 'rgba(255, 82, 82, 0.3)',
                            shadowBlur: 10
                        },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(255, 82, 82, 0.5)' },
                                { offset: 1, color: 'rgba(255, 82, 82, 0.1)' }
                            ])
                        }
                    }
                ]
            };
            monthlyChart.setOption(monthlyOption);
    
    // 收支占比图
    const typeChart = echarts.init(document.getElementById('typeDistribution'));
            const typeOption = {
                backgroundColor: '#ffffff',
        title: {
            text: '收支占比',
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
                type: 'pie',
                        radius: ['50%', '70%'],
                        center: ['40%', '50%'],
                        avoidLabelOverlap: true,
                        itemStyle: {
                            borderRadius: 15,
                            borderColor: '#fff',
                            borderWidth: 3
                        },
                        label: {
                            show: false
                        },
                        emphasis: {
                            scale: true,
                            scaleSize: 10,
                            label: {
                                show: true,
                                fontSize: 16,
                                fontWeight: 'bold',
                                formatter: '{b}\n{d}%'
                            },
                            itemStyle: {
                                shadowBlur: 20,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.2)'
                            }
                        },
                        labelLine: {
                            show: false
                        },
                data: [
                    {
                        name: '收入',
                                value: <?php echo floatval($totals['total_income']); ?>,
                                itemStyle: {
                                    color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                                        { offset: 0, color: '#4CAF50' },
                                        { offset: 1, color: '#81C784' }
                                    ])
                                }
                    },
                    {
                        name: '支出',
                                value: <?php echo floatval($totals['total_expense']); ?>,
                    itemStyle: {
                                    color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                                        { offset: 0, color: '#FF5252' },
                                        { offset: 1, color: '#FF8A80' }
                                    ])
                                }
                            }
                        ]
                    }
                ]
            };
            typeChart.setOption(typeOption);

            // 监听窗口大小变化
    window.addEventListener('resize', function() {
        monthlyChart.resize();
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
        document.querySelectorAll('#monthlyTrend, #typeDistribution').forEach(container => {
            container.innerHTML = '<div class="alert alert-danger">图表加载失败，请刷新页面重试</div>';
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 