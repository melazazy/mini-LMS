# 🎓 Mini LMS - Learning Management System

> A production-ready Learning Management System built with Laravel 12, Livewire 3, and Filament v3

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.6-4E56A6?style=flat&logo=livewire)](https://livewire.laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.3-F59E0B?style=flat)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-227%20Passing-success?style=flat)]()

## 📊 Project Status

**🎯 Production Ready** | **227 Tests Passing** | **517 Assertions** | **100% Success Rate**

### Quick Stats
- 14 Database Tables | 8 Models | 11 Action Classes | 3 Policies
- 6 Filament Resources | 3 Livewire Components | 4 Dashboard Widgets
- Video Player (Plyr.js) | Stripe Payments | Email Notifications | Progress Tracking

## ✨ Key Features

- **Multi-Role System**: Admin, Instructor, Student with policy-based authorization
- **Course Management**: Create, publish, organize lessons with SEO-friendly URLs
- **Video Player**: Plyr.js with auto-resume, progress tracking (auto-save every 5s)
- **Payment System**: Stripe integration with webhooks and idempotent enrollment
- **Analytics**: Role-specific dashboards with real-time widgets
- **Notifications**: Welcome, enrollment, completion emails via SMTP (queued)
- **Content Moderation**: Admin approval workflow for courses and lessons
- **Responsive Design**: Mobile-first with Tailwind CSS and Alpine.js

## 🛠 Tech Stack

**Backend**: Laravel 12.0 | PHP 8.4 | MySQL 8.0+  
**Frontend**: Livewire 3.6 | Alpine.js 3.x | Tailwind CSS 3.x | Plyr.js 3.7.8  
**Admin**: Filament v3.3.43 (6 Resources, 4 Widgets)  
**Services**: Stripe | Hostinger SMTP | HLS Streaming  
**Testing**: Pest 3.8 | PHPUnit 11.5 (227 tests, 517 assertions)

## 📖 Documentation

- **[Complete Project Summary](PROJECT_SUMMARY.md)** - Comprehensive overview
- **[Installation Guide](docs/INSTALLATION.md)** - Detailed setup instructions
- **[Testing Guide](docs/TESTING.md)** - Test coverage and execution
- **[Architecture Guide](docs/ARCHITECTURE.md)** - System design and patterns

## 📋 Prerequisites

- PHP 8.4+ (with `intl`, `pdo_mysql`, `mbstring`, `xml`, `curl`)
- Composer 2.x | Node.js 20.19+ | MySQL 8.0+ | Git
- Stripe Account (optional) | SMTP Server (Hostinger configured)

## 🚀 Quick Start

### One-Command Setup
```bash
composer run setup
```

### Manual Installation
```bash
# 1. Clone and install
git clone <repository-url>
cd Mini_LMS
composer install
npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database setup
mysql -u root -p -e "CREATE DATABASE mini_lms;"
php artisan migrate:fresh --seed

# 4. Create admin user
php artisan make:filament-user

# 5. Start development
npm run dev
php artisan serve
```

**Access**:
- Main Site: http://localhost:8000
- Admin Panel: http://localhost:8000/admin

## 🔧 Configuration

Update `.env` with your credentials:

```env
# Database
DB_DATABASE=mini_lms
DB_USERNAME=root
DB_PASSWORD=your_password

# Stripe (optional)
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_secret
STRIPE_WEBHOOK_SECRET=whsec_your_webhook

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=support@duxelite.net
MAIL_ENCRYPTION=ssl
```

See [Installation Guide](docs/INSTALLATION.md) for complete configuration.

## 👤 Default Users

After running seeders:

- **Admin**: admin@example.com / password
- **Instructor**: instructor@example.com / password
- **Student**: student1@example.com / password

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=AuthTest

# Run with coverage
php artisan test --coverage
```

**Test Coverage**: 227 tests, 517 assertions, 100% pass rate

## 🏗 Architecture

### Action Classes Pattern
Business logic encapsulated in dedicated action classes:
- `EnrollInCourseAction` - Handle course enrollment
- `UpdateLessonProgressAction` - Track video progress
- `PublishCourseAction` - Publish courses
- And 8 more action classes...

### Policy-Based Authorization
- `CoursePolicy` - Course access control
- `LessonPolicy` - Lesson viewing permissions
- `EnrollmentPolicy` - Enrollment operations

See [Architecture Guide](docs/ARCHITECTURE.md) for complete details.

## 📁 Project Structure

```
mini-lms/
├── app/
│   ├── Actions/          # Business logic (11 classes)
│   ├── Models/           # Eloquent models (8 models)
│   ├── Policies/         # Authorization (3 policies)
│   ├── Livewire/         # Components (3 components)
│   └── Filament/         # Admin panel (6 resources)
├── database/
│   ├── migrations/       # Database schema (14 tables)
│   └── seeders/          # Sample data
├── resources/
│   └── views/            # Blade templates
├── routes/
│   └── web.php           # Application routes
└── tests/                # Test suite (227 tests)
```

## 📦 Deployment

### Production Checklist
1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure production database and services
3. Run `npm run production`
4. Run `php artisan migrate --force`
5. Cache configuration: `php artisan config:cache`
6. Set up queue worker with supervisor

See [Deployment Guide](docs/DEPLOYMENT.md) for complete instructions.

## 📊 Project Metrics

| Metric | Count |
|--------|-------|
| Tests Passing | 227 ✅ |
| Total Assertions | 517 ✅ |
| Database Tables | 14 ✅ |
| Eloquent Models | 8 ✅ |
| Action Classes | 11 ✅ |
| Policy Classes | 3 ✅ |
| Livewire Components | 3 ✅ |
| Filament Resources | 6 ✅ |

## 🏆 Key Achievements

- ✅ **100% Test Pass Rate** - All 227 tests passing
- ✅ **Modern Tech Stack** - Laravel 12, Livewire 3, Filament v3
- ✅ **Clean Architecture** - Action Pattern, Policies, Services
- ✅ **Production Ready** - Security, performance, scalability
- ✅ **Best Practices** - PSR standards, SOLID principles
- ✅ **Well Documented** - Clear, comprehensive documentation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Developer

**Mustafa Elazazy**  
Full-Stack Laravel Developer

---

**Built with ❤️ using Laravel, Livewire, and Filament**

**Version**: 1.0.0 | **Last Updated**: October 23, 2025
