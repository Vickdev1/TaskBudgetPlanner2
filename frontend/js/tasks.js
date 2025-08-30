// Tasks page functionality
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('tasks.html')) {
        initTasksPage();
    }
});

function initTasksPage() {
    loadTasks();
    setupTaskEventListeners();
}

function setupTaskEventListeners() {
    // Add task button
    const addTaskBtn = document.getElementById('addTaskBtn');
    if (addTaskBtn) {
        addTaskBtn.addEventListener('click', function() {
            openAddTaskModal();
        });
    }
    
    // Task form submission
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', handleTaskSubmit);
    }
    
    // Filter changes
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterTasks);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTasks);
    }
}

async function loadTasks() {
    try {
        // In a real app, this would be an API call
        // const tasks = await apiRequest('/api/tasks');
        
        // For demo purposes, we'll use mock data
        const mockTasks = [
            {
                id: 1,
                title: 'Complete project proposal',
                description: 'Finish the project proposal for client meeting',
                category: 'work',
                dueDate: '2023-12-15',
                priority: 'high',
                completed: false,
                createdAt: '2023-12-10'
            },
            {
                id: 2,
                title: 'Buy groceries',
                description: 'Milk, eggs, bread, fruits',
                category: 'personal',
                dueDate: '2023-12-12',
                priority: 'medium',
                completed: true,
                createdAt: '2023-12-09'
            },
            {
                id: 3,
                title: 'Gym workout',
                description: '1 hour cardio and weights',
                category: 'health',
                dueDate: '2023-12-11',
                priority: 'medium',
                completed: false,
                createdAt: '2023-12-10'
            }
        ];
        
        displayTasks(mockTasks);
        updateTaskStats(mockTasks);
    } catch (error) {
        console.error('Error loading tasks:', error);
        showNotification('Failed to load tasks', 'error');
    }
}

function displayTasks(tasks) {
    const tasksList = document.getElementById('tasksList');
    if (!tasksList) return;
    
    if (tasks.length === 0) {
        tasksList.innerHTML = `
            <div class="empty-state">
                <p>No tasks found. Add your first task to get started!</p>
                <button class="btn btn-primary" id="addFirstTask">Add Task</button>
            </div>
        `;
        
        document.getElementById('addFirstTask').addEventListener('click', openAddTaskModal);
        return;
    }
    
    tasksList.innerHTML = tasks.map(task => `
        <div class="task-item ${task.completed ? 'task-completed' : ''}">
            <div class="task-info">
                <div class="task-title">${task.title}</div>
                <div class="task-details">
                    <span class="task-category">${task.category}</span>
                    <span class="task-priority priority-${task.priority}">${task.priority}</span>
                    ${task.dueDate ? `<span>Due: ${formatDate(task.dueDate)}</span>` : ''}
                </div>
            </div>
            <div class="task-actions">
                <button class="task-btn btn-complete" data-id="${task.id}">
                    ${task.completed ? 'Undo' : 'Complete'}
                </button>
                <button class="task-btn btn-edit" data-id="${task.id}">Edit</button>
                <button class="task-btn btn-delete" data-id="${task.id}">Delete</button>
            </div>
        </div>
    `).join('');
    
    // Add event listeners to task buttons
    document.querySelectorAll('.btn-complete').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            toggleTaskComplete(taskId);
        });
    });
    
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            openEditTaskModal(taskId);
        });
    });
    
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            deleteTask(taskId);
        });
    });
}

function updateTaskStats(tasks) {
    const totalTasks = tasks.length;
    const completedTasks = tasks.filter(task => task.completed).length;
    
    // Calculate streak (for demo, we'll use a mock value)
    const currentStreak = 3; // This would be calculated based on completion dates
    
    document.getElementById('totalTasks').textContent = totalTasks;
    document.getElementById('completedTasks').textContent = completedTasks;
    document.getElementById('currentStreak').textContent = `${currentStreak} days`;
}

function openAddTaskModal() {
    const modal = document.getElementById('taskModal');
    const modalTitle = document.getElementById('modalTitle');
    const taskForm = document.getElementById('taskForm');
    
    modalTitle.textContent = 'Add New Task';
    taskForm.reset();
    document.getElementById('taskId').value = '';
    
    // Set default due date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('taskDueDate').value = tomorrow.toISOString().split('T')[0];
    
    openModal('taskModal');
}

function openEditTaskModal(taskId) {
    // In a real app, we would fetch the task details
    // For now, we'll just open the modal with the taskId
    const modal = document.getElementById('taskModal');
    const modalTitle = document.getElementById('modalTitle');
    
    modalTitle.textContent = 'Edit Task';
    document.getElementById('taskId').value = taskId;
    
    // Here we would populate the form with task data
    // For demo, we'll just open the modal
    openModal('taskModal');
}

async function handleTaskSubmit(e) {
    e.preventDefault();
    
    const taskId = document.getElementById('taskId').value;
    const taskData = {
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDescription').value,
        category: document.getElementById('taskCategory').value,
        dueDate: document.getElementById('taskDueDate').value,
        priority: document.getElementById('taskPriority').value
    };
    
    try {
        if (taskId) {
            // Update existing task
            // await apiRequest(`/api/tasks/${taskId}`, {
            //     method: 'PUT',
            //     body: JSON.stringify(taskData)
            // });
            showNotification('Task updated successfully', 'success');
        } else {
            // Create new task
            // await apiRequest('/api/tasks', {
            //     method: 'POST',
            //     body: JSON.stringify(taskData)
            // });
            showNotification('Task created successfully', 'success');
        }
        
        closeModal(document.getElementById('taskModal'));
        loadTasks(); // Reload tasks
    } catch (error) {
        console.error('Error saving task:', error);
        showNotification('Failed to save task', 'error');
    }
}

async function toggleTaskComplete(taskId) {
    try {
        // In a real app, this would be an API call
        // await apiRequest(`/api/tasks/${taskId}/toggle`, {
        //     method: 'PATCH'
        // });
        
        showNotification('Task status updated', 'success');
        loadTasks(); // Reload tasks
    } catch (error) {
        console.error('Error updating task:', error);
        showNotification('Failed to update task', 'error');
    }
}

async function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }
    
    try {
        // In a real app, this would be an API call
        // await apiRequest(`/api/tasks/${taskId}`, {
        //     method: 'DELETE'
        // });
        
        showNotification('Task deleted successfully', 'success');
        loadTasks(); // Reload tasks
    } catch (error) {
        console.error('Error deleting task:', error);
        showNotification('Failed to delete task', 'error');
    }
}

function filterTasks() {
    // This would filter the tasks based on selected filters
    // For now, we'll just reload all tasks
    loadTasks();
}
