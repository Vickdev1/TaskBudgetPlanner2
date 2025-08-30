// Budget page functionality
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('budget.html')) {
        initBudgetPage();
    }
});

function initBudgetPage() {
    loadTransactions();
    setupBudgetEventListeners();
}

function setupBudgetEventListeners() {
    // Add income/expense buttons
    const addIncomeBtn = document.getElementById('addIncomeBtn');
    const addExpenseBtn = document.getElementById('addExpenseBtn');
    
    if (addIncomeBtn) {
        addIncomeBtn.addEventListener('click', function() {
            openAddTransactionModal('income');
        });
    }
    
    if (addExpenseBtn) {
        addExpenseBtn.addEventListener('click', function() {
            openAddTransactionModal('expense');
        });
    }
    
    // Transaction form submission
    const transactionForm = document.getElementById('transactionForm');
    if (transactionForm) {
        transactionForm.addEventListener('submit', handleTransactionSubmit);
    }
    
    // Filter changes
    const typeFilter = document.getElementById('typeFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const monthFilter = document.getElementById('monthFilter');
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterTransactions);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterTransactions);
    }
    
    if (monthFilter) {
        monthFilter.addEventListener('change', filterTransactions);
        
        // Set default value to current month
        const now = new Date();
        monthFilter.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    }
}

async function loadTransactions() {
    try {
        // In a real app, this would be an API call
        // const transactions = await apiRequest('/api/transactions');
        
        // For demo purposes, we'll use mock data
        const mockTransactions = [
            {
                id: 1,
                amount: 2500,
                description: 'Monthly Salary',
                category: 'salary',
                type: 'income',
                date: '2023-12-05'
            },
            {
                id: 2,
                amount: 150,
                description: 'Freelance Project',
                category: 'freelance',
                type: 'income',
                date: '2023-12-08'
            },
            {
                id: 3,
                amount: 85.50,
                description: 'Grocery Shopping',
                category: 'food',
                type: 'expense',
                date: '2023-12-10'
            },
            {
                id: 4,
                amount: 45.00,
                description: 'Gasoline',
                category: 'transport',
                type: 'expense',
                date: '2023-12-11'
            }
        ];
        
        displayTransactions(mockTransactions);
        updateBudgetStats(mockTransactions);
    } catch (error) {
        console.error('Error loading transactions:', error);
        showNotification('Failed to load transactions', 'error');
    }
}

function displayTransactions(transactions) {
    const transactionsList = document.getElementById('transactionsList');
    if (!transactionsList) return;
    
    if (transactions.length === 0) {
        transactionsList.innerHTML = `
            <div class="empty-state">
                <p>No transactions found. Add your first transaction to get started!</p>
                <div>
                    <button class="btn btn-primary" id="addFirstIncome">Add Income</button>
                    <button class="btn btn-secondary" id="addFirstExpense">Add Expense</button>
                </div>
            </div>
        `;
        
        document.getElementById('addFirstIncome').addEventListener('click', function() {
            openAddTransactionModal('income');
        });
        
        document.getElementById('addFirstExpense').addEventListener('click', function() {
            openAddTransactionModal('expense');
        });
        
        return;
    }
    
    transactionsList.innerHTML = transactions.map(transaction => `
        <div class="transaction-item">
            <div class="transaction-info">
                <div class="transaction-amount amount-${transaction.type}">
                    ${transaction.type === 'expense' ? '-' : '+'} ${formatCurrency(transaction.amount)}
                </div>
                <div class="transaction-details">
                    <span>${transaction.description}</span>
                    <span class="transaction-category">${transaction.category}</span>
                    <span>${formatDate(transaction.date)}</span>
                </div>
            </div>
            <div class="transaction-actions">
                <button class="task-btn btn-edit" data-id="${transaction.id}">Edit</button>
                <button class="task-btn btn-delete" data-id="${transaction.id}">Delete</button>
            </div>
        </div>
    `).join('');
    
    // Add event listeners to transaction buttons
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.getAttribute('data-id');
            openEditTransactionModal(transactionId);
        });
    });
    
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.getAttribute('data-id');
            deleteTransaction(transactionId);
        });
    });
}

