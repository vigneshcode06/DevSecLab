# VLab - Virtual Laboratory Environment

A responsive dark-themed frontend UI for a cybersecurity learning platform with PHP and MySQL integration.

## Features

- Dark mode with cyberpunk/tech aesthetics using neon blue and purple accent colors
- Responsive design with collapsible sidebar for mobile/tablet views
- User authentication system with PHP and MySQL
- Interactive dashboard with progress tracking
- Labs listing and filtering
- User profile management
- Achievement system
- Customizable user settings

## Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- Font Awesome for icons
- Google Fonts (Orbitron for headings, Roboto for body text)

## Database Setup

1. Create a MySQL database (you can use the SQL queries in `sql.txt`)
2. Update the database connection settings in `includes/config.php` with your database credentials

## Installation Instructions

1. Clone this repository to your local environment or web server
2. Import the SQL database using the queries in `sql.txt`
3. Update the database connection settings in `includes/config.php`
4. Ensure your web server has PHP installed (version 7.4+ recommended)
5. Make sure the web server has appropriate permissions to read/write to the project files
6. Access the website through your web server

## Database Configuration

Edit the `includes/config.php` file and update the following settings:

```php
$db_host = "localhost"; // Database host
$db_user = "your_username"; // MySQL username
$db_pass = "your_password"; // MySQL password
$db_name = "vlab_db"; // Database name
```

## Default Login

A default admin account is created when you import the SQL:
- Username: admin
- Password: Admin@123

## File Structure

```
vlab/
├── css/
│   └── style.css
├── includes/
│   ├── auth.php
│   ├── config.php
│   └── labs.php
├── js/
│   └── script.js
├── 404.php
├── dashboard.php
├── index.php
├── labs.php
├── logout.php
├── profile.php
├── signup.php
├── sql.txt
└── .htaccess
```

## Cyberpunk UI Features

- Neon glow effects on buttons and interactive elements
- Grid line animations in background
- Dark theme with high contrast
- Futuristic card layouts with gradient accents

## Credits

- Font Awesome for icons
- Google Fonts for typography
- Cyberpunk-inspired design elements