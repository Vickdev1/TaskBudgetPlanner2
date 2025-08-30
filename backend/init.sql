CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tasks (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    due_date DATE,
    priority VARCHAR(20) DEFAULT 'medium',
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255),
    category VARCHAR(50),
    type VARCHAR(20) CHECK (type IN ('income', 'expense')),
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE achievements (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    type VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (name, email, password) VALUES 
('Test User', 'test@taskbudget.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO tasks (user_id, title, description, category, due_date, priority, completed) VALUES
(1, 'Complete project proposal', 'Finish the project proposal for client meeting', 'work', '2023-12-15', 'high', false),
(1, 'Buy groceries', 'Milk, eggs, bread, fruits', 'personal', '2023-12-12', 'medium', true);

INSERT INTO transactions (user_id, amount, description, category, type, date) VALUES
(1, 2500.00, 'Monthly Salary', 'salary', 'income', '2023-12-05'),
(1, 85.50, 'Grocery Shopping', 'food', 'expense', '2023-12-10');
