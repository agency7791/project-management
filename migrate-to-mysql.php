<?php

/**
 * Migration script to help users migrate from SQLite to MySQL
 * Run this script after setting up MySQL database
 */

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

echo "🚀 Laravel Project Management - MySQL Migration Helper\n";
echo "====================================================\n\n";

// Check if .env file exists
if (!file_exists('.env')) {
    echo "❌ Error: .env file not found. Please copy .env.example to .env first.\n";
    exit(1);
}

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbConnection = $_ENV['DB_CONNECTION'] ?? 'sqlite';

if ($dbConnection !== 'mysql') {
    echo "⚠️  Warning: DB_CONNECTION is not set to 'mysql' in your .env file.\n";
    echo "Please update your .env file with MySQL configuration:\n\n";
    echo "DB_CONNECTION=mysql\n";
    echo "DB_HOST=127.0.0.1\n";
    echo "DB_PORT=3306\n";
    echo "DB_DATABASE=project_management\n";
    echo "DB_USERNAME=root\n";
    echo "DB_PASSWORD=your_password\n\n";
    
    $continue = readline("Do you want to continue anyway? (y/N): ");
    if (strtolower($continue) !== 'y') {
        echo "Migration cancelled.\n";
        exit(0);
    }
}

echo "📋 Pre-migration checklist:\n";
echo "✅ MySQL server is running\n";
echo "✅ Database 'project_management' exists\n";
echo "✅ Database user has proper permissions\n";
echo "✅ .env file is configured with MySQL settings\n\n";

$proceed = readline("Ready to proceed with migration? (y/N): ");
if (strtolower($proceed) !== 'y') {
    echo "Migration cancelled.\n";
    exit(0);
}

echo "\n🔄 Starting migration process...\n\n";

// Test database connection
try {
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver' => $_ENV['DB_CONNECTION'],
        'host' => $_ENV['DB_HOST'],
        'database' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ]);
    
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    // Test connection
    $capsule->getConnection()->getPdo();
    echo "✅ Database connection successful\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration and try again.\n";
    exit(1);
}

// Run migrations
echo "\n📦 Running migrations...\n";
$output = shell_exec('php artisan migrate --force 2>&1');
echo $output;

if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
    echo "❌ Migration failed. Please check the error messages above.\n";
    exit(1);
}

echo "✅ Migrations completed successfully\n";

// Run seeders
echo "\n🌱 Running database seeders...\n";
$output = shell_exec('php artisan db:seed --force 2>&1');
echo $output;

if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
    echo "⚠️  Seeding completed with warnings. Check the output above.\n";
} else {
    echo "✅ Database seeding completed successfully\n";
}

// Create storage link
echo "\n🔗 Creating storage link...\n";
$output = shell_exec('php artisan storage:link 2>&1');
echo $output;

// Clear caches
echo "\n🧹 Clearing application caches...\n";
shell_exec('php artisan config:clear 2>&1');
shell_exec('php artisan cache:clear 2>&1');
shell_exec('php artisan view:clear 2>&1');
echo "✅ Caches cleared\n";

echo "\n🎉 Migration completed successfully!\n\n";
echo "📋 Next steps:\n";
echo "1. Start your Laravel server: php artisan serve\n";
echo "2. Start Vite dev server: npm run dev\n";
echo "3. Visit http://localhost:8000\n\n";

echo "👤 Demo accounts:\n";
echo "Admin: admin@projectmanagement.com / password\n";
echo "Manager: manager@projectmanagement.com / password\n";
echo "Staff: john@projectmanagement.com / password\n\n";

echo "📚 For detailed setup instructions, see SETUP.md\n";
echo "🆘 For support, check the troubleshooting section in SETUP.md\n\n";

echo "Happy project managing! 🚀\n";