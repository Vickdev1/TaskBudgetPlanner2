document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('achievements.html')) {
        initAchievementsPage();
    }
});

function initAchievementsPage() {
    loadAchievements();
    setupAchievementsEventListeners();
}

function setupAchievementsEventListeners() {
    // Refresh button
    const refreshBtn = document.getElementById('refreshAchievements');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadAchievements);
    }
}

async function loadAchievements() {
    try {
        const achievements = await apiRequest('/api/achievements.php');
        displayAchievements(achievements);
        updateAchievementStats(achievements);
    } catch (error) {
        console.error('Error loading achievements:', error);
        showNotification('Failed to load achievements', 'error');
    }
}

function displayAchievements(achievements) {
    const achievementsList = document.getElementById('achievementsList');
    if (!achievementsList) return;
    
    if (achievements.length === 0) {
        achievementsList.innerHTML = `
            <div class="empty-state">
                <p>No achievements yet. Complete tasks and track your budget to earn achievements!</p>
            </div>
        `;
        return;
    }
    
    achievementsList.innerHTML = achievements.map(achievement => `
        <div class="achievement-item">
            <div class="achievement-icon">🏆</div>
            <div class="achievement-info">
                <h3>${achievement.name}</h3>
                <p>${achievement.description}</p>
                <small>Earned on ${formatDate(achievement.earned_at)}</small>
            </div>
        </div>
    `).join('');
}

function updateAchievementStats(achievements) {
    const totalAchievements = achievements.length;
    document.getElementById('totalAchievements').textContent = totalAchievements;
}

// Check for new achievements when tasks are completed or transactions added
async function checkForNewAchievements(action) {
    try {
        const response = await apiRequest('/api/achievements.php', {
            method: 'POST',
            body: JSON.stringify({ action: action })
        });
        
        if (response.new_achievements && response.new_achievements.length > 0) {
            response.new_achievements.forEach(achievementId => {
                showNotification('New achievement unlocked!', 'success');
            });
            
            // Reload achievements if on the achievements page
            if (window.location.pathname.includes('achievements.html')) {
                loadAchievements();
            }
        }
    } catch (error) {
        console.error('Error checking achievements:', error);
    }
}