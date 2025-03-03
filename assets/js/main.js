// 全局JavaScript配置和函数

/**
 * 初始化文件预览功能
 */
function initFilePreview() {
    console.log('初始化文件预览功能');
    const attachmentInput = document.getElementById('attachment');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removePreviewBtn = document.getElementById('remove-preview');

    if (!attachmentInput) {
        console.error('未找到文件输入元素');
        return;
    }

    console.log('找到文件输入元素，添加change事件监听器');
    
    // 处理文件选择
    attachmentInput.addEventListener('change', function(e) {
        console.log('文件选择发生变化');
        const file = e.target.files[0];
        if (!file) {
            console.log('没有选择文件');
            previewContainer.classList.add('d-none');
            return;
        }

        console.log('选择的文件类型:', file.type);

        // 检查是否为图片文件
        if (file.type.startsWith('image/')) {
            console.log('选择的是图片文件，开始读取');
            const reader = new FileReader();
            
            reader.onload = function(e) {
                console.log('图片读取完成');
                imagePreview.src = e.target.result;
                previewContainer.classList.remove('d-none');
            };
            
            reader.onerror = function(e) {
                console.error('图片读取失败:', e);
                previewContainer.classList.add('d-none');
            };
            
            try {
                reader.readAsDataURL(file);
            } catch (error) {
                console.error('读取文件时发生错误:', error);
            }
        } else {
            console.log('不是图片文件，隐藏预览');
            previewContainer.classList.add('d-none');
        }
    });

    // 处理预览图片删除
    if (removePreviewBtn) {
        console.log('找到删除按钮，添加点击事件监听器');
        removePreviewBtn.addEventListener('click', function(e) {
            console.log('点击删除按钮');
            e.stopPropagation(); // 阻止事件冒泡
            attachmentInput.value = '';
            previewContainer.classList.add('d-none');
        });
    }
}

/**
 * 初始化编辑模态框的文件预览功能
 * @param {number} recordId 记录ID
 */
function initEditFilePreview(recordId) {
    const attachmentInput = document.getElementById(`editAttachment${recordId}`);
    const previewContainer = document.getElementById(`editPreviewContainer${recordId}`);
    const imagePreview = document.getElementById(`editImagePreview${recordId}`);
    const removePreviewBtn = document.getElementById(`editRemovePreview${recordId}`);
    const currentImage = document.querySelector(`#editModal${recordId} .preview-image-container img`);

    if (!attachmentInput) return;

    // 处理文件选择
    attachmentInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        // 如果没有选择文件，隐藏新图片预览，显示原有图片（如果有）
        if (!file) {
            previewContainer.classList.add('d-none');
            if (currentImage && currentImage.parentElement) {
                currentImage.parentElement.parentElement.classList.remove('d-none');
            }
            return;
        }

        // 检查是否为图片文件
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                previewContainer.classList.remove('d-none');
                // 隐藏原有图片预览
                if (currentImage && currentImage.parentElement) {
                    currentImage.parentElement.parentElement.classList.add('d-none');
                }
            };
            
            reader.onerror = function(e) {
                previewContainer.classList.add('d-none');
                // 显示原有图片预览
                if (currentImage && currentImage.parentElement) {
                    currentImage.parentElement.parentElement.classList.remove('d-none');
                }
                console.error('图片读取失败:', e);
            };
            
            try {
                reader.readAsDataURL(file);
            } catch (error) {
                console.error('读取文件时发生错误:', error);
            }
        } else {
            previewContainer.classList.add('d-none');
            // 显示原有图片预览
            if (currentImage && currentImage.parentElement) {
                currentImage.parentElement.parentElement.classList.remove('d-none');
            }
        }
    });

    // 处理预览图片删除
    if (removePreviewBtn) {
        removePreviewBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            attachmentInput.value = '';
            previewContainer.classList.add('d-none');
            // 显示原有图片预览
            if (currentImage && currentImage.parentElement) {
                currentImage.parentElement.parentElement.classList.remove('d-none');
            }
        });
    }
}

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    console.log('页面加载完成，开始初始化功能');
    
    // 初始化文件预览
    const currentPage = window.location.pathname;
    if (currentPage.includes('manage.php')) {
        initFilePreview();
    }

    // 初始化所有编辑模态框的文件预览和分类
    document.querySelectorAll('[id^="editModal"]').forEach(modal => {
        const recordId = modal.id.replace('editModal', '');
        initEditFilePreview(recordId);
        
        // 初始化编辑模态框的分类
        const typeSelect = document.getElementById(`editType${recordId}`);
        const editRecordBtn = modal.querySelector('.edit-record');
        
        if (typeSelect && editRecordBtn) {
            updateEditCategories(typeSelect.value, recordId);
            
            // 设置当前分类
            const categorySelect = document.getElementById(`editCategory${recordId}`);
            try {
                const recordData = JSON.parse(editRecordBtn.getAttribute('data-record'));
                if (categorySelect && recordData && recordData.category_id) {
                    setTimeout(() => {
                        categorySelect.value = recordData.category_id;
                    }, 500);
                }
            } catch (error) {
                console.error('解析记录数据失败:', error);
            }
        }

        // 添加模态框隐藏事件处理
        modal.addEventListener('hidden.bs.modal', function () {
            // 移除所有遮罩层
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            // 移除body上的modal相关类
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });

    // 图片预览功能
    document.querySelectorAll('.view-image').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const imagePath = this.getAttribute('data-image-path');
            const previewImage = document.getElementById('previewImage');
            if (previewImage) {
                previewImage.src = imagePath;
                const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                modal.show();
            }
        });
    });

    // 编辑按钮点击事件
    document.querySelectorAll('.edit-record').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            try {
                const recordData = JSON.parse(this.getAttribute('data-record'));
                const modalId = `editModal${recordData.id}`;
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            } catch (error) {
                console.error('解析记录数据失败:', error);
            }
        });
    });

    // 删除按钮点击事件
    document.querySelectorAll('.delete-record').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const recordId = this.getAttribute('data-id');
            const modalId = `deleteModal${recordId}`;
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    });

    // 附件预览功能
    const attachmentInput = document.getElementById('attachment');
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            const file = this.files[0];
            const previewContainer = document.getElementById('attachmentPreview');
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            if (file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `
                            <div class="mt-2">
                                <img src="${e.target.result}" class="img-fluid" style="max-height: 200px;" alt="预览图片">
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.innerHTML = `
                        <div class="mt-2">
                            <span class="text-muted">已选择文件: ${file.name}</span>
                        </div>
                    `;
                }
            }
        });
    }

    // 清除附件
    const clearAttachmentBtn = document.getElementById('clearAttachment');
    if (clearAttachmentBtn) {
        clearAttachmentBtn.addEventListener('click', function() {
            const attachmentInput = document.getElementById('attachment');
            const previewContainer = document.getElementById('attachmentPreview');
            const currentAttachment = document.getElementById('currentAttachment');
            
            if (attachmentInput) attachmentInput.value = '';
            if (previewContainer) previewContainer.innerHTML = '';
            if (currentAttachment) currentAttachment.value = '';
        });
    }

    // 初始化所有工具提示
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 初始化所有弹出框
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // 表格行悬停效果
    var tableRows = document.querySelectorAll('.table tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseout', function() {
            this.style.backgroundColor = '';
        });
    });
}); 