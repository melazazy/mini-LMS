# Real-time Features Manual Testing Guide

## Prerequisites Checklist
- [x] Pusher credentials configured in `.env`
- [x] VAPID keys generated
- [x] Frontend assets built (`npm run build`)
- [ ] Queue worker running
- [ ] Development server running

## Setup

### 1. Start Queue Worker (Terminal 1)
```bash
php artisan queue:work
```
Keep this running to process queued notifications.

### 2. Start Development Server (Terminal 2)
```bash
php artisan serve
```

### 3. Open Browser Console
Open your browser's Developer Tools (F12) and go to the Console tab to see real-time events.

---

## Test 1: Broadcasting Configuration ✓

### Verify Pusher Connection

1. Open the application in your browser: `http://localhost:8000`
2. Open Browser Console (F12)
3. Look for Echo connection messages:
   ```
   Pusher : State changed : connecting -> connected
   ```

**Expected Result:** ✅ Connection established without errors

**Troubleshooting:**
- If connection fails, verify Pusher credentials in `.env`
- Check that `BROADCAST_CONNECTION=pusher` is set
- Verify `VITE_PUSHER_APP_KEY` and `VITE_PUSHER_APP_CLUSTER` are set

---

## Test 2: Real-time Progress Updates 📊

### Test Progress Broadcasting

1. **Login as a student**
   - Email: `student@test.com` / Password: `password`
   
2. **Enroll in a course** (if not already enrolled)

3. **Open Course Player**
   - Navigate to a course with lessons
   - Start watching a lesson

4. **Watch for Console Messages**
   In the browser console, you should see:
   ```javascript
   Echo listening on: private-user.{userId}
   Received progress.updated event
   ```

5. **Verify UI Updates**
   - Progress bar should update in real-time
   - Percentage should change without page refresh
   - Completion status should update automatically

**Expected Result:** ✅ Progress updates broadcast and UI updates without refresh

---

## Test 3: Course Completion Notification 🎉

### Test Course Completion Flow

1. **Complete all lessons in a course**
   - Watch all lessons to 100%
   - The last lesson completion should trigger course completion

2. **Check Browser Console**
   Look for:
   ```javascript
   Received course.completed event
   ```

3. **Check Notifications**
   - Database notification should be created
   - Email should be queued (check queue worker terminal)
   - Web push notification should appear (if subscribed)

4. **Verify in Database**
   ```bash
   php artisan tinker
   ```
   ```php
   \App\Models\User::find(1)->notifications()->latest()->first()
   ```

**Expected Result:** ✅ Course completion triggers notification across all channels

---

## Test 4: Web Push Notifications 🔔

### Test Push Subscription

1. **Navigate to Settings/Profile** (or wherever PushNotificationManager is displayed)

2. **Click "Enable" Push Notifications**
   - Browser will prompt for permission
   - Click "Allow"

3. **Verify Subscription**
   ```bash
   php artisan tinker
   ```
   ```php
   \App\Models\PushSubscription::where('user_id', 1)->first()
   ```

4. **Test Push Notification**
   In tinker:
   ```php
   $user = \App\Models\User::find(1);
   $course = \App\Models\Course::first();
   $user->notify(new \App\Notifications\CourseUpdateNotification($course, 'updated'));
   ```

5. **Check Browser**
   - Push notification should appear
   - Click notification → should navigate to course

**Expected Result:** ✅ Push notifications work and navigate correctly

---

## Test 5: Service Worker 🔧

### Verify Service Worker Installation

1. **Open Browser DevTools → Application Tab**

2. **Check Service Workers**
   - Should see `/sw.js` registered
   - Status should be "activated and running"

3. **Check Cache Storage**
   - Should see `mini-lms-v1` cache
   - Should contain cached resources

4. **Test Offline Support**
   - Go offline (DevTools → Network → Offline)
   - Refresh page
   - Cached resources should load

**Expected Result:** ✅ Service worker active and caching works

---

## Test 6: Private Channel Authentication 🔐

### Test Channel Authorization

1. **Open Browser Console**

2. **Watch for Auth Requests**
   - Open Network tab
   - Filter by `/broadcasting/auth`

