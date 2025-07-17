/**
 * JavaScript para el Dashboard de Taquilla (POS)
 * Portal Digital de Raqchi
 */

// Variables globales para el POS
let currentSale = {
    items: [],
    subtotal: 0,
    tax: 0,
    total: 0
};

let dailyStats = {
    ticketsSold: 0,
    totalRevenue: 0,
    cashTotal: 0,
    cardTotal: 0
};

// Productos disponibles
const products = {
    'general': {
        name: 'Entrada General',
        nameEn: 'General Admission',
        price: 15.00,
        category: 'ticket'
    },
    'student': {
        name: 'Entrada Estudiante',
        nameEn: 'Student Ticket',
        price: 7.50,
        category: 'ticket'
    },
    'group': {
        name: 'Entrada Grupo',
        nameEn: 'Group Ticket',
        price: 12.00,
        category: 'ticket'
    },
    'guide': {
        name: 'Guía Turístico',
        nameEn: 'Tour Guide',
        price: 25.00,
        category: 'service'
    }
};

// Inicialización cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    initializePOS();
});

function initializePOS() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    
    loadDailyStats();
    loadTransactions();
    setupEventListeners();
    
    // Actualizar estadísticas cada 5 minutos
    setInterval(loadDailyStats, 300000);
}

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleString('es-PE', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

function setupEventListeners() {
    // Event listeners para productos
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            const productId = this.dataset.product;
            const price = parseFloat(this.dataset.price);
            addItemToSale(productId, price);
        });
    });
    
    // Event listeners para navegación
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            showSection(section);
            updateActiveNavigation(this);
        });
    });
    
    // Event listeners para métodos de pago
    document.querySelectorAll('.btn-method').forEach(btn => {
        btn.addEventListener('click', function() {
            selectPaymentMethod(this.dataset.method);
        });
    });
    
    // Event listener para cálculo de cambio
    const amountInput = document.getElementById('amountReceived');
    if (amountInput) {
        amountInput.addEventListener('input', calculateChange);
    }
}

function addItemToSale(productId, price) {
    const existingItem = currentSale.items.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        const product = products[productId];
        if (product) {
            currentSale.items.push({
                id: productId,
                name: product.name,
                price: price,
                quantity: 1,
                category: product.category
            });
        }
    }
    
    updateSaleDisplay();
    showNotification(`${products[productId].name} agregado a la venta`, 'success');
}

function removeItemFromSale(index) {
    if (currentSale.items[index]) {
        const itemName = currentSale.items[index].name;
        currentSale.items.splice(index, 1);
        updateSaleDisplay();
        showNotification(`${itemName} eliminado de la venta`, 'info');
    }
}

function changeItemQuantity(index, delta) {
    if (currentSale.items[index]) {
        currentSale.items[index].quantity += delta;
        
        if (currentSale.items[index].quantity <= 0) {
            removeItemFromSale(index);
        } else {
            updateSaleDisplay();
        }
    }
}

function updateSaleDisplay() {
    const cartItems = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    const processBtn = document.getElementById('processBtn');
    
    if (!cartItems) return;
    
    if (currentSale.items.length === 0) {
        cartItems.innerHTML = '<div class="empty-cart"><p>No hay productos seleccionados</p></div>';
        if (processBtn) processBtn.disabled = true;
        currentSale.subtotal = 0;
        currentSale.tax = 0;
        currentSale.total = 0;
    } else {
        let html = '';
        let subtotal = 0;
        
        currentSale.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            html += `
                <div class="cart-item">
                    <div class="item-info">
                        <h5>${item.name}</h5>
                        <p>S/ ${item.price.toFixed(2)} x ${item.quantity}</p>
                    </div>
                    <div class="item-controls">
                        <button onclick="changeItemQuantity(${index}, -1)" class="qty-btn">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button onclick="changeItemQuantity(${index}, 1)" class="qty-btn">+</button>
                        <button onclick="removeItemFromSale(${index})" class="remove-btn">×</button>
                    </div>
                    <div class="item-total">S/ ${itemTotal.toFixed(2)}</div>
                </div>
            `;
        });
        
        cartItems.innerHTML = html;
        
        currentSale.subtotal = subtotal;
        currentSale.tax = 0; // Sin impuestos por ahora
        currentSale.total = subtotal + currentSale.tax;
        
        if (processBtn) processBtn.disabled = false;
    }
    
    // Actualizar totales en la UI
    if (subtotalEl) subtotalEl.textContent = `S/ ${currentSale.subtotal.toFixed(2)}`;
    if (taxEl) taxEl.textContent = `S/ ${currentSale.tax.toFixed(2)}`;
    if (totalEl) totalEl.textContent = `S/ ${currentSale.total.toFixed(2)}`;
}

function clearSale() {
    currentSale.items = [];
    updateSaleDisplay();
    showNotification('Venta limpiada', 'info');
}

function processSale() {
    if (currentSale.items.length === 0) {
        showNotification('No hay productos en la venta', 'warning');
        return;
    }
    
    showPaymentModal();
}

