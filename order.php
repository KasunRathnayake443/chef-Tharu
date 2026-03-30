<?php
require_once 'config/db.php';

$categories = $pdo->query("
    SELECT id, name, sort_order
    FROM categories
    ORDER BY sort_order ASC, name ASC
")->fetchAll();

$foods = $pdo->query("
    SELECT
        foods.id,
        foods.category_id,
        foods.name,
        foods.description,
        foods.price,
        foods.image,
        categories.name AS category_name
    FROM foods
    INNER JOIN categories ON categories.id = foods.category_id
    WHERE foods.available = 1
    ORDER BY categories.sort_order ASC, foods.id DESC
")->fetchAll();

$menuData = [];
foreach ($foods as $food) {
    $menuData[] = [
        'id' => (int)$food['id'],
        'category_id' => (int)$food['category_id'],
        'name' => $food['name'],
        'description' => $food['description'] ?? '',
        'price' => (float)$food['price'],
        'image_url' => !empty($food['image']) ? 'uploads/foods/' . $food['image'] : ''
    ];
}

$categoryData = [];
foreach ($categories as $cat) {
    $categoryData[] = [
        'id' => (int)$cat['id'],
        'name' => $cat['name']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chef Tharu — Order Online</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Jost:wght@200;300;400;500&family=Cormorant+Infant:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/order.css">
</head>
<body>

<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<header class="site-header" id="siteHeader">
    <div class="header-inner">
        <a href="index.php" class="logo">
            <div class="logo-mark">CT</div>
            <div class="logo-text">
                <span class="logo-main">Chef Tharu</span>
                <span class="logo-tagline">Fine Dining · Colombo</span>
            </div>
        </a>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="index.php" class="nav-link"><span>01</span>Home</a></li>
                <li><a href="index.php#about" class="nav-link"><span>02</span>Story</a></li>
                <li><a href="index.php#menu" class="nav-link"><span>03</span>Menu</a></li>
                <li><a href="catering.html" class="nav-link"><span>04</span>Catering</a></li>
                <li><a href="order.php" class="nav-link active-page"><span>05</span>Order</a></li>
                <li><a href="index.php#contact" class="nav-link"><span>06</span>Reserve</a></li>
            </ul>
        </nav>

        <button class="cart-nav-btn" id="cartNavBtn" type="button" aria-label="Open cart">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-nav-count" id="cartNavCount">0</span>
        </button>

        <button class="hamburger" id="hamburger" aria-label="Toggle navigation" type="button">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<section class="order-hero">
    <div class="order-hero-bg"></div>
    <div class="order-hero-overlay"></div>

    <div class="orb orb-gold"></div>
    <div class="orb orb-ember"></div>

    <div class="order-hero-content">
        <p class="hero-eyebrow">
            <span class="eyebrow-line"></span>
            Fresh · Authentic · Sri Lankan
            <span class="eyebrow-line"></span>
        </p>
        <h1 class="order-hero-title">
            <span style="--i:0">Order</span>
            <span style="--i:1" class="title-accent">Fine</span>
            <span style="--i:2">Dining</span>
        </h1>
        <p class="order-hero-sub">
            Explore our signature menu, build your cart, and place your order in minutes.
        </p>
    </div>
</section>

<div class="marquee-strip">
    <div class="marquee-track">
        <span>Fresh to Order</span><span class="marquee-dot">✦</span>
        <span>Chef Specials</span><span class="marquee-dot">✦</span>
        <span>Sri Lankan Fine Dining</span><span class="marquee-dot">✦</span>
        <span>Fast Confirmation</span><span class="marquee-dot">✦</span>
        <span>Fresh to Order</span><span class="marquee-dot">✦</span>
        <span>Chef Specials</span><span class="marquee-dot">✦</span>
        <span>Sri Lankan Fine Dining</span><span class="marquee-dot">✦</span>
        <span>Fast Confirmation</span><span class="marquee-dot">✦</span>
    </div>
</div>

<section class="order-section">
    <div class="order-layout">

        <main class="menu-panel">
            <div class="menu-header-block" data-reveal="up">
                <p class="section-eyebrow">Curated Menu</p>
                <h2 class="section-title left-align">Choose Your<br><em>Favorites</em></h2>
                <div class="gold-divider"></div>
            </div>

            <div class="category-bar" id="categoryBar"></div>

            <div class="menu-loading" id="menuLoading" style="display:none">
                <div class="loading-spinner"></div>
                <p>Loading menu…</p>
            </div>

            <div class="menu-grid" id="menuGrid"></div>

            <div class="menu-empty" id="menuEmpty" style="display:none">
                <i class="fa-solid fa-bowl-food"></i>
                <p>No items in this category yet.</p>
            </div>
        </main>

        <aside class="cart-panel" id="cartPanel">
            <div class="cart-header">
                <h2 class="cart-title">
                    <i class="fa-solid fa-bag-shopping"></i>
                    Your Order
                </h2>

                <button class="cart-close-btn" id="cartCloseBtn" type="button" aria-label="Close cart">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="cart-empty">
                    <i class="fa-solid fa-plate-wheat"></i>
                    <p>Your cart is empty</p>
                    <span>Add items from the menu to begin</span>
                </div>
            </div>

            <div class="cart-summary" id="cartSummary" style="display:none">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="cartSubtotal">Rs. 0</span>
                </div>
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span id="cartTotal">Rs. 0</span>
                </div>
                <button class="btn-checkout" id="checkoutBtn" type="button">
                    <span>Proceed to Checkout</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </aside>

    </div>
</section>

<div class="cart-overlay" id="cartOverlay"></div>

<div class="modal-backdrop" id="checkoutBackdrop">
    <div class="checkout-modal">
        <div class="modal-header">
            <h2>Complete Your Order</h2>
            <button class="modal-close" id="checkoutCloseBtn" type="button" aria-label="Close checkout">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="modal-order-summary" id="modalOrderSummary"></div>
            <div class="modal-divider"></div>

            <form id="checkoutForm">
                <p class="form-section-label">Your Details</p>

                <div class="form-group">
                    <label for="custName">Full Name <span class="req">*</span></label>
                    <input type="text" id="custName" placeholder="Your full name" required>
                </div>

                <div class="form-group">
                    <label for="custPhone">Phone Number <span class="req">*</span></label>
                    <input type="tel" id="custPhone" placeholder="+94 XX XXX XXXX" required>
                </div>

                <div class="form-group">
                    <label for="custEmail">Email Address <span class="opt">(optional)</span></label>
                    <input type="email" id="custEmail" placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="custAddress">Delivery Address <span class="req">*</span></label>
                    <textarea id="custAddress" rows="3" placeholder="Street, city, postal code" required></textarea>
                </div>

                <div class="form-group">
                    <label for="custNotes">Special Instructions <span class="opt">(optional)</span></label>
                    <textarea id="custNotes" rows="2" placeholder="Allergies, spice level, etc."></textarea>
                </div>

                <button type="submit" class="btn-place-order" id="placeOrderBtn">
                    <span>Place Order</span>
                    <i class="fa-solid fa-check"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="confirmBackdrop">
    <div class="confirm-modal">
        <div class="confirm-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 class="confirm-title">Order Placed!</h2>
        <p class="confirm-sub">Thank you. Your order has been received and will be confirmed shortly.</p>
        <div class="confirm-details" id="confirmDetails"></div>
        <button class="btn-confirm-close" id="confirmCloseBtn" type="button">Back to Menu</button>
    </div>
</div>

<footer class="order-footer">
    <div class="footer-inner">
        <span class="footer-logo">Chef Tharu</span>
        <p>© 2026 Chef Tharu. Fine Dining · Colombo &amp; Kandy</p>
        <div class="footer-links">
            <a href="index.html">Home</a>
            <a href="catering.html">Catering</a>
            <a href="index.html#contact">Contact</a>
        </div>
    </div>
</footer>

<script>
const categories = <?php echo json_encode($categoryData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const menuItems = <?php echo json_encode($menuData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

let cart = [];
let activeCategory = 'all';

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function initHeader() {
    const header = document.getElementById('siteHeader');
    if (!header) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 80) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }, { passive: true });
}

function initCursor() {
    const dot = document.getElementById('cursorDot');
    const ring = document.getElementById('cursorRing');
    if (!dot || !ring || window.innerWidth <= 900) return;

    let mouseX = 0;
    let mouseY = 0;
    let ringX = 0;
    let ringY = 0;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        dot.style.left = mouseX + 'px';
        dot.style.top = mouseY + 'px';
    });

    const LERP = 0.1;

    (function animateRing() {
        ringX += (mouseX - ringX) * LERP;
        ringY += (mouseY - ringY) * LERP;
        ring.style.left = ringX + 'px';
        ring.style.top = ringY + 'px';
        requestAnimationFrame(animateRing);
    })();

    document.querySelectorAll('a, button, input, select, textarea').forEach(el => {
        el.addEventListener('mouseenter', () => {
            ring.style.width = '52px';
            ring.style.height = '52px';
            ring.style.borderColor = 'var(--gold)';
        });
        el.addEventListener('mouseleave', () => {
            ring.style.width = '36px';
            ring.style.height = '36px';
            ring.style.borderColor = 'rgba(201,166,84,.55)';
        });
    });
}

function initMobileNav() {
    const hamburger = document.getElementById('hamburger');
    const nav = document.getElementById('mainNav');
    if (!hamburger || !nav) return;

    hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        hamburger.classList.toggle('active');
        nav.classList.toggle('active');
        document.body.classList.toggle('nav-open', nav.classList.contains('active'));
    });

    nav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            nav.classList.remove('active');
            document.body.classList.remove('nav-open');
        });
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth > 900) return;
        if (!nav.classList.contains('active')) return;

        const clickedInsideNav = nav.contains(e.target);
        const clickedHamburger = hamburger.contains(e.target);

        if (!clickedInsideNav && !clickedHamburger) {
            hamburger.classList.remove('active');
            nav.classList.remove('active');
            document.body.classList.remove('nav-open');
        }
    });
}

