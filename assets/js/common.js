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
        tbody.innerHTML = records.map(record => `
            <tr>
                <td>${record.date}</td>
                <td>${record.category}</td>
                <td class="text-success">${record.income > 0 ? '￥' + parseFloat(record.income).toFixed(2) : '-'}</td>
                <td class="text-danger">${record.expense > 0 ? '￥' + parseFloat(record.expense).toFixed(2) : '-'}</td>
                <td>${record.image ? 
                    `<button class="btn btn-sm btn-outline-primary preview-btn" 
                            data-image="${record.image}"
                            onclick="showPreview('${record.image}')">
                        <svg class="preview-icon" viewBox="0 0 24 24" width="16" height="16">
                            <path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5c-1.7-4.4-6-7.5-11-7.5zM12 17c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5zm0-8c-1.7 0-3 1.3-3 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"/>
                        </svg>
                        查看凭证
                    </button>` 
                    : '无'}</td>
                <td>${record.note || '--'}</td>
            </tr>
        `).join('');
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
