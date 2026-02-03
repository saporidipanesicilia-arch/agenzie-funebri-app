# Finance Module - Margin Calculation & Alert System

## Overview
The finance module provides **transparent pricing** and **automatic margin calculation** with configurable alerts to prevent unprofitable contracts.

---

## Margin Calculation Logic

### QuoteItem Level

Each quote item tracks:
- `cost_price` - Costo unitario (quanto paghiamo)
- `selling_price` - Prezzo vendita unitario (quanto facciamo pagare)
- `quantity` - Quantità

**Formulas:**
```
total_cost = cost_price × quantity
total_selling = selling_price × quantity
margin_amount = total_selling - total_cost
margin_percentage = (margin_amount / total_selling) × 100
```

**Example:**
```
Cofano impiallacciato noce
- cost_price: €800
- selling_price: €1,200
- quantity: 1

total_cost = €800 × 1 = €800
total_selling = €1,200 × 1 = €1,200
margin_amount = €1,200 - €800 = €400
margin_percentage = (€400 / €1,200) × 100 = 33.33%
```

---

### Quote Level (Aggregated)

Quote totals are calculated by summing all items:

```
total_cost = Σ items.total_cost
total_selling = Σ items.total_selling
```

**Discount handling:**
```
discount_applied = (total_selling × discount_percentage / 100) + discount_amount
final_total = total_selling - discount_applied
margin_amount = final_total - total_cost
margin_percentage = (margin_amount / final_total) × 100
```

**Example with discount:**
```
Items total selling: €2,000
Items total cost: €1,400

Discount 10%:
  discount_applied = (€2,000 × 10 / 100) = €200
  final_total = €2,000 - €200 = €1,800
  margin_amount = €1,800 - €1,400 = €400
  margin_percentage = (€400 / €1,800) × 100 = 22.22%

Without discount:
  margin_percentage = (€600 / €2,000) × 100 = 30%
```

**⚠️ Discount reduces margin!**

---

## Margin Alert System

### Configurable Thresholds

Each agency configures 3 thresholds in `margin_settings`:

1. **minimum_margin_percentage** (target)
   - Agency 1: 20%
   - Agency 2: 30%

2. **warning_margin_percentage** (soglia warning)
   - Agency 1: 12%
   - Agency 2: 20%

3. **critical_margin_percentage** (soglia critica)
   - Agency 1: 5%
   - Agency 2: 10%

### Alert Levels

```
🟢 GOOD (green)
   margin >= minimum
   Example: 33% (target 30%) → OK

🔵 INFO (blue)
   warning < margin < minimum
   Example: 22% (target 30%, warning 20%) → Below target but acceptable

🟡 WARNING (yellow)
   critical < margin < warning
   Example: 15% (warning 20%, critical 10%) → LOW MARGIN

🟠 CRITICAL (orange)
   0 < margin < critical
   Example: 7% (critical 10%) → VERY LOW MARGIN

🔴 LOSING MONEY (red)
   margin < 0
   Example: -5% → LOSING MONEY!
```

---

## How Alerts are Triggered

### 1. On Quote Save

```php
$quote = Quote::create([...]);

// Auto-calculate margin
$margin = $quote->margin_percentage;  // e.g. 22%

// Check alert level
$alertLevel = $quote->margin_alert_level;
/*
Returns:
   - 'good' if margin >= 30%
   - 'info' if 20% <= margin < 30%
   - 'warning' if 10% <= margin < 20%
   - 'critical' if 0% <= margin < 10%
   - 'critical' if margin < 0%
*/

// Get color
$color = $quote->margin_color;  // 'blue' for 22%
```

### 2. On Item Add/Edit

```php
// Add item
QuoteItem::create([
    'quote_id' => $quote->id,
    'cost_price' => 800,
    'selling_price' => 1200,
    'quantity' => 1,
]);

// Automatically triggers quote total recalculation
// Alert level re-evaluated
```

### 3. On Discount Apply

```php
// Apply discount
$quote->update(['discount_percentage' => 10]);

// Margin percentage decreases
// Alert level may change from 'good' to 'warning'
```

### 4. Visual Display (Frontend)

```php
$quote = Quote::find($id);

// Dashboard shows:
echo "Margine: " . number_format($quote->margin_percentage, 2) . "%";
echo "Stato: ";

switch($quote->margin_color) {
    case 'green':
        echo "🟢 Ottimo";
        break;
    case 'blue':
        echo "🔵 Sotto target";
        break;
    case 'yellow':
        echo "🟡 WARNING - Margine basso";
        break;
    case 'orange':
        echo "🟠 CRITICO - Margine molto basso";
        break;
    case 'red':
        echo "🔴 ATTENZIONE - In perdita!";
        break;
}
```

---

## Approval Workflow

### Automatic Approval Required

When `require_approval_for_low_margin` is enabled:

```php
if ($quote->requiresApproval()) {
    // Margin is 'warning' or 'critical'
    // Quote must be approved by manager before sending
    
    // Block sending
    if (!$quote->approved_by_user_id) {
        throw new Exception('Quote requires manager approval due to low margin');
    }
}
```

### Block Negative Margins

When `block_negative_margin` is enabled:

```php
if (!$quote->canBeAccepted()) {
    // Margin < 0%
    // Quote cannot be accepted
    
    throw new Exception('Cannot accept quote with negative margin');
}
```

---

## Real-World Examples

### Example 1: Good Margin (✅ Green)

**Scenario:** Basic burial, Agency 1 (target 20%)