function renderCategoryTabs() {
    const categoryBar = document.getElementById('categoryBar');
    categoryBar.innerHTML = `
        <button class="cat-tab ${activeCategory === 'all' ? 'active' : ''}" type="button" onclick="setCategory('all')">All</button>
        ${categories.map(cat => `
            <button class="cat-tab ${String(activeCategory) === String(cat.id) ? 'active' : ''}" type="button" onclick="setCategory('${cat.id}')">
                ${escapeHtml(cat.name)}
            </button>
        `).join('')}
    `;
}

function setCategory(categoryId) {
    activeCategory = categoryId;
    renderCategoryTabs();
    renderMenu();
}

function renderMenu() {
    const grid = document.getElementById('menuGrid');
    const menuEmpty = document.getElementById('menuEmpty');

    let filtered = menuItems;
    if (activeCategory !== 'all') {
        filtered = menuItems.filter(item => String(item.category_id) === String(activeCategory));
    }

    if (!filtered.length) {
        grid.innerHTML = '';
        menuEmpty.style.display = 'flex';
        return;
    }

    menuEmpty.style.display = 'none';

    grid.innerHTML = filtered.map(item => `
        <div class="menu-card">
            <div class="menu-card-image-wrap">
                ${item.image_url
                    ? `<img src="${item.image_url}" alt="${escapeHtml(item.name)}" class="menu-card-image">`
                    : `<div class="menu-card-image placeholder"></div>`
                }
            </div>
            <div class="menu-card-body">
                <div class="menu-card-top">
                    <h3>${escapeHtml(item.name)}</h3>
                    <span class="menu-price">Rs. ${Number(item.price).toLocaleString()}</span>
                </div>
                <p>${escapeHtml(item.description || '')}</p>
                <button class="menu-add-btn" type="button" onclick="addToCart(${item.id})">Add to Cart</button>
            </div>
        </div>
    `).join('');
}

