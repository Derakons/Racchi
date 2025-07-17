<?php
/**
 * Dashboard de Taquilla (POS) - Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../includes/bootstrap.php';

// Verificar autenticación y rol - requerir vendedor o admin
requireRole(['vendedor', 'admin']);

// Obtener datos del usuario desde la sesión
$userName = $_SESSION['user_name'] ?? 'Usuario';
$userRole = $_SESSION['user_role'] ?? 'vendedor';

includeHeader(rqText('ticket_office'), ['pos.css'], ['pos.js']);
?>

<div class="pos-container">
    <!-- Sidebar de navegación -->
    <aside class="pos-sidebar">
        <div class="sidebar-header">
            <h2><?= rqText('ticket_office') ?></h2>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                <span class="user-role"><?= rqText('cashier') ?></span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
                    <a href="#" data-section="sales" class="nav-link">
                        <i class="icon-ticket"></i>
                        <span><?= rqText('ticket_sales') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-section="services" class="nav-link">
                        <i class="icon-service"></i>
                        <span><?= rqText('services') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-section="transactions" class="nav-link">
                        <i class="icon-history"></i>
                        <span><?= rqText('transactions') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-section="reports" class="nav-link">
                        <i class="icon-chart"></i>
                        <span><?= rqText('daily_report') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-section="cash" class="nav-link">
                        <i class="icon-cash"></i>
                        <span><?= rqText('cash_register') ?></span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="<?= SITE_URL ?>/api/logout.php?redirect=1" class="logout-btn" title="<?= rqText('logout') ?>">
                <i class="fas fa-sign-out-alt"></i>
                <span><?= rqText('logout') ?></span>
            </a>
        </div>
    </aside>

    <!-- Contenido principal -->
    <main class="pos-main">
        <!-- Sección de ventas de tickets -->
        <section id="sales-section" class="pos-section active">
            <div class="section-header">
                <h1><?= rqText('ticket_sales') ?></h1>
                <div class="current-time" id="currentTime"></div>
            </div>

            <div class="sales-grid">
                <!-- Panel de productos -->
                <div class="products-panel">
                    <h3><?= rqText('available_tickets') ?></h3>
                    <div class="products-grid">
                        <div class="product-card" data-product="general" data-price="15.00">
                            <div class="product-image">
                                <img src="<?= SITE_URL ?>/assets/images/tickets/entrada-general.jpg" alt="<?= rqText('general_ticket') ?>">
                            </div>
                            <div class="product-info">
                                <h4><?= rqText('general_ticket') ?></h4>
                                <p class="product-price">S/ 15.00</p>
                            </div>
                        </div>

                        <div class="product-card" data-product="student" data-price="7.50">
                            <div class="product-image">
                                <img src="<?= SITE_URL ?>/assets/images/tickets/entrada-estudiante.jpg" alt="<?= rqText('student_ticket') ?>">
                            </div>
                            <div class="product-info">
                                <h4><?= rqText('student_ticket') ?></h4>
                                <p class="product-price">S/ 7.50</p>
                            </div>
                        </div>

                        <div class="product-card" data-product="group" data-price="12.00">
                            <div class="product-image">
                                <img src="<?= SITE_URL ?>/assets/images/tickets/entrada-grupo.jpg" alt="<?= rqText('group_ticket') ?>">
                            </div>
                            <div class="product-info">
                                <h4><?= rqText('group_ticket') ?></h4>
                                <p class="product-price">S/ 12.00</p>
                            </div>
                        </div>

                        <div class="product-card" data-product="guide" data-price="25.00">
                            <div class="product-image">
                                <img src="<?= SITE_URL ?>/assets/images/services/guia.jpg" alt="<?= rqText('tour_guide') ?>">
                            </div>
                            <div class="product-info">
                                <h4><?= rqText('tour_guide') ?></h4>
                                <p class="product-price">S/ 25.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de carrito de compras -->
                <div class="cart-panel">
                    <h3><?= rqText('current_sale') ?></h3>
                    <div class="cart-items" id="cartItems">
                        <div class="empty-cart">
                            <p><?= rqText('no_items_selected') ?></p>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span><?= rqText('subtotal') ?>:</span>
                            <span id="subtotal">S/ 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span><?= rqText('tax') ?> (0%):</span>
                            <span id="tax">S/ 0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span><?= rqText('total') ?>:</span>
                            <span id="total">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="cart-actions">
                        <button class="btn btn-outline" onclick="clearCart()"><?= rqText('clear') ?></button>
                        <button class="btn btn-primary" onclick="processSale()" id="processBtn" disabled><?= rqText('process_sale') ?></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de servicios -->
        <section id="services-section" class="pos-section">
            <div class="section-header">
                <h1><?= rqText('services') ?></h1>
            </div>
            <div class="services-list">
                <div class="service-item">
                    <h4><?= rqText('tour_guides') ?></h4>
                    <p><?= rqText('available_guides') ?>: <span class="status available">3</span></p>
                    <button class="btn btn-secondary"><?= rqText('assign_guide') ?></button>
                </div>
                <div class="service-item">
                    <h4><?= rqText('transportation') ?></h4>
                    <p><?= rqText('available_vehicles') ?>: <span class="status available">2</span></p>
                    <button class="btn btn-secondary"><?= rqText('book_transport') ?></button>
                </div>
            </div>
        </section>

        <!-- Sección de transacciones -->
        <section id="transactions-section" class="pos-section">
            <div class="section-header">
                <h1><?= rqText('today_transactions') ?></h1>
                <div class="header-actions">
                    <select id="transactionFilter">
                        <option value="today"><?= rqText('today') ?></option>
                        <option value="week"><?= rqText('this_week') ?></option>
                        <option value="month"><?= rqText('this_month') ?></option>
                    </select>
                </div>
            </div>
            <div class="transactions-table">
                <table>
                    <thead>
                        <tr>
                            <th><?= rqText('time') ?></th>
                            <th><?= rqText('transaction_id') ?></th>
                            <th><?= rqText('items') ?></th>
                            <th><?= rqText('amount') ?></th>
                            <th><?= rqText('payment_method') ?></th>
                            <th><?= rqText('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody id="transactionsBody">
                        <!-- Las transacciones se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Sección de reportes -->
        <section id="reports-section" class="pos-section">
            <div class="section-header">
                <h1><?= rqText('daily_report') ?></h1>
            </div>
            <div class="reports-grid">
                <div class="report-card">
                    <h4><?= rqText('sales_summary') ?></h4>
                    <div class="report-stat">
                        <span class="stat-number" id="todayTickets">0</span>
                        <span class="stat-label"><?= rqText('tickets_sold') ?></span>
                    </div>
                    <div class="report-stat">
                        <span class="stat-number" id="todayRevenue">S/ 0.00</span>
                        <span class="stat-label"><?= rqText('total_revenue') ?></span>
                    </div>
                </div>
                
                <div class="report-card">
                    <h4><?= rqText('payment_methods') ?></h4>
                    <div class="payment-breakdown">
                        <div class="payment-item">
                            <span><?= rqText('cash') ?>:</span>
                            <span id="cashTotal">S/ 0.00</span>
                        </div>
                        <div class="payment-item">
                            <span><?= rqText('card') ?>:</span>
                            <span id="cardTotal">S/ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de caja -->
        <section id="cash-section" class="pos-section">
            <div class="section-header">
                <h1><?= rqText('cash_register') ?></h1>
            </div>
            <div class="cash-actions">
                <button class="btn btn-primary" onclick="openCashRegister()"><?= rqText('open_register') ?></button>
                <button class="btn btn-secondary" onclick="countCash()"><?= rqText('count_cash') ?></button>
                <button class="btn btn-outline" onclick="closeCashRegister()"><?= rqText('close_register') ?></button>
            </div>
            <div class="cash-status">
                <p><?= rqText('register_status') ?>: <span id="registerStatus" class="status closed"><?= rqText('closed') ?></span></p>
                <p><?= rqText('current_cash') ?>: <span id="currentCash">S/ 0.00</span></p>
            </div>
        </section>
    </main>
</div>

<!-- Modal de pago -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= rqText('process_payment') ?></h3>
            <button class="modal-close" onclick="closePaymentModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="payment-summary">
                <h4><?= rqText('sale_summary') ?></h4>
                <div id="paymentItems"></div>
                <div class="payment-total">
                    <strong><?= rqText('total_to_pay') ?>: <span id="paymentTotal">S/ 0.00</span></strong>
                </div>
            </div>
            
            <div class="payment-methods">
                <h4><?= rqText('payment_method') ?></h4>
                <div class="method-buttons">
                    <button class="btn btn-method active" data-method="cash">
                        <i class="icon-cash"></i>
                        <?= rqText('cash') ?>
                    </button>
                    <button class="btn btn-method" data-method="card">
                        <i class="icon-card"></i>
                        <?= rqText('card') ?>
                    </button>
                </div>
            </div>
            
            <div class="payment-input">
                <label for="amountReceived"><?= rqText('amount_received') ?>:</label>
                <input type="number" id="amountReceived" step="0.01" placeholder="0.00">
                <div class="change-amount">
                    <strong><?= rqText('change') ?>: <span id="changeAmount">S/ 0.00</span></strong>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closePaymentModal()"><?= rqText('cancel') ?></button>
            <button class="btn btn-primary" onclick="completeSale()" id="completeSaleBtn"><?= rqText('complete_sale') ?></button>
        </div>
    </div>
</div>

<script>
// Variables globales
let cart = [];
let currentPaymentMethod = 'cash';
let currentTotal = 0;

// Inicializar POS
document.addEventListener('DOMContentLoaded', function() {
    updateTime();
    setInterval(updateTime, 1000);
    loadTransactions();
    loadDailyReport();
    
    // Event listeners para productos
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            addToCart(this.dataset.product, parseFloat(this.dataset.price));
        });
    });
    
    // Event listeners para navegación
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showSection(this.dataset.section);
        });
    });
});

function updateTime() {
    const now = new Date();
    document.getElementById('currentTime').textContent = now.toLocaleString('es-PE');
}

function addToCart(productId, price) {
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: productId,
            name: getProductName(productId),
            price: price,
            quantity: 1
        });
    }
    
    updateCartDisplay();
}

function getProductName(productId) {
    const names = {
        'general': <?= json_encode(rqText('general_ticket')) ?>,
        'student': <?= json_encode(rqText('student_ticket')) ?>,
        'group': <?= json_encode(rqText('group_ticket')) ?>,
        'guide': <?= json_encode(rqText('tour_guide')) ?>
    };
    return names[productId] || productId;
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    const processBtn = document.getElementById('processBtn');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="empty-cart"><p><?= rqText('no_items_selected') ?></p></div>';
        processBtn.disabled = true;
    } else {
        let html = '';
        let subtotal = 0;
        
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            html += `
                <div class="cart-item">
                    <div class="item-info">
                        <h5>${item.name}</h5>
                        <p>S/ ${item.price.toFixed(2)} x ${item.quantity}</p>
                    </div>
                    <div class="item-controls">
                        <button onclick="changeQuantity(${index}, -1)" class="qty-btn">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button onclick="changeQuantity(${index}, 1)" class="qty-btn">+</button>
                        <button onclick="removeFromCart(${index})" class="remove-btn">×</button>
                    </div>
                    <div class="item-total">S/ ${itemTotal.toFixed(2)}</div>
                </div>
            `;
        });
        
        cartItems.innerHTML = html;
        
        const tax = 0; // Sin impuestos por ahora
        const total = subtotal + tax;
        
        subtotalEl.textContent = `S/ ${subtotal.toFixed(2)}`;
        taxEl.textContent = `S/ ${tax.toFixed(2)}`;
        totalEl.textContent = `S/ ${total.toFixed(2)}`;
        
        currentTotal = total;
        processBtn.disabled = false;
    }
}

function changeQuantity(index, delta) {
    if (cart[index]) {
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        updateCartDisplay();
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function clearCart() {
    cart = [];
    updateCartDisplay();
}

function processSale() {
    if (cart.length === 0) return;
    
    // Mostrar modal de pago
    document.getElementById('paymentModal').classList.add('active');
    
    // Llenar resumen de pago
    const paymentItems = document.getElementById('paymentItems');
    const paymentTotal = document.getElementById('paymentTotal');
    
    let html = '';
    cart.forEach(item => {
        html += `<div class="payment-item">${item.name} x${item.quantity} - S/ ${(item.price * item.quantity).toFixed(2)}</div>`;
    });
    
    paymentItems.innerHTML = html;
    paymentTotal.textContent = `S/ ${currentTotal.toFixed(2)}`;
    
    // Resetear input de pago
    document.getElementById('amountReceived').value = '';
    document.getElementById('changeAmount').textContent = 'S/ 0.00';
}

function showSection(sectionName) {
    // Ocultar todas las secciones
    document.querySelectorAll('.pos-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Mostrar sección seleccionada
    document.getElementById(sectionName + '-section').classList.add('active');
    
    // Actualizar navegación
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    document.querySelector(`[data-section="${sectionName}"]`).parentElement.classList.add('active');
}

// Funciones adicionales para completar la funcionalidad...
function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('active');
}

function completeSale() {
    // Aquí iría la lógica para completar la venta
    // Por ahora solo simulamos
    alert(<?= json_encode(rqText('sale_completed')) ?>);
    clearCart();
    closePaymentModal();
}

function loadTransactions() {
    // Cargar transacciones del día
    // Implementar llamada a API
}

function loadDailyReport() {
    // Cargar reporte diario
    // Implementar llamada a API
}
</script>

<?php includeFooter(); ?>
