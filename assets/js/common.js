// API基础URL
const API_BASE = 'api.php';
let authToken = 'Bearer your_secret_key'; // 与后端一致

// 通用API调用函数
async function fetchAPI(action, data = {}) {
    try {
        // 构建URL，添加action参数
        const url = `${API_BASE}?action=${action}`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': authToken
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        console.log('API Response:', result); // 调试日志
        return result;
    } catch (error) {
        console.error('API调用错误:', error);
        return { success: false, message: '网络错误' };
    }
}

// 格式化金额
function formatAmount(amount) {
    return parseFloat(amount).toFixed(2);
}

// 格式化日期
function formatDate(date) {
    return new Date(date).toLocaleDateString('zh-CN');
}

// 提交表单
async function handleSubmit(e) {
    e.preventDefault();
    
    const formData = {
        date: document.getElementById('date').value,
        category: document.getElementById('category').value,
        income: parseFloat(document.getElementById('income').value) || 0,
        expense: parseFloat(document.getElementById('expense').value) || 0,
        image: document.getElementById('imagePreview').querySelector('img')?.src || '',
        note: document.getElementById('note').value
    };

    if (!formData.date || !formData.category) {
        alert('日期和分类为必填项');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}?action=create_record`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': authToken
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || '保存失败');
        }

        alert('记录保存成功！');
        e.target.reset();
        document.getElementById('imagePreview').innerHTML = '';
        window.location.href = 'dashboard.html';  // 修改这里，直接跳转到仪表盘页面
    } catch (error) {
        alert(error.message);
    }
}

// 图片上传
async function handleImageUpload() {
    const file = this.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await fetch(`${API_BASE}?action=upload_image`, {
            method: 'POST',
            headers: {
                'Authorization': authToken
            },
            body: formData
        });

        const { url } = await response.json();
        document.getElementById('imagePreview').innerHTML = `
            <img src="${url}" class="img-fluid rounded" 
                 onclick="window.open('${url}', '_blank')">
        `;
    } catch (error) {
        alert('图片上传失败: ' + error.message);
    }
}

// 加载统计
async function loadStatistics() {
    try {
        const response = await fetch(`${API_BASE}?action=get_stats`);
        const data = await response.json();
        
        document.getElementById('totalIncome').textContent = data.total_income.toFixed(2);
        document.getElementById('totalExpense').textContent = data.total_expense.toFixed(2);
        document.getElementById('totalBalance').textContent = data.balance.toFixed(2);
    } catch (error) {
        console.error('加载统计失败:', error);
    }
}

// 加载记录
async function loadRecords() {
    try {
        const response = await fetch(`${API_BASE}?action=get_records`);
        const records = await response.json();
        
        const tbody = document.getElementById('recordsBody');
        tbody.innerHTML = records.map(record => {
            const isIncome = record.income > 0;
            return `
            <tr class="${isIncome ? 'table-light' : ''}" style="background-color: ${isIncome ? '#d3ccd6' : '#ffffff'}">
                <td>${record.date}</td>
                <td>
                    <span class="badge ${isIncome ? 'bg-success' : 'bg-danger'} me-1">
                        ${isIncome ? '收入' : '支出'}
                    </span>
                    <span class="badge bg-secondary">
                        ${record.category}
                    </span>
                </td>
                <td>${isIncome ? 
                    '￥' + parseFloat(record.income).toFixed(2) : 
                    '￥' + parseFloat(record.expense).toFixed(2)
                }</td>
                <td>${record.image ? 
                    `<button class="btn btn-sm btn-outline-primary preview-btn" 
                            data-image="${record.image}"
                            onclick="showPreview('${record.image}')">
                        <svg class="preview-icon" viewBox="0 0 24 24" width="16" height="16">
                            <path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5c-1.7-4.4-6-7.5-11-7.5zM12 17c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5zm0-8c-1.7 0-3 1.3-3 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"/>
                        </svg>
                        查看
                    </button>` 
                    : '无'}</td>
                <td>${record.note || '--'}</td>
            </tr>
        `}).join('');
    } catch (error) {
        console.error('加载记录失败:', error);
    }
}

function showPreview(imgUrl) {
    const modal = document.createElement('div');
    modal.className = 'preview-modal active';
    modal.innerHTML = `
        <div class="preview-content">
            <img src="${imgUrl}" class="img-fluid" style="max-height:80vh">
            <div class="text-center mt-3">
                <button onclick="this.closest('.preview-modal').remove()" 
                        class="btn btn-danger btn-sm">
                    关闭
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// 点击外部关闭
document.addEventListener('click', (e) => {
    if(e.target.classList.contains('preview-modal')) {
        e.target.remove();
    }
});

// 添加图表初始化函数
async function initCharts() {
    try {
        // 获取数据
        const response = await fetch(`${API_BASE}?action=get_records`);
        const records = await response.json();
        
        // 初始化两个图表实例
        const categoryChart = echarts.init(document.getElementById('categoryChart'));
        const trendChart = echarts.init(document.getElementById('trendChart'));
        
        // 处理分类数据
        const categories = processCategories(records);
        
        // 处理趋势数据
        const trends = processTrends(records);
        
        // 设置图表配置和数据
        setCategoryChartOption(categoryChart, categories);
        setTrendChartOption(trendChart, trends);
        
        // 响应窗口调整大小
        window.addEventListener('resize', () => {
            categoryChart.resize();
            trendChart.resize();
        });
    } catch (error) {
        console.error('加载图表数据失败:', error);
    }
}

// 处理分类数据
function processCategories(records) {
    // 初始化收入和支出的分类统计
    const incomeCategories = {};
    const expenseCategories = {};
    
    // 统计每个分类的金额
    records.forEach(record => {
        if (record.income > 0) {
            incomeCategories[record.category] = (incomeCategories[record.category] || 0) + parseFloat(record.income);
        } else {
            expenseCategories[record.category] = (expenseCategories[record.category] || 0) + parseFloat(record.expense);
        }
    });
    
    // 转换为图表所需的数据格式
    return {
        income: Object.entries(incomeCategories).map(([name, value]) => ({ name, value })),
        expense: Object.entries(expenseCategories).map(([name, value]) => ({ name, value }))
    };
}

// 处理趋势数据
function processTrends(records) {
    // 获取最近6个月的数据
    const months = {};
    const now = new Date();
    
    // 初始化最近6个月的数据结构
    for (let i = 5; i >= 0; i--) {
        const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        months[monthKey] = { income: 0, expense: 0 };
    }
    
    // 统计每月的收支
    records.forEach(record => {
        const monthKey = record.date.substring(0, 7);
        if (months[monthKey]) {
            if (record.income > 0) {
                months[monthKey].income += parseFloat(record.income);
            } else {
                months[monthKey].expense += parseFloat(record.expense);
            }
        }
    });
    
    return {
        months: Object.keys(months),
        income: Object.values(months).map(m => m.income.toFixed(2)),
        expense: Object.values(months).map(m => m.expense.toFixed(2))
    };
}

// 设置分类图表的配置
function setCategoryChartOption(chart, data) {
    const option = {
        title: {
            text: '收支分类统计',
            left: 'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: '{b}: ¥{c} ({d}%)'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            top: 'middle'
        },
        series: [
            {
                name: '支出',
                type: 'pie',
                radius: ['40%', '55%'],
                label: {
                    position: 'outer',
                    alignTo: 'none',
                    bleedMargin: 5
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '14',
                        fontWeight: 'bold'
                    }
                },
                data: data.expense
            },
            {
                name: '收入',
                type: 'pie',
                radius: ['20%', '35%'],
                label: {
                    position: 'outer',
                    alignTo: 'none',
                    bleedMargin: 5
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '14',
                        fontWeight: 'bold'
                    }
                },
                data: data.income
            }
        ]
    };
    
    chart.setOption(option);
}

// 设置趋势图表的配置
function setTrendChartOption(chart, data) {
    const option = {
        title: {
            text: '月度收支趋势',
            left: 'center'
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            },
            formatter: function(params) {
                let result = params[0].axisValue + '<br/>';
                params.forEach(param => {
                    result += param.marker + param.seriesName + ': ¥' + param.value + '<br/>';
                });
                return result;
            }
        },
        legend: {
            data: ['收入', '支出'],
            top: '30px'
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.months
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter: '¥{value}'
            }
        },
        series: [
            {
                name: '收入',
                type: 'bar',
                data: data.income,
                itemStyle: {
                    color: '#198754' // Bootstrap success color
                }
            },
            {
                name: '支出',
                type: 'bar',
                data: data.expense,
                itemStyle: {
                    color: '#dc3545' // Bootstrap danger color
                }
            }
        ]
    };
    
    chart.setOption(option);
}
