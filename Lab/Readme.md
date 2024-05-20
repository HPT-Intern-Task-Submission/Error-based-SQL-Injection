How to run this lab
-----
### Set up environment
- Download XAMPP
- Start Apache and SQL from the XAMPP control panel
### Set up database
- Go to `http://localhost/phpmyadmin`. 
- Create a database named `vulnerable_db`. 
- Import the following SQL script:
```
CREATE DATABASE IF NOT EXISTS vulnerable_db;

USE vulnerable_db;

DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

-- Create roles table
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
);

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

-- Create user_roles table
CREATE TABLE user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

-- Insert sample data
INSERT INTO roles (role_name) VALUES ('admin'), ('editor'), ('viewer');

INSERT INTO users (username, password, email, role_id) VALUES
('admin', 'adminpass', 'admin@example.com', 1),
('editor', 'editorpass', 'editor@example.com', 2),
('viewer', 'viewerpass', 'viewer@example.com', 3);

INSERT INTO user_roles (user_id, role_id) VALUES (1, 1), (2, 2), (3, 3);

```
### Set up the web page
- Place the `sql_injection` folder in `C:\xampp\htdocs`
### Run the lab
- Enter the URL: `http://localhost/index.html` to access the lab
