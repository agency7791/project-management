# 🚀 Laravel Project Management System

A comprehensive project management tool built with **Laravel 12**, **Vue 3**, **Inertia.js**, and **MySQL**. Perfect for agencies, consultancies, and project-based businesses.

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green?style=flat-square&logo=vue.js)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue?style=flat-square&logo=mysql)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3-blue?style=flat-square&logo=tailwindcss)

## ✨ Features

### 🔐 **Authentication & Authorization**
- Laravel Breeze with Vue 3 + Inertia.js
- Role-based access control (Admin, Manager, Staff)
- Secure route protection and permissions

### 👥 **User & Team Management**
- User roles with specific permissions
- Team creation and member management
- Project-team assignments with roles (lead/member)

### 🏢 **Client & Project Management**
- Complete CRUD operations for clients
- Advanced project management with status tracking
- Budget and timeline management
- Priority levels and comprehensive filtering

### ⏱️ **Time Tracking System**
- **Live timer** with start/stop functionality
- Time entry CRUD with authorization policies
- Billable/non-billable hour tracking
- Custom hourly rates and duration calculation
- Advanced filtering and analytics dashboard

### 💬 **Real-time Team Chat**
- Project-specific chat rooms
- File sharing capabilities (images, documents)
- Message history and pagination
- Ready for Laravel Echo integration

### 📊 **Reports & Analytics**
- Comprehensive dashboard with statistics
- Timesheet reports with advanced filtering
- **CSV and PDF export** functionality
- Revenue calculations and billing insights
- Project summary reports with budget analysis

### 📁 **File Management**
- Secure file upload/download system
- Project file organization
- Access control based on team membership
- File metadata tracking

## 🛠️ Tech Stack

- **Backend**: Laravel 12, MySQL 8.0+, Spatie Laravel Permission
- **Frontend**: Vue 3, Inertia.js, TailwindCSS
- **Authentication**: Laravel Breeze
- **Real-time**: Laravel Echo + Pusher (configured)
- **Exports**: DomPDF, Maatwebsite Excel
- **Storage**: Local filesystem with secure access

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/agency7791/project-management.git
   cd project-management
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure MySQL database**
   ```sql
   CREATE DATABASE project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Update `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=project_management
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Create storage link**
   ```bash
   php artisan storage:link
   ```

7. **Start the application**
   ```bash
   # Terminal 1: Laravel server
   php artisan serve
   
   # Terminal 2: Vite dev server
   npm run dev
   ```

Visit: `http://localhost:8000`

## 👤 Demo Accounts

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Admin** | admin@projectmanagement.com | password | Full system access |
| **Manager** | manager@projectmanagement.com | password | Manage projects, teams, reports |
| **Staff** | john@projectmanagement.com | password | Time tracking, assigned projects |

## 🗄️ Database Schema

The system includes 10 comprehensive tables:

- **users** - Authentication with roles and hourly rates
- **clients** - Client management and contact information
- **projects** - Project details, budgets, and timelines
- **teams** - Team organization structure
- **team_members** - Team membership with roles
- **time_entries** - Time tracking with authorization policies
- **chat_rooms** - Project-specific communication
- **chat_messages** - Messages and file sharing
- **project_files** - Secure file management
- **permission_tables** - Spatie permission system

## 📱 Screenshots

### Dashboard Analytics
![Dashboard](https://via.placeholder.com/800x400/4F46E5/FFFFFF?text=Dashboard+Analytics)

### Time Tracking with Live Timer
![Time Tracking](https://via.placeholder.com/800x400/059669/FFFFFF?text=Time+Tracking+Timer)

### Project Management
![Projects](https://via.placeholder.com/800x400/DC2626/FFFFFF?text=Project+Management)

### Team Chat
![Chat](https://via.placeholder.com/800x400/7C3AED/FFFFFF?text=Team+Chat)

## 🔧 Configuration

### Production Setup
```bash
# Build for production
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue Configuration (Recommended)
```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:work
```

### Email Notifications
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

## 🔒 Security Features

- **Role-based access control** throughout the application
- **Input validation** and sanitization
- **CSRF protection** on all forms
- **Secure file handling** with access controls
- **Authorization policies** for all resources
- **24-hour edit window** for staff time entries

## 📚 Documentation

- [Setup Guide](SETUP.md) - Detailed installation instructions
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Guide](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For support and questions:
- Check the [Setup Guide](SETUP.md)
- Review the troubleshooting section
- Open an issue on GitHub

---

**Built with ❤️ for modern project management**