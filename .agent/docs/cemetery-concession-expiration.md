# Cemetery Management - Concession Expiration System

## Overview
The cemetery management system tracks graves, burials (cemetery register), and concessions with automated expiration alerts.

---

## Concession Expiration Logic

### Expiration Timeline

```
Concession Created
     │
     ↓
🟢 ACTIVE (> 90 days remaining)
  Status: 'active'
  Alert: None
     │
     ↓
🟡 EXPIRING SOON (30-90 days remaining)
  Status: 'expiring'
  Alert: Yellow flag
  Action: Send renewal reminder
     │
     ↓
🟠 CRITICAL (0-30 days remaining)
  Status: 'expiring'
  Alert: Orange flag
  Action: Urgent renewal reminder
     │
     ↓
🔴 EXPIRED (past expiry date)
  Status: 'expired'
  Alert: Red flag
  Action: Contact concessionaire
     │
     ↓
⚫ GRACE PERIOD (60 days after expiry)
  Status: 'expired'
  Alert: Black flag
  Action: Final notice before termination
     │
     ↓
Grave becomes available (if not renewed)
```

---

## How Expiration is Calculated

### Automatic Expiry Date Calculation

```php
// On concession creation
$concession = Concession::create([
    'concession_date' => now(),
    'duration_years' => 20,
]);

// Auto-calculated:
$concession->expiry_date = now()->addYears(20);
```

### Days Until Expiry

```php
$concession->days_until_expiry;
// Returns: number of days (negative if expired)

// Example:
// Expiry: 2026-05-01
// Today: 2026-02-02
// Returns: 88 days
```

---

## Alert Levels

### Level Detection

```php
$concession->expiration_alert_level;

/*
Returns:
  - 'active' if days_until_expiry > 90
  - 'warning' if days_until_expiry <= 90 and > 30
  - 'critical' if days_until_expiry <= 30 and >= 0
  - 'expired' if days_until_expiry < 0
*/
```

### Color Coding

```php
$concession->expiration_color;

/*
Returns:
  - 'green' → active (> 90 days)
  - 'yellow' → warning (30-90 days)
  - 'orange' → critical (0-30 days)
  - 'red' → expired
*/
```

---

## Expiration Checks

### Individual Checks

```php
// Is expired?
$concession->isExpired();  // true/false

// Is expiring soon (< 90 days)?
$concession->isExpiringSoon();  // true/false

// Is expiring critical (< 30 days)?
$concession->isExpiringCritical();  // true/false
```

### Query Scopes

```php
// Get all expiring concessions (< 90 days)
Concession::expiringSoon()->get();

// Get critical expiring concessions (< 30 days)
Concession::expiringCritical()->get();

// Get expired concessions
Concession::expired()->get();

// Get concessions expiring in specific days
Concession::expiringWithinDays(30)->get();
```

---

## Auto-Update Status (Cron Job)

### Cron Command

Create a scheduled command to update concession statuses daily:

```php
// app/Console/Commands/UpdateConcessionStatuses.php

namespace App\Console\Commands;

use App\Models\Concession;
use Illuminate\Console\Command;

class UpdateConcessionStatuses extends Command
{
    protected $signature = 'concessions:update-statuses';
    protected $description = 'Update concession statuses based on expiry dates';

    public function handle()
    {
        Concession::updateExpirationStatuses();
        
        $this->info('✅ Concession statuses updated');
        $this->info('   - Active: ' . Concession::active()->count());
        $this->info('   - Expiring: ' . Concession::expiring()->count());
        $this->info('   - Expired: ' . Concession::expired()->count());
    }
}
```

### Schedule in Kernel

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Run daily at midnight
    $schedule->command('concessions:update-statuses')->daily();
}
```

### Manual Update

```php
// Update all statuses manually
Concession::updateExpirationStatuses();
```

---

## Renewal Workflow

### Renew Concession

```php
$concession = Concession::find($id);

// Renew for additional 20 years
$concession->renew(
    additionalYears: 20,
    feePaid: 2500.00
);

// Result:
// - expiry_date extended by 20 years
// - renewal_count incremented
// - last_renewal_date = now()
// - status = 'active'
// - fee_paid = 2500.00
// - fee_paid_date = now()
```

### Terminate Concession

```php
$concession->terminate('Famiglia ha rinunciato alla concessione');

// Result:
// - status = 'terminated'
// - grave status = 'available'
// - notes updated with termination reason
```

---

## Real-World Examples

### Example 1: Active Concession (✅ Green)

```php
$concession = Concession::create([
    'grave_id' => $grave->id,
    'concessionaire_name' => 'Famiglia Bianchi',
    'concession_date' => now()->subYears(5),
    'duration_years' => 20,
    'fee_paid' => 2000.00,
]);

// Auto-calculated:
// expiry_date = now()->addYears(15)

// Status:
$concession->days_until_expiry;  // ~5,475 days (15 years)
$concession->expiration_alert_level;  // 'active'
$concession->expiration_color;  // 'green'
$concession->isExpiringSoon();  // false
```

---

### Example 2: Expiring Soon (🟡 Warning)

```php
$concession = Concession::create([
    'concessionaire_name' => 'Famiglia Rossi',
    'concession_date' => now()->subYears(19)->subMonths(10),
    'duration_years' => 20,
]);

