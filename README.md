 Features
📋 Task Management
Create & Organize Tasks: Add tasks with titles, descriptions, categories, and priorities

Smart Filtering: Filter tasks by category, status, and priority levels

Progress Tracking: Visual completion tracking with streaks and statistics

Due Date Management: Set and track task deadlines

💰 Budget Planning
Income & Expense Tracking: Comprehensive financial transaction management

Category-based Organization: Categorize transactions for better insights

Real-time Balance Calculation: Automatic calculation of total balance, income, and expenses

Monthly Filtering: View transactions by specific months

🏆 Gamification System
Achievement Badges: Earn rewards for completing tasks and budget goals

Streak Tracking: Maintain daily task completion streaks

Progress Milestones: Unlock achievements at various progress levels

Motivational Rewards: Stay motivated with visual progress indicators

👤 User Management
Secure Authentication: JWT-based login and registration system

Personal Profiles: Individual user dashboards with statistics

Progress Analytics: Comprehensive overview of user activity and achievements

🛠 Tech Stack
Frontend
HTML5 - Semantic markup and structure

CSS3 - Modern styling with Flexbox/Grid layouts

Vanilla JavaScript - ES6+ features for interactivity

Responsive Design - Mobile-first approach

Backend
PHP 8.2 - RESTful API with object-oriented programming

PostgreSQL - Robust relational database

Nginx - High-performance web server

Infrastructure
Docker - Containerization for easy deployment

Docker Compose - Multi-container orchestration

PGAdmin - Database management interface

📦 Installation & Setup
Prerequisites
Docker and Docker Compose installed on your system

Git for version control

Quick Start
Clone the Repository

bash
git clone https://github.com/yourusername/taskbudget-planner.git
cd taskbudget-planner
Start the Application

bash
docker-compose up -d --build
Access the Application

🌐 Frontend: http://localhost

🔧 Backend API: http://localhost:9000

🗄️ Database: localhost:5432

📊 PGAdmin: http://localhost:5050

Default Login Credentials
Email: demo@taskbudget.com

Password: password123

🗂 Project Structure
text
taskbudget-planner/
├── frontend/                 # Frontend application
│   ├── css/                 # Stylesheets
│   │   ├── styles.css      # Main styles
│   │   └── components.css  # Component styles
│   ├── js/                 # JavaScript modules
│   │   ├── app.js          # Main application logic
│   │   ├── tasks.js        # Task management
│   │   ├── budget.js       # Budget management
│   │   ├── achievements.js # Gamification system
│   │   ├── profile.js      # User profiles
│   │   └── utils.js        # Utility functions
│   ├── *.html              # HTML pages
│   └── Dockerfile          # Frontend container config
├── backend/                 # Backend API
│   ├── api/                # PHP API endpoints
│   │   ├── login.php       # Authentication
│   │   ├── register.php    # User registration
│   │   ├── tasks.php       # Task CRUD operations
│   │   ├── budget.php      # Transaction management
│   │   ├── achievements.php # Gamification logic
│   │   └── profile.php     # User profiles
│   ├── config.php          # Database configuration
│   ├── init.sql            # Database schema & sample data
│   └── Dockerfile          # Backend container config
├── nginx/                  # Web server configuration
│   └── nginx.conf          # Nginx server config
├── scripts/                # Utility scripts
│   ├── build.sh            # Build script
│   ├── start.sh            # Start script
│   ├── stop.sh             # Stop script
│   ├── logs.sh             # Log viewing
│   └── restart.sh          # Restart script
├── docker-compose.yml      # Multi-container setup
└── README.md              # Project documentation
🔧 API Documentation
Authentication Endpoints
POST /api/login.php
Authenticate user and return JWT token.

Request:

json
{
  "email": "user@example.com",
  "password": "password123"
}
Response:

json
{
  "message": "Login successful",
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
POST /api/register.php
Register new user account.

Request:

json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "confirmPassword": "password123"
}
Task Management Endpoints
GET /api/tasks.php
Retrieve user tasks with optional filtering.

Query Parameters:

category - Filter by category (work, personal, health, education)

status - Filter by status (pending, completed)

POST /api/tasks.php
Create new task.

Request:

json
{
  "title": "Complete project",
  "description": "Finish the project documentation",
  "category": "work",
  "due_date": "2023-12-31",
  "priority": "high"
}
PUT /api/tasks.php?id={task_id}
Update existing task.

DELETE /api/tasks.php?id={task_id}
Delete task.

