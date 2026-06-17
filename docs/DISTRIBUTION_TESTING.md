# Distribution System Testing Guide

## Problem Fixed

The distribution queue was not being processed automatically. Conversations were stuck in the 'new' status and not being assigned to agents.

**Root Cause:** The `distribution:process-queue` command was not scheduled in the Laravel scheduler.

**Solution:** 
- Added `distribution:process-queue` to run every minute in `app/Console/Kernel.php`
- Improved queue filtering to only process 'new' status conversations
- Added comprehensive test suites

---

## Automatic Testing (Tinker)

Run the PHP test that creates dummy conversations and processes them through the distribution queue:

```bash
php artisan tinker < tests/distribution-test.php
```

This test:
1. Verifies distribution settings (mode and overflow action)
2. Lists all active agents and their current load
3. Creates 3 test conversations
4. Processes them through the distribution system
5. Verifies assignments were made correctly

### Expected Output

```
=== Distribution System Test ===

1. Verifying settings...
   Mode: automatic
   Overflow: next_agent

2. Checking active agents...
   Found 3 agents
   - Agent 1: 0/10 conversations
   - Agent 2: 1/10 conversations
   - Agent 3: 0/10 conversations

3. Creating test conversations...
   ✓ Created conversation #123
   ✓ Created conversation #124
   ✓ Created conversation #125

4. Processing distribution queue...
   ✓ Conv #123: new → in_attendance (Assigned to: Agent 1)
   ✓ Conv #124: new → in_attendance (Assigned to: Agent 2)
   ✓ Conv #125: new → in_attendance (Assigned to: Agent 3)

5. Verification Results:
   ✓ Conversation #123: status=in_attendance, claimed_by=1
   ✓ Conversation #124: status=in_attendance, claimed_by=2
   ✓ Conversation #125: status=in_attendance, claimed_by=3

6. Final Queue Status:
   Remaining in queue: 0

=== Test Complete ===
```

---

## UI Testing (Playwright)

Run the automated browser tests:

```bash
npx playwright test tests/distribution-ui-test.spec.js
```

Or with debug mode:

```bash
npx playwright test tests/distribution-ui-test.spec.js --debug
```

### Test Cases

1. **Display Settings** - Verifies distribution mode selector is visible
2. **Show Queued** - Checks if queued conversations appear in table
3. **Process Queue** - Manually processes the queue and verifies success
4. **Agent Metrics** - Displays agent capacity and load information
5. **Mode Switching** - Switches between automatic/manual modes

---

## Manual Testing (Web Interface)

Navigate to `/admin/distribution` and:

1. **Check Queue Status**
   - View "Conversas Aguardando Distribuição" section
   - Verify conversations are listed if any exist

2. **Manual Queue Processing**
   - Click "Processar Fila" button
   - Verify success message appears
   - Check that queued conversations moved to assigned agents

3. **Check Agent Metrics**
   - View agent list with current load percentages
   - Verify agents have capacity available (not all at 100%)

4. **Change Distribution Mode**
   - Toggle between "Automático" and "Manual" modes
   - Verify mode persists after page reload

---

## Scheduling Details

### Command Configuration

The distribution queue processor is scheduled in `app/Console/Kernel.php`:

```php
$schedule->command('distribution:process-queue')->everyMinute();
```

This means:
- Every minute, the system checks for unassigned conversations
- Conversations with status='new' and no active claim are processed
- Distribution happens according to configured settings

### Local Testing

For local development without a scheduler, manually run:

```bash
php artisan distribution:process-queue
```

Or start the scheduler in another terminal:

```bash
php artisan schedule:work
```

---

## Troubleshooting

### Conversations Not Being Distributed

1. **Check if scheduler is running:**
   ```bash
   php artisan schedule:list
   ```

2. **Check distribution settings:**
   - Verify mode is set to "automatic" (not "manual")
   - Verify at least one agent is marked as active with capacity > 0

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i distribution
   ```

4. **Manually trigger queue processing:**
   ```bash
   php artisan distribution:process-queue
   ```

### Agent Not Receiving Assignments

1. Check agent capacity settings:
   - Agent must have `is_active = true`
   - Agent must have available slots (current < max)

2. Check conversation status:
   - Conversation must have `status = 'new'`
   - Conversation must not have an active claim

3. Review distribution mode:
   - In "automatic" mode: agents below capacity get assigned
   - In "manual" mode: agents claim conversations themselves

---

## Related Files

- `app/Console/Kernel.php` - Scheduler configuration
- `app/Console/Commands/ProcessDistributionQueue.php` - Queue processing command
- `app/Services/DistributionService.php` - Distribution logic
- `app/Http/Controllers/Admin/DistributionController.php` - Web interface controller
- `resources/views/admin/distribution/index.blade.php` - UI template
