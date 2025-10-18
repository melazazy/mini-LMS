# Mini LMS - Learning Management System

A modern, full-featured Learning Management System built with Laravel, Livewire, and Filament.

## 🚀 Features

- **Authentication System**: Complete login/register with role-based access control
- **Authorization System**: Policies, gates, and middleware for secure access
- **Course Management**: Create, organize, and manage courses with video content
- **User Management**: Admin, instructor, and student roles with comprehensive permissions
- **Video Streaming**: Secure video delivery with HLS support and progress tracking
- **Payment Processing**: Stripe integration for course purchases
- **Real-time Notifications**: Pusher-powered live updates
- **Admin Dashboard**: Filament-powered admin interface
- **Content Moderation**: Admin approval workflow for courses and lessons
- **Progress Tracking**: Real-time video progress with 90% completion threshold
- **Enrollment System**: Free and paid course enrollment with payment tracking
- **Responsive Design**: Mobile-first design with Tailwind CSS

## 🛠 Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **Livewire** - Full-stack framework for dynamic UIs
- **Filament** - Admin panel and form builder
- **Laravel Sanctum** - API authentication
- **Laravel Policies** - Authorization system
- **Custom Middleware** - Role-based access control

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
- **HLS Streaming** - Video streaming protocol

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

## 🗄️ Database Implementation

### Database Schema Overview
The Mini LMS uses a comprehensive database schema designed for scalability and performance:

#### Core Entities
- **Users**: Role-based user management (admin, instructor, student)
- **Courses**: Course management with pricing, levels, and publication workflow
- **Lessons**: Video content with HLS support and sequential ordering
- **Enrollments**: User-course relationships with payment tracking
- **Lesson Progress**: Video progress tracking with 90% completion threshold
- **Course Completions**: Course completion tracking and certification
- **Moderation Reviews**: Content approval workflow for courses and lessons
- **Notifications**: User-specific notification system

#### Key Database Features
- **Foreign Key Constraints**: Proper referential integrity
- **Indexes**: Optimized for common queries (user roles, course levels, progress tracking)
- **Unique Constraints**: Prevents duplicate enrollments and progress entries
- **JSON Fields**: Flexible data storage for resources and notification data
- **Polymorphic Relationships**: Moderation reviews for both courses and lessons
- **Cascade Deletes**: Automatic cleanup when parent records are deleted

#### Sample Data
The database includes comprehensive seeders with:
- **5 Users**: 1 admin, 2 instructors, 2 students
- **5 Courses**: Mix of free and paid courses across all difficulty levels
- **31 Lessons**: Complete lesson sets for each course with video URLs and resources

### Database Verification
```bash
# Verify database setup
php artisan tinker --execute="echo 'Users: ' . \App\Models\User::count(); echo PHP_EOL; echo 'Courses: ' . \App\Models\Course::count(); echo PHP_EOL; echo 'Lessons: ' . \App\Models\Lesson::count(); echo PHP_EOL;"

# Test relationships
php artisan tinker --execute="\$course = \App\Models\Course::first(); echo 'Course: ' . \$course->title . PHP_EOL; echo 'Lessons: ' . \$course->lessons()->count() . PHP_EOL; echo 'Creator: ' . \$course->creator->name . PHP_EOL;"
```

## 🔐 Authentication & Authorization

The Mini LMS includes a comprehensive authentication and authorization system:

### User Roles
- **Admin**: Full system access, content moderation, user management
- **Instructor**: Course creation, lesson management, student progress tracking
- **Student**: Course enrollment, video learning, progress tracking

### Database Schema
The system includes a comprehensive database schema with the following core entities:

#### Core Tables
- **users** - User accounts with role-based access (admin, instructor, student)
- **courses** - Course management with pricing, levels, and publication status
- **lessons** - Video content with HLS support, ordering, and free preview flags
- **enrollments** - User-course relationships with payment tracking
- **lesson_progress** - Video progress tracking with 90% completion threshold
- **course_completions** - Course completion tracking
- **moderation_reviews** - Content approval workflow for courses and lessons
- **notifications** - User notification system

#### Key Features
- **Role-based Access Control**: Three distinct user roles with specific permissions
- **Course Management**: Free and paid courses with different difficulty levels
- **Video Learning**: HLS streaming support with progress tracking and resume functionality
- **Enrollment System**: Stripe-ready payment integration with enrollment status tracking
- **Content Moderation**: Admin approval workflow for course and lesson publication
- **Progress Tracking**: Real-time video progress with completion thresholds
- **Notification System**: User-specific notifications with read/unread status

### Authentication Features
- User registration with role selection
- Secure login/logout with remember me functionality
- Password reset via email
- Session management and CSRF protection
- Mobile-responsive authentication forms

### Authorization System
- **Policies**: Course, Lesson, and Enrollment policies for fine-grained access control
- **Gates**: Custom gates for content management and user management
- **Middleware**: Role-based middleware for route protection
- **Route Protection**: Automatic redirection based on user roles

