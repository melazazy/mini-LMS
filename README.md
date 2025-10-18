# Mini LMS - Learning Management System

A modern, full-featured Learning Management System built with Laravel, Livewire, and Filament.

## 🚀 Features

- **Course Management**: Create, organize, and manage courses with video content
- **User Management**: Student and instructor roles with comprehensive permissions
- **Video Streaming**: Secure video delivery with progress tracking
- **Payment Processing**: Stripe integration for course purchases
- **Real-time Notifications**: Pusher-powered live updates
- **Admin Dashboard**: Filament-powered admin interface
- **Responsive Design**: Mobile-first design with Tailwind CSS

## 🛠 Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **Livewire** - Full-stack framework for dynamic UIs
- **Filament** - Admin panel and form builder
- **Laravel Sanctum** - API authentication
- **Spatie Permission** - Role and permission management

### Frontend
- **Alpine.js** - Lightweight JavaScript framework
- **Tailwind CSS** - Utility-first CSS framework
- **Plyr.js** - Video player
- **Laravel Mix** - Asset compilation

### Services
- **Stripe** - Payment processing
- **Pusher** - Real-time broadcasting
- **AWS S3** - File storage
- **MySQL** - Database

## 📋 Prerequisites

- PHP 8.2+
- Composer
- Node.js 20.19+ (or use Laravel Mix with Node.js 20.15+)
- MySQL 8.0+
- Git

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd Mini_LMS/mini-lms
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration
Update your `.env` file with database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_lms
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Run Migrations and Seeders
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE mini_lms;"

# Run migrations and seeders
php artisan migrate:fresh --seed
```

### 6. Compile Assets
```bash
# Using Laravel Mix (recommended for Node.js 20.15+)
npm run development

# Or using Vite (requires Node.js 20.19+)
npm run dev
```

### 7. Start Development Server
```bash
php artisan serve
```

## 🐳 Docker Development (Optional)

A Docker Compose configuration is provided for easy development setup:

```bash
# Start services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate:fresh --seed
```

## 🔧 Configuration

### Required Environment Variables

#### AWS S3 (File Storage)
```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
```

#### Pusher (Real-time Notifications)
```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

#### Stripe (Payments)
```env
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

#### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Mini LMS"
```

## 👤 Default Admin User

After running the seeders, you can access the admin panel with:

- **URL**: http://localhost:8000/admin
- **Email**: admin@minilms.com
- **Password**: password

## 📁 Project Structure

```
mini-lms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Livewire/          # Livewire components
│   ├── Models/
│   └── Providers/
├── config/
│   ├── broadcasting.php       # Pusher configuration
│   ├── filesystems.php        # S3 configuration
│   └── mail.php              # Email configuration
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   │   └── app.css           # Tailwind CSS
│   ├── js/
│   │   ├── app.js            # Alpine.js setup
│   │   └── bootstrap.js      # Axios configuration
│   └── views/
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
└── public/
```

## 🎯 Development Workflow

### Asset Compilation
```bash
# Development (with watch)
npm run watch

# Production build
npm run production
```

### Database Operations
```bash
# Create migration
php artisan make:migration create_table_name

# Create model with migration
php artisan make:model ModelName -m

# Create Livewire component
php artisan make:livewire ComponentName

# Create Filament resource
php artisan make:filament-resource ResourceName
```

### Queue Processing
```bash
# Start queue worker
php artisan queue:work

# Process failed jobs
php artisan queue:retry all
```

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter TestName
```

## 📦 Deployment

### Production Checklist

1. **Environment Configuration**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure production database
   - Set up production AWS S3 bucket
   - Configure production Pusher app
   - Set up production Stripe account

2. **Asset Compilation**
   ```bash
   npm run production
   ```

3. **Database Migration**
   ```bash
   php artisan migrate --force
   ```

4. **Cache Optimization**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Queue Configuration**
   - Set up supervisor or similar for queue workers
   - Configure cron jobs for scheduled tasks

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/your-repo/issues) page
2. Create a new issue with detailed information
3. Join our community discussions

## 🗺 Roadmap

- [ ] Course categories and tags
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native)
- [ ] Multi-language support
- [ ] Advanced reporting features
- [ ] Integration with external LMS platforms

---

Built with ❤️ using Laravel, Livewire, and Filament.