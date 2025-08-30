document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('profile.html')) {
        initProfilePage();
    }
});

function initProfilePage() {
    loadProfile();
    setupProfileEventListeners();
}

function setupProfileEventListeners() {
    // Profile form submission
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', handleProfileUpdate);
    }
}

async function loadProfile() {
    try {
        const profileData = await apiRequest('/api/profile.php');
        displayProfile(profileData);
    } catch (error) {
        console.error('Error loading profile:', error);
        showNotification('Failed to load profile', 'error');
    }
}

function displayProfile(profileData) {
    const { user, stats } = profileData;
    
    // Display user info
    document.getElementById('profileName').textContent = user.name;
    document.getElementById('profileEmail').textContent = user.email;
    document.getElementById('memberSince').textContent = formatDate(user.created_at);
    
    // Fill form fields
    document.getElementById('profileNameInput').value = user.name;
    document.getElementById('profileEmailInput').value = user.email;
    
    // Display stats
    document.getElementById('totalTasksStat').textContent = stats.tasks.total_tasks;
    document.getElementById('completedTasksStat').textContent = stats.tasks.completed_tasks;
    document.getElementById('totalTransactionsStat').textContent = stats.transactions.total_transactions;
    document.getElementById('totalIncomeStat').textContent = formatCurrency(stats.transactions.total_income);
    document.getElementById('totalExpensesStat').textContent = formatCurrency(stats.transactions.total_expenses);
    document.getElementById('achievementsStat').textContent = stats.achievements.total_achievements;
    
    // Calculate completion rate
    const completionRate = stats.tasks.total_tasks > 0 
        ? Math.round((stats.tasks.completed_tasks / stats.tasks.total_tasks) * 100)
        : 0;
    document.getElementById('completionRateStat').textContent = `${completionRate}%`;
}

async function handleProfileUpdate(e) {
    e.preventDefault();
    
    const name = document.getElementById('profileNameInput').value;
    
    try {
        await apiRequest('/api/profile.php', {
            method: 'PUT',
            body: JSON.stringify({ name: name })
        });
        
        showNotification('Profile updated successfully', 'success');
        
        // Update displayed name
        document.getElementById('profileName').textContent = name;
        
        // Update user data in localStorage if needed
        const userData = getFromStorage('userData');
        if (userData) {
            userData.name = name;
            saveToStorage('userData', userData);
        }
        
    } catch (error) {
        console.error('Error updating profile:', error);
        showNotification('Failed to update profile', 'error');
    }
}