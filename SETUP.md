# Laravel Project Management System - Setup Guide

## 🚀 Quick Start

This is a comprehensive project management system built with Laravel 12, Vue 3, and MySQL.

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2+** with required extensions
- **Composer** (latest version)
- **Node.js 18+** and npm
- **MySQL 8.0+** or MariaDB 10.3+
- **Git**

### Required PHP Extensions
```bash
# Check if extensions are installed
php -m | grep -E "(pdo|mysql|mbstring|openssl|tokenizer|xml|ctype|json|bcmath|fileinfo|gd)"
```

## 🗄️ Database Setup

### 1. Install MySQL (if not already installed)

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

**macOS (using Homebrew):**
```bash
brew install mysql
brew services start mysql
```

**Windows:**
Download and install from [MySQL Official Website](https://dev.mysql.com/downloads/mysql/)

### 2. Create Database and User

```sql
-- Login to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional, you can use root)
CREATE USER 'pm_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON project_management.* TO 'pm_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

## 🛠️ Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/agency7791/project-management.git
cd project-management
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database in .env
Edit the `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_management
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

### 6. Run Database Migrations and Seeders
```bash
# Run migrations
php artisan migrate

# Seed the database with demo data
php artisan db:seed
```

### 7. Create Storage Link
```bash
php artisan storage:link
```

### 8. Build Frontend Assets
```bash
# For development
npm run dev

# For production
npm run build
```

## 🚀 Running the Application

### Development Mode
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server (for hot reloading)
npm run dev
```

The application will be available at: `http://localhost:8000`

### Production Mode
```bash
# Build assets for production
npm run build

# Start Laravel server
php artisan serve --env=production
```

## 👤 Demo Accounts

After running the seeders, you can login with these accounts:

| Role | Email | Password | Permissions |
|------|-------|----------|-------------|
| **Admin** | admin@projectmanagement.com | password | Full system access |
| **Manager** | manager@projectmanagement.com | password | Manage projects, teams, reports |
| **Staff** | john@projectmanagement.com | password | Time tracking, assigned projects |

## 🔧 Configuration Options

### File Storage
The application stores uploaded files in `storage/app/public`. Make sure this directory is writable:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Queue Configuration (Optional)
For better performance with file uploads and notifications:

```bash
# In .env file
QUEUE_CONNECTION=database

# Run queue worker
php artisan queue:work
```

### Email Configuration (Optional)
Configure email settings in `.env` for notifications:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="Project Management System"
```

## 🔒 Security Considerations

### Production Deployment
1. **Environment**: Set `APP_ENV=production` and `APP_DEBUG=false`
2. **HTTPS**: Always use HTTPS in production
3. **Database**: Use strong passwords and limit database user privileges
4. **File Permissions**: Ensure proper file permissions (755 for directories, 644 for files)
5. **Backup**: Set up regular database backups

### Security Headers
Consider adding these headers to your web server configuration:
```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

## 🐛 Troubleshooting

### Common Issues

**1. Database Connection Error**
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u root -p -e "SELECT 1"
```

**2. Permission Errors**
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

**3. Composer Memory Issues**
```bash
# Increase PHP memory limit
php -d memory_limit=2G /usr/local/bin/composer install
```

**4. Node.js Build Errors**
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Log Files
Check these log files for debugging:
- Laravel: `storage/logs/laravel.log`
- Web Server: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- MySQL: `/var/log/mysql/error.log`

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [TailwindCSS Documentation](https://tailwindcss.com/docs)

## 🆘 Support

If you encounter any issues:

1. Check the troubleshooting section above
2. Review the log files for error messages
3. Ensure all prerequisites are properly installed
4. Verify database credentials and connectivity

## 🔄 Updates

To update the application:

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install
npm install

# Run any new migrations
php artisan migrate

# Rebuild assets
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

**Happy Project Managing! 🎉**