# Production Queue Setup for Certificate Notifications

## Current Setup (Development)
For development convenience, certificate notifications are sent **synchronously** (immediately). This means:
- ✅ No queue worker needed
- ✅ Emails send immediately when you click "Generate Certificate"
- ✅ Easy to test and debug
- ⚠️ Slower response time (waits for email to send)
- ⚠️ Not suitable for production with many users

## Production Setup (Recommended)

For production, you should enable **asynchronous** (queued) notifications for better performance.

### Step 1: Enable Queue for Notifications

**File:** `app/Notifications/CertificateIssuedNotification.php`

Change line 18 from:
```php
class CertificateIssuedNotification extends Notification
```

To:
```php
class CertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;
```

### Step 2: Enable Queue for Listener

**File:** `app/Listeners/SendCertificateIssuedNotification.php`

Change line 17 from:
```php
class SendCertificateIssuedNotification
{
    // Removed ShouldQueue and InteractsWithQueue for synchronous sending in development
    // For production: add "implements ShouldQueue" and "use InteractsWithQueue;"
    
    // Removed for synchronous mode
    // public $tries = 3;
```

To:
```php
class SendCertificateIssuedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
```

### Step 3: Configure Queue Driver

**File:** `.env`

Change from:
```env
QUEUE_CONNECTION=sync
```

To one of these options:

**Option A: Database Queue (Recommended for small-medium apps)**
```env
QUEUE_CONNECTION=database
```

**Option B: Redis Queue (Recommended for large apps)**
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Step 4: Run Queue Worker

**For Development:**
```bash
php artisan queue:work --tries=3
```

**For Production (using Supervisor):**

Create `/etc/supervisor/conf.d/mini-lms-worker.conf`:
```ini
[program:mini-lms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/mini-lms/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/mini-lms/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mini-lms-worker:*
```

### Step 5: Monitor Queue

**Check queue status:**
```bash
php artisan queue:monitor database
```

**View failed jobs:**
```bash
php artisan queue:failed
```

**Retry failed jobs:**
```bash
php artisan queue:retry all
```

**Clear failed jobs:**
```bash
php artisan queue:flush
```

## Benefits of Queued Notifications

### Development (Synchronous)
- ✅ No setup required
- ✅ Immediate feedback
- ✅ Easy debugging
- ⚠️ Slower response time

### Production (Asynchronous)
- ✅ Fast response time (instant)
- ✅ Better user experience
- ✅ Handles email server delays
- ✅ Automatic retries on failure
- ✅ Can handle bulk operations
- ⚠️ Requires queue worker
- ⚠️ Requires monitoring

## Testing Email in Development

### Option 1: Mailpit (Recommended)
Already configured in your docker-compose.yml:
```bash
docker-compose up -d
```

View emails at: http://localhost:8025

### Option 2: Mailtrap
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Option 3: Log Driver (No email sent)
```env
MAIL_MAILER=log
```

Emails will be written to `storage/logs/laravel.log`

## Current Configuration

**Modified Files for Development:**
1. `app/Notifications/CertificateIssuedNotification.php` - Removed `implements ShouldQueue`
2. `app/Listeners/SendCertificateIssuedNotification.php` - Removed `implements ShouldQueue`

**To Revert to Production Mode:**
Follow Steps 1-4 above.

## Monitoring in Production

### Laravel Horizon (Redis only)
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

Access dashboard at: `/horizon`

### Queue Monitoring Tools
- **Laravel Telescope** - Debug tool with queue monitoring
- **Laravel Pulse** - Real-time monitoring
- **Supervisor** - Process monitoring
- **New Relic** - Application monitoring
- **Sentry** - Error tracking

## Best Practices

1. **Always use queues in production** for better performance
2. **Monitor failed jobs** and set up alerts
3. **Use Supervisor** to keep queue workers running
4. **Set retry limits** to prevent infinite loops
5. **Log all queue operations** for debugging
6. **Test email delivery** before deploying
7. **Use Redis** for high-traffic applications
8. **Scale workers** based on queue size

## Troubleshooting

### Emails not sending?
1. Check `.env` mail configuration
2. Check `storage/logs/laravel.log` for errors
3. Test with: `php artisan tinker` then `Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });`
4. Verify SMTP credentials
5. Check firewall/port settings

### Queue not processing?
1. Ensure queue worker is running: `ps aux | grep queue:work`
2. Check queue connection: `php artisan queue:monitor`
3. Restart queue worker: `php artisan queue:restart`
4. Check for failed jobs: `php artisan queue:failed`

### Slow email sending?
1. Enable queue (asynchronous)
2. Increase queue workers
3. Use Redis instead of database
4. Optimize SMTP connection