function addToCart(id) {
    const item = menuItems.find(i => Number(i.id) === Number(id));
    if (!item) return;

    const existing = cart.find(i => Number(i.id) === Number(id));
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({
            id: item.id,
            name: item.name,
            price: Number(item.price),
            qty: 1
        });
    }

    updateCartUI();
}

function changeQty(id, diff) {
    const item = cart.find(i => Number(i.id) === Number(id));
    if (!item) return;

    item.qty += diff;
    if (item.qty <= 0) {
        cart = cart.filter(i => Number(i.id) !== Number(id));
    }

    updateCartUI();
}

function updateCartUI() {
    const cartItems = document.getElementById('cartItems');
    const cartSummary = document.getElementById('cartSummary');
    const cartNavCount = document.getElementById('cartNavCount');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTotal = document.getElementById('cartTotal');

    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    const total = cart.reduce((sum, item) => sum + (item.qty * item.price), 0);

    cartNavCount.textContent = totalQty;

    if (!cart.length) {
        cartItems.innerHTML = `
            <div class="cart-empty">
                <i class="fa-solid fa-plate-wheat"></i>
                <p>Your cart is empty</p>
                <span>Add items from the menu to begin</span>
            </div>
        `;
        cartSummary.style.display = 'none';
        return;
    }

    cartItems.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-info">
                <strong>${escapeHtml(item.name)}</strong>
                <span>Rs. ${Number(item.price).toLocaleString()}</span>
            </div>
            <div class="cart-item-actions">
                <button type="button" onclick="changeQty(${item.id}, -1)">−</button>
                <span>${item.qty}</span>
                <button type="button" onclick="changeQty(${item.id}, 1)">+</button>
            </div>
        </div>
    `).join('');

    cartSummary.style.display = 'block';
    cartSubtotal.textContent = `Rs. ${total.toLocaleString()}`;
    cartTotal.textContent = `Rs. ${total.toLocaleString()}`;
}

function toggleCart(forceState = null) {
    if (window.innerWidth > 900) return;

    const cartPanel = document.getElementById('cartPanel');
    const cartOverlay = document.getElementById('cartOverlay');

    const shouldOpen = forceState !== null
        ? forceState
        : !cartPanel.classList.contains('open');

    cartPanel.classList.toggle('open', shouldOpen);
    cartOverlay.classList.toggle('show', shouldOpen);
    document.body.classList.toggle('cart-open', shouldOpen);
}

function openCheckout() {
    if (!cart.length) {
        alert('Your cart is empty.');
        return;
    }

    const modalOrderSummary = document.getElementById('modalOrderSummary');
    const total = cart.reduce((sum, item) => sum + (item.qty * item.price), 0);

    modalOrderSummary.innerHTML = `
        <p class="form-section-label">Order Summary</p>
        ${cart.map(item => `
            <div class="summary-row">
                <span>${escapeHtml(item.name)} × ${item.qty}</span>
                <span>Rs. ${(item.qty * item.price).toLocaleString()}</span>
            </div>
        `).join('')}
        <div class="summary-row total-row">
            <span>Total</span>
            <span>Rs. ${total.toLocaleString()}</span>
        </div>
    `;

    document.getElementById('checkoutBackdrop').classList.add('open');
}

function closeCheckout() {
    document.getElementById('checkoutBackdrop').classList.remove('open');
}

async function placeOrder(event) {
    event.preventDefault();

    if (!cart.length) {
        alert('Your cart is empty.');
        return;
    }

    const placeOrderBtn = document.getElementById('placeOrderBtn');
    placeOrderBtn.disabled = true;

    const payload = {
        customer: {
            name: document.getElementById('custName').value.trim(),
            phone: document.getElementById('custPhone').value.trim(),
            email: document.getElementById('custEmail').value.trim(),
            address: document.getElementById('custAddress').value.trim(),
            notes: document.getElementById('custNotes').value.trim()
        },
        items: cart
    };

    try {
        const response = await fetch('order_submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!data.success) {
            alert(data.error || 'Failed to place order.');
            placeOrderBtn.disabled = false;
            return;
        }

        closeCheckout();

        document.getElementById('confirmDetails').innerHTML = `
            <p><strong>Order Ref:</strong> ${escapeHtml(data.order_ref)}</p>
            <p><strong>Total:</strong> Rs. ${Number(data.total).toLocaleString()}</p>
        `;

        document.getElementById('confirmBackdrop').classList.add('open');
        document.getElementById('checkoutForm').reset();

        cart = [];
        updateCartUI();
    } catch (error) {
        alert('Something went wrong while placing the order.');
    } finally {
        placeOrderBtn.disabled = false;
    }
}

function closeConfirm() {
    document.getElementById('confirmBackdrop').classList.remove('open');
}

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initCursor();
    initMobileNav();

    document.getElementById('cartNavBtn')?.addEventListener('click', () => toggleCart());
    document.getElementById('cartCloseBtn')?.addEventListener('click', () => toggleCart(false));
    document.getElementById('cartOverlay')?.addEventListener('click', () => toggleCart(false));
    document.getElementById('checkoutBtn')?.addEventListener('click', openCheckout);
    document.getElementById('checkoutCloseBtn')?.addEventListener('click', closeCheckout);
    document.getElementById('confirmCloseBtn')?.addEventListener('click', closeConfirm);
    document.getElementById('checkoutForm')?.addEventListener('submit', placeOrder);

    renderCategoryTabs();
    renderMenu();
    updateCartUI();
});
</script>
</body>
</html>