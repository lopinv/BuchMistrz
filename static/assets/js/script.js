// WooCommerce API 配置
const API_CONFIG = {
    baseUrl: 'https://www.buchmistrz.pl/wp-json/wc/v3', // 替换为您的域名
    consumerKey: 'ck_75e405e4a60395d1b76aaebb1bf9cda39f53373a', // 替换为您的 Consumer Key
    consumerSecret: 'cs_7b4c64a2aa0681754d85a35100b70ddf562a33ca' // 替换为您的 Consumer Secret
};

// 全局变量
let allProducts = [];
let filteredProducts = [];
let currentPage = 1;
const productsPerPage = 12;
let categories = [];

// 页面初始化
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    initializeFilters();
});

// 初始化过滤器
function initializeFilters() {
    const categoryFilter = document.getElementById('category-filter');
    const sortFilter = document.getElementById('sort-filter');
    const searchInput = document.getElementById('search-input');

    // 分类过滤器事件
    categoryFilter.addEventListener('change', function() {
        applyFilters();
    });

    // 排序过滤器事件
    sortFilter.addEventListener('change', function() {
        applyFilters();
    });

    // 搜索输入事件
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });
}

// 从WooCommerce API加载产品
async function loadProducts() {
    try {
        showLoading(true);
        hideError();

        // 如果是演示模式（API未配置），加载示例数据
        if (API_CONFIG.baseUrl.includes('twoja-domena.com')) {
            await loadDemoProducts();
            return;
        }

        // 创建授权头
        const credentials = btoa(`${API_CONFIG.consumerKey}:${API_CONFIG.consumerSecret}`);
        const headers = {
            'Authorization': `Basic ${credentials}`,
            'Content-Type': 'application/json'
        };

        // 获取产品
        const response = await fetch(`${API_CONFIG.baseUrl}/products?per_page=100&status=publish`, {
            method: 'GET',
            headers: headers
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const products = await response.json();
        allProducts = products;
        filteredProducts = [...allProducts];

        // 加载分类
        await loadCategories();

        // 显示产品
        displayProducts();
        updatePagination();

    } catch (error) {
        console.error('加载产品时出错:', error);
        showError();
    } finally {
        showLoading(false);
    }
}

// 加载分类
async function loadCategories() {
    try {
        const credentials = btoa(`${API_CONFIG.consumerKey}:${API_CONFIG.consumerSecret}`);
        const headers = {
            'Authorization': `Basic ${credentials}`,
            'Content-Type': 'application/json'
        };

        const response = await fetch(`${API_CONFIG.baseUrl}/products/categories?per_page=100`, {
            method: 'GET',
            headers: headers
        });

        if (response.ok) {
            categories = await response.json();
            populateCategoryFilter();
        }
    } catch (error) {
        console.error('加载分类时出错:', error);
    }
}

// 加载演示产品（当API未配置时）
async function loadDemoProducts() {
    // 模拟API延迟
    await new Promise(resolve => setTimeout(resolve, 1000));

    allProducts = [
        {
            id: 1,
            name: 'Księgowość dla małych firm',
            price: '299.00',
            regular_price: '399.00',
            sale_price: '299.00',
            short_description: 'Kompleksowe rozwiązanie księgowe dla małych przedsiębiorstw. Zawiera wszystkie niezbędne narzędzia do prowadzenia księgowości.',
            images: [{ src: 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=400&h=300&fit=crop' }],
            permalink: '#',
            stock_status: 'instock',
            categories: [{ id: 1, name: 'Oprogramowanie' }]
        },
        {
            id: 2,
            name: 'System fakturowania online',
            price: '199.00',
            regular_price: '199.00',
            sale_price: '',
            short_description: 'Nowoczesny system do wystawiania i zarządzania fakturami online. Prosty w obsłudze i zgodny z polskim prawem.',
            images: [{ src: 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=400&h=300&fit=crop' }],
            permalink: '#',
            stock_status: 'instock',
            categories: [{ id: 1, name: 'Oprogramowanie' }]
        },
        {
            id: 3,
            name: 'Konsultacje księgowe',
            price: '150.00',
            regular_price: '200.00',
            sale_price: '150.00',
            short_description: 'Profesjonalne konsultacje księgowe z doświadczonym księgowym. Pomoc w rozwiązywaniu problemów podatkowych.',
            images: [{ src: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop' }],
            permalink: '#',
            stock_status: 'instock',
            categories: [{ id: 2, name: 'Usługi' }]
        },
        {
            id: 4,
            name: 'Kurs księgowości online',
            price: '499.00',
            regular_price: '499.00',
            sale_price: '',
            short_description: 'Kompleksowy kurs księgowości online. Nauka od podstaw z certyfikatem ukończenia.',
            images: [{ src: 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400&h=300&fit=crop' }],
            permalink: '#',
            stock_status: 'instock',
            categories: [{ id: 3, name: 'Szkolenia' }]
        },
        {
            id: 5,
            name: 'Poradnik podatkowy 2024',
            price: '89.00',
            regular_price: '89.00',
            sale_price: '',
            short_description: 'Aktualny poradnik podatkowy na rok 2024. Wszystkie zmiany w przepisach podatkowych.',
            images: [{ src: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop' }],
            permalink: '#',
            stock_status: 'outofstock',
            categories: [{ id: 4, name: 'Książki' }]
        },
        {
            id: 6,
            name: 'Backup księgowy',
            price: '79.00',
            regular_price: '99.00',
            sale_price: '79.00',
            short_description: 'Bezpieczne przechowywanie kopii zapasowych dokumentów księgowych w chmurze.',
            images: [{ src: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=400&h=300&fit=crop' }],
            permalink: '#',
            stock_status: 'instock',
            categories: [{ id: 1, name: 'Oprogramowanie' }]
        }
    ];

    categories = [
        { id: 1, name: 'Oprogramowanie', count: 3 },
        { id: 2, name: 'Usługi', count: 1 },
        { id: 3, name: 'Szkolenia', count: 1 },
        { id: 4, name: 'Książki', count: 1 }
    ];

    filteredProducts = [...allProducts];
    populateCategoryFilter();
    displayProducts();
    updatePagination();
}

// 填充分类过滤器
function populateCategoryFilter() {
    const categoryFilter = document.getElementById('category-filter');
    
    // 清除现有选项（除了第一个）
    while (categoryFilter.children.length > 1) {
        categoryFilter.removeChild(categoryFilter.lastChild);
    }
    
    categories.forEach(category => {
        if (category.count > 0) {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categoryFilter.appendChild(option);
        }
    });
}

// 应用过滤器
function applyFilters() {
    const categoryFilter = document.getElementById('category-filter').value;
    const sortFilter = document.getElementById('sort-filter').value;
    const searchQuery = document.getElementById('search-input').value.toLowerCase();

    // 重置到第一页
    currentPage = 1;

    // 过滤产品
    filteredProducts = allProducts.filter(product => {
        // 分类过滤
        if (categoryFilter && !product.categories.some(cat => cat.id == categoryFilter)) {
            return false;
        }

        // 搜索过滤
        if (searchQuery && !product.name.toLowerCase().includes(searchQuery)) {
            return false;
        }

        return true;
    });

    // 排序
    switch (sortFilter) {
        case 'name':
            filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'name-desc':
            filteredProducts.sort((a, b) => b.name.localeCompare(a.name));
            break;
        case 'price':
            filteredProducts.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
            break;
        case 'price-desc':
            filteredProducts.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
            break;
        case 'date':
        default:
            // 保持原始顺序（假设API返回最新的在前面）
            break;
    }

    displayProducts();
    updatePagination();
}

// 显示产品
function displayProducts() {
    const grid = document.getElementById('products-grid');
    const startIndex = (currentPage - 1) * productsPerPage;
    const endIndex = startIndex + productsPerPage;
    const productsToShow = filteredProducts.slice(startIndex, endIndex);

    grid.innerHTML = '';

    if (productsToShow.length === 0) {
        grid.innerHTML = '<p class="error-message">Nie znaleziono produktów spełniających kryteria wyszukiwania.</p>';
        return;
    }

    productsToShow.forEach((product, index) => {
        const productCard = createProductCard(product);
        productCard.style.animationDelay = `${index * 0.1}s`;
        grid.appendChild(productCard);
    });
}

// 创建产品卡片
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';

    // 获取第一张图片
    const imageUrl = product.images && product.images.length > 0 
        ? product.images[0].src 
        : 'https://via.placeholder.com/300x250?text=Brak+obrazu';

    // 格式化价格
    const price = parseFloat(product.price);
    const salePrice = parseFloat(product.sale_price);
    const regularPrice = parseFloat(product.regular_price);

    let priceHTML = '';
    if (salePrice && salePrice < regularPrice) {
        priceHTML = `
            <div class="product-price">
                <span style="text-decoration: line-through; color: var(--text-light); font-size: 1rem;">${regularPrice.toFixed(2)} zł</span>
                <span style="color: var(--primary-color);">${salePrice.toFixed(2)} zł</span>
            </div>
        `;
    } else {
        priceHTML = `<div class="product-price">${price.toFixed(2)} zł</div>`;
    }

    // 库存状态
    const stockStatus = product.stock_status === 'instock' ? 'in-stock' : 'out-of-stock';
    const stockText = product.stock_status === 'instock' ? 'Dostępny' : 'Brak w magazynie';

    // 产品描述（限制100字符）
    const description = product.short_description 
        ? product.short_description.replace(/<[^>]*>/g, '').substring(0, 100) + '...' 
        : 'Opis produktu niedostępny.';

    card.innerHTML = `
        ${salePrice && salePrice < regularPrice ? '<div class="sale-badge">PROMOCJA</div>' : ''}
        <div class="stock-status ${stockStatus}">${stockText}</div>
        <img src="${imageUrl}" alt="${product.name}" class="product-image" loading="lazy">
        <div class="product-info">
            <h3 class="product-title">${product.name}</h3>
            ${priceHTML}
            <p class="product-description">${description}</p>
            <div class="product-actions">
                <a href="${product.permalink}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i>
                    Kup teraz
                </a>
                <button class="btn btn-secondary" onclick="toggleWishlist(${product.id})" title="Dodaj do ulubionych">
                    <i class="far fa-heart"></i>
                </button>
            </div>
        </div>
    `;

    return card;
}

// 更新分页
function updatePagination() {
    const pagination = document.getElementById('pagination');
    const totalPages = Math.ceil(filteredProducts.length / productsPerPage);

    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let paginationHTML = '';

    // 上一页按钮
    paginationHTML += `
        <button ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i> Poprzednia
        </button>
    `;

    // 页码
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        paginationHTML += `<button onclick="changePage(1)">1</button>`;
        if (startPage > 2) {
            paginationHTML += `<span>...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <button ${i === currentPage ? 'class="active"' : ''} onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<span>...</span>`;
        }
        paginationHTML += `<button onclick="changePage(${totalPages})">${totalPages}</button>`;
    }

    // 下一页按钮
    paginationHTML += `
        <button ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">
            Następna <i class="fas fa-chevron-right"></i>
        </button>
    `;

    pagination.innerHTML = paginationHTML;
}

// 更改页面
function changePage(page) {
    const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displayProducts();
        updatePagination();
        
        // 滚动到产品区域
        document.getElementById('produkty').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

// 收藏夹功能
function toggleWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const index = wishlist.indexOf(productId);
    
    if (index > -1) {
        wishlist.splice(index, 1);
        showNotification('Usunięto z ulubionych', 'success');
    } else {
        wishlist.push(productId);
        showNotification('Dodano do ulubionych', 'success');
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    updateWishlistButtons();
}

// 更新收藏按钮状态
function updateWishlistButtons() {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    document.querySelectorAll('.btn-secondary').forEach(button => {
        const productId = parseInt(button.getAttribute('onclick').match(/\d+/)[0]);
        const icon = button.querySelector('i');
        
        if (wishlist.includes(productId)) {
            icon.className = 'fas fa-heart';
            button.style.background = 'var(--primary-color)';
            button.style.color = 'var(--secondary-color)';
        } else {
            icon.className = 'far fa-heart';
            button.style.background = 'var(--border-color)';
            button.style.color = 'var(--text-dark)';
        }
    });
}

// 显示通知
function showNotification(message, type = 'info') {
    // 创建通知元素
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'var(--success-color)' : 'var(--primary-color)'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: var(--shadow);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // 3秒后自动移除
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// 显示/隐藏加载状态
function showLoading(show) {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.style.display = show ? 'block' : 'none';
    }
}

// 显示错误状态
function showError() {
    const errorContainer = document.getElementById('error-container');
    const loading = document.getElementById('loading');
    
    if (loading) loading.style.display = 'none';
    if (errorContainer) errorContainer.style.display = 'block';
}

// 隐藏错误状态
function hideError() {
    const errorContainer = document.getElementById('error-container');
    if (errorContainer) errorContainer.style.display = 'none';
}

// 页面加载完成后更新收藏按钮状态
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(updateWishlistButtons, 1000);
});

// 添加CSS动画
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);