function updateBudgetStats(transactions) {
    const totalIncome = transactions
        .filter(t => t.type === 'income')
        .reduce((sum, t) => sum + t.amount, 0);
    
    const totalExpenses = transactions
        .filter(t => t.type === 'expense')
        .reduce((sum, t) => sum + t.amount, 0);
    
    const totalBalance = totalIncome - totalExpenses;
    
    document.getElementById('totalBalance').textContent = formatCurrency(totalBalance);
    document.getElementById('totalIncome').textContent = formatCurrency(totalIncome);
    document.getElementById('totalExpenses').textContent = formatCurrency(totalExpenses);
}

function openAddTransactionModal(type) {
    const modal = document.getElementById('transactionModal');
    const modalTitle = document.getElementById('modalTitle');
    const transactionForm = document.getElementById('transactionForm');
    const transactionType = document.getElementById('transactionType');
    
    modalTitle.textContent = `Add ${type.charAt(0).toUpperCase() + type.slice(1)}`;
    transactionType.value = type;
    transactionForm.reset();
    document.getElementById('transactionId').value = '';
    
    // Set default date to today
    document.getElementById('transactionDate').value = new Date().toISOString().split('T')[0];
    
    // Pre-select category based on type
    const categorySelect = document.getElementById('transactionCategory');
    if (type === 'income') {
        categorySelect.value = 'salary';
    } else {
        categorySelect.value = 'food';
    }
    
    openModal('transactionModal');
}

function openEditTransactionModal(transactionId) {
    // In a real app, we would fetch the transaction details
    // For now, we'll just open the modal with the transactionId
    const modal = document.getElementById('transactionModal');
    const modalTitle = document.getElementById('modalTitle');
    
    modalTitle.textContent = 'Edit Transaction';
    document.getElementById('transactionId').value = transactionId;
    
    // Here we would populate the form with transaction data
    // For demo, we'll just open the modal
    openModal('transactionModal');
}

async function handleTransactionSubmit(e) {
    e.preventDefault();
    
    const transactionId = document.getElementById('transactionId').value;
    const transactionData = {
        amount: parseFloat(document.getElementById('transactionAmount').value),
        description: document.getElementById('transactionDescription').value,
        category: document.getElementById('transactionCategory').value,
        date: document.getElementById('transactionDate').value,
        type: document.getElementById('transactionType').value
    };
    
    try {
        if (transactionId) {
            // Update existing transaction
            // await apiRequest(`/api/transactions/${transactionId}`, {
            //     method: 'PUT',
            //     body: JSON.stringify(transactionData)
            // });
            showNotification('Transaction updated successfully', 'success');
        } else {
            // Create new transaction
            // await apiRequest('/api/transactions', {
            //     method: 'POST',
            //     body: JSON.stringify(transactionData)
            // });
            showNotification('Transaction created successfully', 'success');
        }
        
        closeModal(document.getElementById('transactionModal'));
        loadTransactions(); // Reload transactions
    } catch (error) {
        console.error('Error saving transaction:', error);
        showNotification('Failed to save transaction', 'error');
    }
}

async function deleteTransaction(transactionId) {
    if (!confirm('Are you sure you want to delete this transaction?')) {
        return;
    }
    
    try {
        // In a real app, this would be an API call
        // await apiRequest(`/api/transactions/${transactionId}`, {
        //     method: 'DELETE'
        // });
        
        showNotification('Transaction deleted successfully', 'success');
        loadTransactions(); // Reload transactions
    } catch (error) {
        console.error('Error deleting transaction:', error);
        showNotification('Failed to delete transaction', 'error');
    }
}

function filterTransactions() {
    // This would filter the transactions based on selected filters
    // For now, we'll just reload all transactions
    loadTransactions();
}