// Status:
$concession->days_until_expiry;  // ~60 days
$concession->expiration_alert_level;  // 'warning'
$concession->expiration_color;  // 'yellow'
$concession->isExpiringSoon();  // true
$concession->isExpiringCritical();  // false

// Action: Send renewal reminder email
```

---

### Example 3: Expiring Critical (🟠 Critical)

```php
$concession = Concession::where('expiry_date', now()->addDays(20))->first();

// Status:
$concession->days_until_expiry;  // 20 days
$concession->expiration_alert_level;  // 'critical'
$concession->expiration_color;  // 'orange'
$concession->isExpiringCritical();  // true

// Action: Urgent phone call to concessionaire
```

---

### Example 4: Expired (🔴 Red)

```php
$concession = Concession::where('expiry_date', now()->subDays(30))->first();

// Status:
$concession->days_until_expiry;  // -30 days
$concession->expiration_alert_level;  // 'expired'
$concession->expiration_color;  // 'red'
$concession->isExpired();  // true

// Action: Contact concessionaire for renewal or termination
```

---

## Dashboard Queries

### Expiring Concessions Summary

```php
// Get counts by alert level
$active = Concession::active()->count();
$expiring = Concession::expiringSoon()->count();
$critical = Concession::expiringCritical()->count();
$expired = Concession::expired()->count();

// Display:
/*
🟢 Active: 1,234
🟡 Expiring soon (< 90 days): 45
🟠 Critical (< 30 days): 12
🔴 Expired: 8
*/
```

### Concessions Requiring Action

```php
// Get concessions expiring in next 30 days
$urgentRenewals = Concession::expiringWithinDays(30)
    ->with(['grave.cemeteryArea.cemetery'])
    ->orderBy('expiry_date')
    ->get();

foreach ($urgentRenewals as $concession) {
    echo "{$concession->concessionaire_name} - {$concession->days_until_expiry} days\n";
}
```

### Expired Concessions Report

```php
// Get all expired concessions by cemetery
$expiredByCemetery = Concession::expired()
    ->join('graves', 'concessions.grave_id', '=', 'graves.id')
    ->join('cemetery_areas', 'graves.cemetery_area_id', '=', 'cemetery_areas.id')
    ->join('cemeteries', 'cemetery_areas.cemetery_id', '=', 'cemeteries.id')
    ->select('cemeteries.name', 'concessions.*')
    ->get()
    ->groupBy('name');
```

---

## Automated Renewal Reminders

### Email Reminder Logic

```php
// Send reminders for concessions expiring in 90 days
$expiringIn90 = Concession::expiringWithinDays(90)
    ->whereNull('last_reminder_sent')  // Add this column if needed
    ->get();

foreach ($expiringIn90 as $concession) {
    // Send email
    Mail::to($concession->concessionaire_email)->send(
        new ConcessionExpiryReminder($concession)
    );
    
    // Mark as reminded
    $concession->update(['last_reminder_sent' => now()]);
}
```

### Reminder Schedule

```
90 days before → First reminder (email)
60 days before → Second reminder (email)
30 days before → Urgent reminder (email + SMS)
7 days before → Final reminder (phone call)
Expiry date → Expired notification
30 days after → Grace period ending (final notice)
```

---

## Cemetery Register (Lista Morti)

### Query All Burials

```php
// Get burial register for a cemetery
$cemetery = Cemetery::find($id);

$register = Burial::whereHas('grave.cemeteryArea', function ($query) use ($cemetery) {
        $query->where('cemetery_id', $cemetery->id);
    })
    ->with(['grave.cemeteryArea', 'deceased', 'concession'])
    ->orderBy('burial_date', 'desc')
    ->get();
```

### Export Register to PDF/Excel

```php
// Export cemetery register
$burials = Burial::byDateRange('2020-01-01', '2024-12-31')->get();

// Generate PDF with:
// - Deceased name
// - Death date
// - Burial date
// - Grave number
// - Cemetery area
// - Concession status
```

---

## Benefits

### ✅ Automated Tracking
- No manual expiry checking
- Real-time status updates
- Visual alert system (semaforo)

### ✅ Revenue Protection
- Timely renewal reminders
- Prevents lost revenue from expired concessions
- Track unpaid fees

### ✅ Legal Compliance
- Complete burial register
- Historical record keeping
- Concession documentation

### ✅ Operational Efficiency
- Quick grave availability lookup
- Automated status workflows
- Reduced administrative burden

---

## Future Enhancements

- 📧 **Automated Email/SMS Reminders** - Schedule notifications
- 📊 **Revenue Forecasting** - Predict renewal income
- 🔔 **Dashboard Alerts** - Real-time notifications
- 📱 **Family Portal** - Self-service renewal
- 💳 **Online Payment** - Stripe/PayPal integration
- 📜 **Digital Contracts** - E-signature for renewals

---

**Result:** Never miss a concession renewal! 🎉