3. **Verify Authentication**
   - Should see POST requests to `/broadcasting/auth`
   - Status should be 200
   - Response should contain auth signature

4. **Test as Guest**
   - Logout
   - Try to access course player
   - Should NOT see private channel subscriptions

**Expected Result:** ✅ Only authenticated users can subscribe to private channels

---

## Test 7: Multiple Users Real-time 👥

### Test Multi-user Broadcasting

1. **Open Two Browser Windows**
   - Window 1: Login as User A
   - Window 2: Login as User B

2. **User A: Update Progress**
   - Watch a lesson
   - Update progress

3. **Verify Isolation**
   - User B should NOT see User A's progress updates
   - Each user only receives their own events

**Expected Result:** ✅ Events are properly isolated per user

---

## Test 8: Queue Processing ⚙️

### Verify Async Notification Processing

1. **Check Queue Worker Terminal**
   Should see:
   ```
   [timestamp] Processing: Illuminate\Notifications\SendQueuedNotifications
   [timestamp] Processed:  Illuminate\Notifications\SendQueuedNotifications
   ```

2. **Trigger Multiple Notifications**
   ```php
   $users = \App\Models\User::take(5)->get();
   $course = \App\Models\Course::first();
   
   foreach ($users as $user) {
       $user->notify(new \App\Notifications\CourseUpdateNotification($course));
   }
   ```

3. **Monitor Queue**
   ```bash
   php artisan queue:work --verbose
   ```

**Expected Result:** ✅ Notifications processed asynchronously

---

## Test 9: Error Handling 🛡️

### Test Graceful Degradation

1. **Disable Pusher** (temporarily)
   - Set `PUSHER_APP_KEY=invalid` in `.env`
   - Restart server

2. **Verify App Still Works**
   - App should function without real-time features
   - No JavaScript errors
   - Manual refresh shows updates

3. **Re-enable Pusher**
   - Restore correct credentials
   - Verify reconnection

**Expected Result:** ✅ App works without real-time features

---

## Test 10: Performance Monitoring 📈

### Check Pusher Dashboard

1. **Login to Pusher Dashboard**
   - Visit https://dashboard.pusher.com

2. **Monitor Events**
   - Go to Debug Console
   - Watch for events in real-time
   - Verify channel names: `private-user.{id}`

3. **Check Metrics**
   - Connection count
   - Message count
   - Peak connections

**Expected Result:** ✅ Events visible in Pusher dashboard

---

## Common Issues & Solutions

### Issue: Echo not connecting
**Solution:**
- Verify `VITE_PUSHER_APP_KEY` matches `PUSHER_APP_KEY`
- Run `npm run build` after changing .env
- Clear browser cache

### Issue: Auth endpoint 403
**Solution:**
- Verify user is authenticated
- Check CSRF token is present
- Verify `Broadcast::routes()` in routes

### Issue: Notifications not sending
**Solution:**
- Check queue worker is running
- Verify `QUEUE_CONNECTION=database` in .env
- Run `php artisan queue:failed` to check failed jobs

### Issue: Push notifications not appearing
**Solution:**
- Verify HTTPS (required for push in production)
- Check browser permissions
- Verify VAPID keys are correct
- Check service worker is registered

### Issue: Service worker not updating
**Solution:**
- Hard refresh (Ctrl+Shift+R)
- Unregister old service worker in DevTools
- Clear cache storage

---

## Success Criteria ✅

All tests should pass with:
- [ ] Pusher connection established
- [ ] Progress updates broadcast in real-time
- [ ] Course completion triggers notifications
- [ ] Push notifications work
- [ ] Service worker active
- [ ] Private channels authenticated
- [ ] Events isolated per user
- [ ] Notifications queued and processed
- [ ] Graceful degradation works
- [ ] Events visible in Pusher dashboard

---

## Next Steps After Testing

1. **Monitor in Production**
   - Set up error tracking (Sentry)
   - Monitor Pusher usage
   - Track notification delivery rates

2. **Optimize**
   - Add rate limiting
   - Implement notification preferences
   - Add notification history UI

3. **Enhance**
   - Add sound notifications
   - Add desktop notification badges
   - Add notification grouping

---

**Testing Date:** _________________
**Tested By:** _________________
**Results:** _________________