### Security Features
- Password hashing with Laravel's Hash facade
- Session regeneration on login
- Secure password reset tokens
- CSRF protection on all forms
- Role-based access control

## 👤 Default Users

After running the seeders, you can access the system with these default accounts:

### Admin User
- **URL**: http://localhost:8000/admin
- **Email**: admin@example.com
- **Password**: password
- **Role**: Admin

### Instructor User
- **Email**: instructor@example.com
- **Password**: password
- **Role**: Instructor

### Student User
- **Email**: student1@example.com
- **Password**: password
- **Role**: Student

### Authentication Routes
- **Login**: `/login` - User authentication
- **Register**: `/register` - User registration with role selection
- **Dashboard**: `/dashboard` - User dashboard (authenticated users)
- **Password Reset**: `/password/reset` - Password reset functionality

## 📁 Project Structure

```
mini-lms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/         # Authentication controllers
│   │   ├── Middleware/       # Custom middleware
│   │   └── Livewire/         # Livewire components
│   ├── Models/
│   │   ├── User.php          # User model with role-based relationships
│   │   ├── Course.php       # Course model with business logic
│   │   ├── Lesson.php       # Lesson model with video handling
│   │   ├── Enrollment.php   # Enrollment model
│   │   ├── LessonProgress.php # Progress tracking model
│   │   ├── CourseCompletion.php # Completion tracking model
│   │   ├── ModerationReview.php # Content moderation model
│   │   └── Notification.php # Notification model
│   ├── Policies/             # Authorization policies
│   └── Providers/
│       └── AuthServiceProvider.php
├── config/
│   ├── auth.php              # Authentication configuration
│   ├── broadcasting.php      # Pusher configuration
│   ├── filesystems.php       # S3 configuration
│   └── mail.php              # Email configuration
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/
│   │   ├── 2025_10_18_003744_add_role_to_users_table.php
│   │   ├── 2025_10_18_003818_create_courses_table.php
│   │   ├── 2025_10_18_003829_create_lessons_table.php
│   │   ├── 2025_10_18_003838_create_enrollments_table.php
│   │   ├── 2025_10_18_003849_create_lesson_progress_table.php
│   │   ├── 2025_10_18_003855_create_course_completions_table.php
│   │   ├── 2025_10_18_003901_create_moderation_reviews_table.php
│   │   └── 2025_10_18_003906_create_notifications_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── CourseSeeder.php
│       └── LessonSeeder.php
├── resources/
│   ├── css/
│   │   └── app.css           # Tailwind CSS
│   ├── js/
│   │   ├── app.js            # Alpine.js setup
│   │   └── bootstrap.js      # Axios configuration
│   └── views/
│       ├── auth/             # Authentication views
│       └── layouts/          # Layout templates
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

# Create seeder
php artisan make:seeder SeederName

# Run migrations and seeders
php artisan migrate:fresh --seed

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

The project includes comprehensive test coverage for authentication, authorization, and database models:

```bash
# Run all tests
php artisan test

# Run authentication tests
php artisan test --filter AuthTest

# Run policy tests
php artisan test --filter PolicyTest

# Run model tests
php artisan test --filter ModelTest

# Run specific test
php artisan test --filter TestName
```

### Test Coverage
- **Database Models**: All 8 core models with relationships and business logic
- **Authentication Tests**: Registration, login, logout, role validation
- **Policy Tests**: Course, lesson, enrollment permissions
- **Middleware Tests**: Role-based access control
- **Factory Tests**: Model factories for testing
- **Seeder Tests**: Database seeding verification

### Test Scenarios
- User registration as student/instructor/admin
- Login with valid/invalid credentials
- Role-based access control enforcement
- Policy enforcement for all models
- Middleware protection verification
- Password reset functionality
- Remember me functionality
- Database relationships and constraints
- Model business logic and helper methods

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

### ✅ Completed Features
- [x] Authentication system with role-based access control
- [x] Authorization system with policies and middleware
- [x] User registration and login functionality
- [x] Password reset functionality
- [x] Comprehensive database schema with 8 core models
- [x] Database migrations and seeders
- [x] Eloquent models with relationships and business logic
- [x] User roles (admin, instructor, student)
- [x] Course management system
- [x] Lesson management with video support
- [x] Enrollment system with payment tracking
- [x] Progress tracking system
- [x] Content moderation workflow
- [x] Notification system
- [x] HLS video streaming support

### 🚧 In Progress
- [ ] Video streaming integration with Plyr.js
- [ ] Payment processing with Stripe
- [ ] Real-time notifications with Pusher
- [ ] Admin dashboard with Filament

### 📋 Planned Features
- [ ] Course categories and tags
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native)
- [ ] Multi-language support
- [ ] Advanced reporting features
- [ ] Integration with external LMS platforms
- [ ] Certificate generation
- [ ] Discussion forums
- [ ] Assignment submissions

---

Built with ❤️ using Laravel, Livewire, and Filament.