| Item | Cost | Selling | Qty | Total Cost | Total Selling | Margin |
|------|------|---------|-----|------------|---------------|--------|
| Cofano | €800 | €1,200 | 1 | €800 | €1,200 | 33% |
| Fiori | €100 | €180 | 2 | €200 | €360 | 44% |
| Trasporto | €150 | €250 | 1 | €150 | €250 | 40% |
| Vestizione | €80 | €150 | 1 | €80 | €150 | 47% |
| Pratiche | €100 | €200 | 1 | €100 | €200 | 50% |

**Totals:**
- Total cost: €1,330
- Total selling: €2,160
- Margin: €830 (38.43%)

**Alert:** 🟢 GOOD (38.43% > 20% minimum)

---

### Example 2: Low Margin with Discount (🟡 Warning)

**Scenario:** Cremation with 10% discount, Agency 2 (target 30%)

| Item | Cost | Selling | Qty | Total Cost | Total Selling |
|------|------|---------|-----|------------|---------------|
| Cofano cremazione | €1,200 | €1,600 | 1 | €1,200 | €1,600 |
| Tanatoprassi | €300 | €450 | 1 | €300 | €450 |
| Vestizione | €120 | €200 | 1 | €120 | €200 |
| Trasporto | €200 | €320 | 1 | €200 | €320 |
| Cremazione | €600 | €800 | 1 | €600 | €800 |
| Fiori | €150 | €250 | 1 | €150 | €250 |

**Before discount:**
- Total cost: €2,570
- Total selling: €3,620
- Margin: €1,050 (29%)

**After 10% discount:**
- Discount: -€362
- Final total: €3,258
- Margin: €688 (21.12%)

**Alert:** 🟡 WARNING (21.12% < 30% minimum, > 20% warning)
- Requires manager approval

---

### Example 3: Negative Margin (🔴 Critical)

**Scenario:** Family requested excessive discount

| Item | Cost | Selling | Qty | Total Cost | Total Selling |
|------|------|---------|-----|------------|---------------|
| Cofano | €800 | €1,000 | 1 | €800 | €1,000 |
| Servizio | €400 | €500 | 1 | €400 | €500 |

**With 40% discount:**
- Total selling: €1,500
- Discount: -€600
- Final total: €900
- Total cost: €1,200
- Margin: -€300 (-33.33%)

**Alert:** 🔴 CRITICAL - LOSING MONEY
- **BLOCKED** - Cannot accept
- Must renegotiate discount or increase prices

---

## Configuration Per Agency

### Small Agency (Conservative)
```php
MarginSettings::create([
    'agency_id' => 1,
    'minimum_margin_percentage' => 20.00,
    'warning_margin_percentage' => 12.00,
    'critical_margin_percentage' => 5.00,
    'alert_enabled' => true,
    'block_negative_margin' => true,
    'require_approval_for_low_margin' => true,
]);
```

**Strategy:** Lower margins acceptable due to lower overhead

### Large Agency (Aggressive)
```php
MarginSettings::create([
    'agency_id' => 2,
    'minimum_margin_percentage' => 30.00,
    'warning_margin_percentage' => 20.00,
    'critical_margin_percentage' => 10.00,
    'alert_enabled' => true,
    'block_negative_margin' => true,
    'require_approval_for_low_margin' => true,
]);
```

**Strategy:** Higher margins required due to higher overhead (staff, branches, equipment)

---

## Usage in Code

### Create Quote with Items
```php
$quote = Quote::create([
    'funeral_id' => $funeral->id,
    'agency_id' => $funeral->agency_id,
    'branch_id' => $funeral->branch_id,
    'created_by_user_id' => auth()->id(),
]);

// Add items
QuoteItem::create([
    'quote_id' => $quote->id,
    'item_type' => 'coffin',
    'description' => 'Cofano impiallacciato noce',
    'cost_price' => 800.00,
    'selling_price' => 1200.00,
    'quantity' => 1,
]);

// Check margin
$quote->fresh(); // Reload to get calculated totals
echo "Margin: " . $quote->margin_percentage . "%";
echo "Alert: " . $quote->margin_alert_level;
```

### Apply Discount and Check
```php
$quote->update(['discount_percentage' => 10]);

if (!$quote->requiresApproval()) {
    // Can send immediately
    $quote->markAsSent();
} else {
    // Needs approval
    echo "⚠️ Quote requires approval due to low margin";
}
```

### Approve and Accept
```php
// Manager approves
$quote->update(['approved_by_user_id' => $managerId]);

// Try to accept
if ($quote->canBeAccepted()) {
    $quote->accept();
} else {
    echo "🔴 Cannot accept - margin is negative";
}
```

---

## Benefits

### ✅ Financial Transparency
- Real-time margin visibility
- No hidden costs
- Clear profitability per quote

### ✅ Error Prevention
- Alerts before sending unprofitable quotes
- Blocks acceptance of losing contracts
- Requires approval for risky margins

### ✅ Data-Driven Decisions
- See which items have low margins
- Identify opportunities to increase prices
- Compare margins across funerals

### ✅ Configurable per Agency
- Small agency: lower thresholds
- Large agency: higher thresholds
- Same code, different config

---

## Future Enhancements

- 📊 **Margin Analytics Dashboard** - Trends over time
- 💰 **Price Recommendations** - AI suggests optimal prices
- 📦 **Quote Templates** - Pre-configured packages
- 📧 **Email Alerts** - Notify manager of low-margin quotes
- 🔄 **Historical Comparison** - Compare with past quotes

---

**Result:** No more unprofitable contracts! 🎉