Budget Management Endpoints
GET /api/budget.php
Retrieve transactions with filtering.

Query Parameters:

type - Filter by type (income, expense)

category - Filter by category

month - Filter by month (YYYY-MM)

POST /api/budget.php
Create new transaction.

Request:

json
{
  "amount": 150.50,
  "description": "Grocery shopping",
  "category": "food",
  "type": "expense",
  "date": "2023-12-15"
}
🎮 Gamification System
Available Achievements
Achievement	Requirement	Description
🏆 Task Master	Complete 5+ tasks	Demonstrated consistent task completion
💰 Budget Master	Track budget for 7+ days	Maintained financial discipline
🔥 3-Day Streak	Complete tasks for 3 consecutive days	Built a productive habit
📊 Productive Planner	Complete 5+ tasks OR track budget for 7+ days	Overall productivity achievement
How to Earn Achievements
Complete Tasks: Each completed task brings you closer to task-based achievements

Track Transactions: Regular budget tracking unlocks financial achievements

Maintain Streaks: Consistent daily activity earns streak rewards

Mix Activities: Combine task and budget activities for comprehensive achievements

🐳 Docker Commands
Development
bash
# Start all services
docker-compose up -d --build

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Restart specific service
docker-compose restart backend
Database Management
bash
# Access PostgreSQL database
docker-compose exec db psql -U app_user -d taskbudget

# Backup database
docker-compose exec db pg_dump -U app_user taskbudget > backup.sql

# Restore database
docker-compose exec -T db psql -U app_user -d taskbudget < backup.sql
Utility Scripts
bash
# Build containers
./scripts/build.sh

# Start application
./scripts/start.sh

# Stop application
./scripts/stop.sh

# View logs
./scripts/logs.sh

# Restart application
./scripts/restart.sh
🔒 Security Features
JWT Authentication: Secure token-based authentication

Password Hashing: BCrypt password encryption

CORS Protection: Configured Cross-Origin Resource Sharing

SQL Injection Prevention: Prepared statements with PDO

Input Validation: Server-side data validation

XSS Protection: Output escaping and content security

📱 Responsive Design
The application is fully responsive and optimized for:

📱 Mobile devices (320px and up)

📟 Tablets (768px and up)

💻 Desktop computers (1024px and up)

🖥️ Large screens (1440px and up)

🚀 Performance Optimizations
Lazy Loading: Images and content loaded on demand

Efficient Queries: Optimized database queries with proper indexing

Caching Strategies: Browser caching for static assets

Minified Assets: Compressed CSS and JavaScript

CDN Ready: Static assets structured for CDN deployment

🧪 Testing
Manual Testing Checklist
User registration and login

Task creation, editing, and deletion

Task completion and filtering

Transaction creation and management

Budget filtering and statistics

Achievement unlocking

Profile management

Responsive design on multiple devices

Browser Compatibility
✅ Chrome 90+

✅ Firefox 88+

✅ Safari 14+

✅ Edge 90+

🔄 Development Workflow
Adding New Features
Create feature branch: git checkout -b feature/new-feature

Implement changes with proper documentation

Test thoroughly across different browsers

Submit pull request for review

Deploy to staging environment for testing

Code Standards
HTML: Semantic markup with proper accessibility

CSS: BEM methodology for component styling

JavaScript: ES6+ features with proper error handling

PHP: PSR-12 coding standards

SQL: Proper indexing and query optimization

 Troubleshooting
Common Issues
Docker Connection Error

bash
# Ensure Docker is running
sudo systemctl status docker
sudo systemctl start docker

# Check user permissions
sudo usermod -aG docker $USER
newgrp docker
Database Connection Issues

bash
# Check if database container is running
docker-compose ps

# View database logs
docker-compose logs db

# Restart database service
docker-compose restart db
Port Conflicts

bash
# Check what's using port 80, 9000, or 5432
sudo netstat -tulpn | grep :80
sudo netstat -tulpn | grep :9000
sudo netstat -tulpn | grep :5432
Logs and Debugging
bash
# View all service logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f db

# Check container status
docker-compose ps

# Access container shell
docker-compose exec backend sh
📈 Monitoring & Analytics
Built-in Analytics
Task completion rates and trends

Budget vs. actual spending analysis

Achievement progress tracking

User activity patterns

Streak maintenance statistics

Performance Metrics
Page load times

API response times

Database query performance

User engagement metrics

🤝 Contributing
We welcome contributions! Please see our Contributing Guidelines for details.

Development Setup
Fork the repository

Create your feature branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request