function showPaymentModal() {
    const modal = document.getElementById('paymentModal');
    const paymentItems = document.getElementById('paymentItems');
    const paymentTotal = document.getElementById('paymentTotal');
    
    if (!modal) return;
    
    // Llenar resumen de pago
    let html = '';
    currentSale.items.forEach(item => {
        const itemTotal = item.price * item.quantity;
        html += `<div class="payment-item">${item.name} x${item.quantity} - S/ ${itemTotal.toFixed(2)}</div>`;
    });
    
    if (paymentItems) paymentItems.innerHTML = html;
    if (paymentTotal) paymentTotal.textContent = `S/ ${currentSale.total.toFixed(2)}`;
    
    // Resetear formulario de pago
    const amountInput = document.getElementById('amountReceived');
    if (amountInput) {
        amountInput.value = '';
        amountInput.focus();
    }
    
    calculateChange();
    modal.classList.add('active');
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function selectPaymentMethod(method) {
    currentPaymentMethod = method;
    
    // Actualizar UI
    document.querySelectorAll('.btn-method').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.querySelector(`[data-method="${method}"]`).classList.add('active');
    
    // Si es tarjeta, llenar automáticamente el monto exacto
    if (method === 'card') {
        const amountInput = document.getElementById('amountReceived');
        if (amountInput) {
            amountInput.value = currentSale.total.toFixed(2);
            calculateChange();
        }
    }
}

function calculateChange() {
    const amountInput = document.getElementById('amountReceived');
    const changeElement = document.getElementById('changeAmount');
    const completeSaleBtn = document.getElementById('completeSaleBtn');
    
    if (!amountInput || !changeElement) return;
    
    const amountReceived = parseFloat(amountInput.value) || 0;
    const change = amountReceived - currentSale.total;
    
    changeElement.textContent = `S/ ${Math.max(0, change).toFixed(2)}`;
    
    // Habilitar/deshabilitar botón según el pago
    if (completeSaleBtn) {
        completeSaleBtn.disabled = amountReceived < currentSale.total;
    }
}

function completeSale() {
    const amountInput = document.getElementById('amountReceived');
    const amountReceived = parseFloat(amountInput.value) || 0;
    
    if (amountReceived < currentSale.total) {
        showNotification('El monto recibido es insuficiente', 'error');
        return;
    }
    
    // Simular procesamiento de venta
    // En una implementación real, aquí se enviaría a la API
    processSaleTransaction();
}

function processSaleTransaction() {
    // Crear objeto de transacción
    const transaction = {
        id: generateTransactionId(),
        timestamp: new Date().toISOString(),
        items: [...currentSale.items],
        subtotal: currentSale.subtotal,
        tax: currentSale.tax,
        total: currentSale.total,
        paymentMethod: currentPaymentMethod,
        amountReceived: parseFloat(document.getElementById('amountReceived').value),
        change: parseFloat(document.getElementById('amountReceived').value) - currentSale.total,
        cashier: 'Usuario Actual' // En implementación real obtener del usuario logueado
    };
    
    // Simular guardado en base de datos
    saveTransaction(transaction);
    
    // Actualizar estadísticas
    updateDailyStats(transaction);
    
    // Mostrar confirmación y limpiar venta
    showNotification('Venta procesada exitosamente', 'success');
    printReceipt(transaction);
    
    // Limpiar y cerrar
    clearSale();
    closePaymentModal();
}

function generateTransactionId() {
    const date = new Date();
    const timestamp = date.getTime();
    return `TXN-${timestamp}`;
}

function saveTransaction(transaction) {
    // En implementación real, enviar a API
    console.log('Guardando transacción:', transaction);
    
    // Guardar en localStorage para demo
    let transactions = JSON.parse(localStorage.getItem('pos_transactions') || '[]');
    transactions.unshift(transaction);
    
    // Mantener solo las últimas 100 transacciones
    if (transactions.length > 100) {
        transactions = transactions.slice(0, 100);
    }
    
    localStorage.setItem('pos_transactions', JSON.stringify(transactions));
}

function updateDailyStats(transaction) {
    // Actualizar estadísticas del día
    dailyStats.ticketsSold += transaction.items.reduce((sum, item) => sum + item.quantity, 0);
    dailyStats.totalRevenue += transaction.total;
    
    if (transaction.paymentMethod === 'cash') {
        dailyStats.cashTotal += transaction.total;
    } else {
        dailyStats.cardTotal += transaction.total;
    }
    
    // Actualizar UI si estamos en la sección de reportes
    updateStatsDisplay();
}

function updateStatsDisplay() {
    const todayTickets = document.getElementById('todayTickets');
    const todayRevenue = document.getElementById('todayRevenue');
    const cashTotal = document.getElementById('cashTotal');
    const cardTotal = document.getElementById('cardTotal');
    
    if (todayTickets) todayTickets.textContent = dailyStats.ticketsSold;
    if (todayRevenue) todayRevenue.textContent = `S/ ${dailyStats.totalRevenue.toFixed(2)}`;
    if (cashTotal) cashTotal.textContent = `S/ ${dailyStats.cashTotal.toFixed(2)}`;
    if (cardTotal) cardTotal.textContent = `S/ ${dailyStats.cardTotal.toFixed(2)}`;
}

function printReceipt(transaction) {
    // Simular impresión de recibo
    console.log('Imprimiendo recibo:', transaction);
    // En implementación real, integrar con impresora de tickets
}

function showSection(sectionName) {
    // Ocultar todas las secciones
    document.querySelectorAll('.pos-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Mostrar sección seleccionada
    const targetSection = document.getElementById(sectionName + '-section');
    if (targetSection) {
        targetSection.classList.add('active');
        
        // Cargar datos específicos de la sección
        switch(sectionName) {
            case 'transactions':
                loadTransactions();
                break;
            case 'reports':
                loadDailyStats();
                break;
        }
    }
}

function updateActiveNavigation(activeLink) {
    // Actualizar navegación activa
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    activeLink.parentElement.classList.add('active');
}

function loadTransactions() {
    const transactionsBody = document.getElementById('transactionsBody');
    if (!transactionsBody) return;
    
    // Cargar desde localStorage para demo
    const transactions = JSON.parse(localStorage.getItem('pos_transactions') || '[]');
    
    if (transactions.length === 0) {
        transactionsBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay transacciones hoy</td></tr>';
        return;
    }
    
    let html = '';
    transactions.forEach(transaction => {
        const time = new Date(transaction.timestamp).toLocaleTimeString('es-PE');
        const itemsCount = transaction.items.reduce((sum, item) => sum + item.quantity, 0);
        
        html += `
            <tr>
                <td>${time}</td>
                <td>${transaction.id}</td>
                <td>${itemsCount} productos</td>
                <td>S/ ${transaction.total.toFixed(2)}</td>
                <td>${transaction.paymentMethod === 'cash' ? 'Efectivo' : 'Tarjeta'}</td>
                <td>
                    <button class="btn btn-sm btn-outline" onclick="viewTransaction('${transaction.id}')">Ver</button>
                </td>
            </tr>
        `;
    });
    
    transactionsBody.innerHTML = html;
}

function loadDailyStats() {
    // Cargar estadísticas del día desde localStorage para demo
    const transactions = JSON.parse(localStorage.getItem('pos_transactions') || '[]');
    const today = new Date().toDateString();
    
    const todayTransactions = transactions.filter(t => 
        new Date(t.timestamp).toDateString() === today
    );
    
    // Resetear estadísticas
    dailyStats = {
        ticketsSold: 0,
        totalRevenue: 0,
        cashTotal: 0,
        cardTotal: 0
    };
    
    // Calcular estadísticas
    todayTransactions.forEach(transaction => {
        dailyStats.ticketsSold += transaction.items.reduce((sum, item) => sum + item.quantity, 0);
        dailyStats.totalRevenue += transaction.total;
        
        if (transaction.paymentMethod === 'cash') {
            dailyStats.cashTotal += transaction.total;
        } else {
            dailyStats.cardTotal += transaction.total;
        }
    });
    
    updateStatsDisplay();
}

function viewTransaction(transactionId) {
    // Mostrar detalles de transacción
    const transactions = JSON.parse(localStorage.getItem('pos_transactions') || '[]');
    const transaction = transactions.find(t => t.id === transactionId);
    
    if (transaction) {
        alert(`Transacción: ${transaction.id}\nTotal: S/ ${transaction.total.toFixed(2)}\nProductos: ${transaction.items.length}`);
        // En implementación real, mostrar modal con detalles completos
    }
}

function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Aplicar estilos
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Funciones para manejo de caja registradora
function openCashRegister() {
    showNotification('Caja registradora abierta', 'success');
    updateRegisterStatus('open');
}

function countCash() {
    const amount = prompt('Ingrese el monto contado:');
    if (amount && !isNaN(amount)) {
        showNotification(`Efectivo contado: S/ ${parseFloat(amount).toFixed(2)}`, 'info');
        updateCurrentCash(parseFloat(amount));
    }
}

function closeCashRegister() {
    const confirmClose = confirm('¿Está seguro de cerrar la caja registradora?');
    if (confirmClose) {
        showNotification('Caja registradora cerrada', 'info');
        updateRegisterStatus('closed');
    }
}

function updateRegisterStatus(status) {
    const statusElement = document.getElementById('registerStatus');
    if (statusElement) {
        statusElement.textContent = status === 'open' ? 'Abierta' : 'Cerrada';
        statusElement.className = `status ${status === 'open' ? 'available' : 'closed'}`;
    }
}

function updateCurrentCash(amount) {
    const cashElement = document.getElementById('currentCash');
    if (cashElement) {
        cashElement.textContent = `S/ ${amount.toFixed(2)}`;
    }
}

// Variables globales
let currentPaymentMethod = 'cash';
