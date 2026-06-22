# Auction Service Module Implementation Plan

## Executive Summary

This document outlines the implementation plan for a complete auction service module with Role-Based Access Control (RBAC). The auction module will allow vendors to list products for bidding, manage auction lifecycles, and handle bidder interactions. The implementation follows existing patterns in the codebase (bookings, subscriptions, product management) and integrates with the current module access system.

---

## 1. Architecture Overview

### 1.1 System Context

The auction module operates within the existing multi-vendor e-commerce platform, extending it with time-based bidding capabilities. It leverages:

- **Module Access System**: Uses `config/modules.php` for feature gating via subscription plans
- **RBAC via Roles**: Uses the existing `Role` model and `role_has_permissions` table
- **Laravel Policies**: For fine-grained authorization on auction entities
- **Service Layer**: Dedicated service classes for business logic
- **Scheduled Jobs**: For auction lifecycle automation (end, settle, extend)

### 1.2 Module Structure

```
app/
├── Models/
│   ├── Auction.php
│   ├── AuctionBid.php
│   ├── AuctionWinner.php
│   └── (existing models extended)
├── Services/
│   └── Auction/
│       ├── AuctionService.php
│       ├── BiddingService.php
│       └── SettlementService.php
├── Http/
│   ├── Controllers/
│   │   ├── AuctionController.php (public)
│   │   ├── Vendor/
│   │   │   └── AuctionController.php
│   │   └── Admin/
│   │       └── AuctionController.php
│   └── Middleware/
│       └── EnsureAuctionAccess.php (new)
├── Policies/
│   ├── AuctionPolicy.php
│   └── AuctionBidPolicy.php
└── Notifications/
    ├── AuctionEndingSoon.php
    ├── OutbidNotification.php
    ├── AuctionWonNotification.php
    └── AuctionSettledNotification.php

database/migrations/
├── XXXX_XX_XX_XXXXXX_create_auctions_table.php
├── XXXX_XX_XX_XXXXXX_create_auction_bids_table.php
└── XXXX_XX_XX_XXXXXX_create_auction_winners_table.php

routes/
├── web.php (public + vendor + admin routes)
└── api.php (REST API endpoints)

resources/views/
├── auctions/ (public auction pages)
├── vendor/auctions/ (vendor dashboard)
└── admin/auctions/ (admin management)
```

---

## 2. Database Schema

### 2.1 Core Tables

#### auctions table

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| vendor_id | BIGINT UNSIGNED | FK to vendors table |
| product_id | BIGINT UNSIGNED | FK to products table (optional - can create directly) |
| title | VARCHAR(255) | Auction title |
| description | TEXT | Auction description |
| starting_price | DECIMAL(12,2) | Minimum bid amount |
| reserve_price | DECIMAL(12,2) | Hidden minimum seller will accept |
| buy_now_price | DECIMAL(12,2) | Optional instant purchase |
| current_price | DECIMAL(12,2) | Current highest bid |
| bid_increment | DECIMAL(12,2) | Minimum bid increment |
| starts_at | DATETIME | Auction start time |
| ends_at | DATETIME | Auction end time |
| original_ends_at | DATETIME | Original end (for extensions) |
| extension_minutes | TINYINT | Auto-extend window in minutes |
| status | ENUM | draft, scheduled, active, ended, cancelled, settled |
| winner_id | BIGINT UNSIGNED | FK to users (nullable) |
| winning_bid_id | BIGINT UNSIGNED | FK to auction_bids |
| total_bids | INT | Count of bids |
| view_count | INT | Number of views |
| is_featured | BOOLEAN | Featured auction flag |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### auction_bids table

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| auction_id | BIGINT UNSIGNED | FK to auctions |
| user_id | BIGINT UNSIGNED | FK to users (bidder) |
| amount | DECIMAL(12,2) | Bid amount |
| is_winning | BOOLEAN | Current winning bid |
| is_auto_bid | BOOLEAN | Auto-bid flag |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### auction_winners table

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| auction_id | BIGINT UNSIGNED | FK to auctions |
| user_id | BIGINT UNSIGNED | FK to users |
| bid_id | BIGINT UNSIGNED | FK to auction_bids |
| final_price | DECIMAL(12,2) | Winning bid amount |
| status | ENUM | pending, paid, shipped, completed, cancelled, refunded |
| paid_at | DATETIME | Payment timestamp |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### auction_auto_bids table (for auto-bidding feature)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| auction_id | BIGINT UNSIGNED | FK to auctions |
| user_id | BIGINT UNSIGNED | FK to users |
| max_amount | DECIMAL(12,2) | Maximum amount willing to bid |
| is_active | BOOLEAN | Auto-bid active flag |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### auction_notifications table

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| auction_id | BIGINT UNSIGNED | FK to auctions |
| user_id | BIGINT UNSIGNED | FK to users |
| type | VARCHAR(50) | Notification type |
| is_read | BOOLEAN | Read status |
| created_at | TIMESTAMP | |

---

## 3. RBAC & Permissions Design

### 3.1 Permission Keys

All permissions follow the pattern `auction.<action>`:

| Permission | Description | Admin | Vendor | Customer |
|------------|-------------|-------|--------|----------|
| auction.view_any | View all auctions | ✓ | Own only | Own only |
| auction.create | Create auction | - | ✓ | - |
| auction.update | Edit own auction | - | ✓ | - |
| auction.delete | Delete own auction | ✓ | Own only | - |
| auction.cancel | Cancel own auction | ✓ | Own only | - |
| auction.manage | Full management | ✓ | - | - |
| auction.bid | Place bids | - | - | ✓ |
| auction.bid.view_any | View own bids | - | Own auction | ✓ |
| auction.settle | Settle auction | - | ✓ | - |
| auction.featured | Feature auctions | ✓ | - | - |

### 3.2 Role-Based Access Matrix

| Role | Create Auction | Bid | Manage Own | View All | Admin Manage |
|------|---------------|-----|------------|----------|--------------|
| Admin | ✓ | ✓ | ✓ | ✓ | ✓ |
| Vendor | ✓ | ✗ | ✓ | ✗ | ✗ |
| Salon Owner | ✓ | ✗ | ✓ | ✗ | ✗ |
| Customer | ✗ | ✓ | N/A | ✗ | ✗ |

### 3.3 Module Access Integration

The auction module integrates with the existing subscription-based module access system:

```php
// config/modules.php additions
'catalog' => [
    ['key' => 'auctions', 'name' => 'Auctions', 'implemented' => false],
]

'tiers' => [
    'starter' => [],
    'professional' => ['auctions'],
    'business' => ['auctions'],
    'enterprise' => ['auctions'],
]
```

Vendors require the 'auctions' feature key in their subscription plan to create/manage auctions.

---

## 4. Auction Types

### 4.1 Standard English Auction
- Forward auction: price increases with each bid
- Highest bidder at end wins
- Reserve price protection for sellers

### 4.2 Dutch Auction (Descending Price)
- Price starts high and decreases
- First bidder wins at current price

### 4.3 Sealed Bid Auction
- Bids are hidden until end
- Highest bidder wins
- No public bidding history

### 4.4 Buy Now Option
- Instant purchase at fixed price
- Ends auction immediately

---

## 5. Auction Lifecycle

```
[DRAFT] → [SCHEDULED] → [ACTIVE] → [ENDED] → [SETTLED]
                  ↓            ↓          ↓
              [CANCELLED]  [CANCELLED] [CANCELLED]
                                    ↓
                              [REFUNDED]
```

### 5.1 State Transitions

| From State | To State | Trigger |
|------------|----------|---------|
| draft | scheduled | Vendor publishes auction |
| scheduled | active | reaches starts_at time |
| active | ended | reaches ends_at time |
| active | cancelled | Seller or admin cancels |
| ended | settled | Winner completes payment |
| ended | cancelled | No winner/payment issues |
| settled | refunded | Refund initiated |

### 5.2 Automatic Extensions

If a bid is placed within the last X minutes (configurable per auction):
- End time extends by X minutes
- Ensures fair bidding wars

---

## 6. Service Layer Design

### 6.1 AuctionService

```php
class AuctionService
{
    public function create(array $data): Auction;
    public function update(Auction $auction, array $data): Auction;
    public function schedule(Auction $auction): Auction;
    public function activate(Auction $auction): Auction;
    public function cancel(Auction $auction, string $reason): Auction;
    public function end(Auction $auction): Auction;
    public function settle(Auction $auction): Auction;
    public function extend(Auction $auction, int $minutes): Auction;
    public function getActiveAuctions(array $filters): Collection;
    public function getEndedAuctions(array $filters): Collection;
}
```

### 6.2 BiddingService

```php
class BiddingService
{
    public function placeBid(Auction $auction, User $user, float $amount): AuctionBid;
    public function validateBid(Auction $auction, float $amount): bool;
    public function getBidHistory(Auction $auction): Collection;
    public function getUserBids(User $user): Collection;
    public function setAutoBid(User $user, Auction $auction, float $maxAmount): AutoBid;
    public function processAutoBids(Auction $auction): void;
    public function getCurrentWinningBid(Auction $auction): ?AuctionBid;
}
```

### 6.3 SettlementService

```php
class SettlementService
{
    public function determineWinner(Auction $auction): ?AuctionWinner;
    public function createWinningOrder(AuctionWinner $winner): Order;
    public function processPayment(AuctionWinner $winner, Payment $payment): bool;
    public function refundWinner(AuctionWinner $winner): bool;
    public function notifyWinner(AuctionWinner $winner): void;
    public function notifySellers(Auction $auction): void;
}
```

---

## 7. API Endpoints

### 7.1 Public API (Browse Auctions)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/auctions | List active auctions |
| GET | /api/v1/auctions/{slug} | Auction details |
| GET | /api/v1/auctions/{slug}/bids | Bid history (public) |
| GET | /api/v1/auctions/featured | Featured auctions |
| GET | /api/v1/auctions/ending-soon | Ending soon |
| GET | /api/v1/auctions/search | Search auctions |

### 7.2 Customer API (Authenticated)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/auctions/{id}/bid | Place a bid |
| GET | /api/v1/my-bids | User's bids |
| POST | /api/v1/auctions/{id}/auto-bid | Set auto-bid |
| DELETE | /api/v1/auto-bids/{id} | Cancel auto-bid |
| GET | /api/v1/my-winnings | User's winning auctions |

### 7.3 Vendor API (Authenticated + Vendor Role)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /vendor/auctions | Vendor's auctions |
| POST | /vendor/auctions | Create auction |
| GET | /vendor/auctions/{id} | Auction details |
| PUT | /vendor/auctions/{id} | Update auction |
| DELETE | /vendor/auctions/{id} | Delete auction |
| POST | /vendor/auctions/{id}/publish | Schedule/activate |
| POST | /vendor/auctions/{id}/cancel | Cancel auction |
| POST | /vendor/auctions/{id}/extend | Extend auction |
| GET | /vendor/auctions/{id}/bids | Auction bids |
| POST | /vendor/auctions/{id}/settle | Settle auction |

### 7.4 Admin API (Authenticated + Admin Role)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /admin/auctions | All auctions |
| GET | /admin/auctions/{id} | Auction details |
| PUT | /admin/auctions/{id} | Update auction |
| DELETE | /admin/auctions/{id} | Delete auction |
| POST | /admin/auctions/{id}/feature | Toggle featured |
| GET | /admin/auctions/reports | Auction reports |

---

## 8. View Components

### 8.1 Public Views

| View | Description |
|------|-------------|
| auctions/index | Auction listing with filters |
| auctions/show | Auction detail with bidding |
| auctions/category | Category-based auctions |
| auctions/search | Search results |

### 8.2 Vendor Views

| View | Description |
|------|-------------|
| vendor/auctions/index | Auction management dashboard |
| vendor/auctions/create | Create auction form |
| vendor/auctions/edit | Edit auction form |
| vendor/auctions/show | Auction detail with bids |
| vendor/auctions/settings | Default auction settings |

### 8.3 Admin Views

| View | Description |
|------|-------------|
| admin/auctions/index | All auctions management |
| admin/auctions/show | Auction detail |
| admin/auctions/reports | Analytics and reports |
| admin/auctions/settings | Global auction settings |

---

## 9. Scheduled Jobs

### 9.1 AuctionScheduler (Every minute)

```php
// Commands: auction:activate, auction:end, auction:settle
class AuctionScheduler
{
    // Activates scheduled auctions that reached start time
    public function activateScheduledAuctions(): void;

    // Ends active auctions that reached end time
    public function endActiveAuctions(): void;

    // Settles ended auctions (process winners)
    public function settleEndedAuctions(): void;

    // Sends ending soon notifications
    public function notifyEndingSoon(): void;

    // Cleans up old draft auctions
    public function cleanupDraftAuctions(): void;
}
```

### 9.2 Cron Schedule

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('auction:activate')->everyMinute();
    $schedule->command('auction:end')->everyMinute();
    $schedule->command('auction:settle')->everyMinute();
    $schedule->command('auction:notify-ending')->everyFiveMinutes();
    $schedule->command('auction:cleanup')->daily();
}
```

---

## 10. Notifications

| Notification | Trigger | Recipients |
|--------------|---------|------------|
| AuctionEndingSoon | 1 hour before end | Bidders, watchers |
| OutbidNotification | New higher bid | Previous high bidder |
| AuctionWonNotification | Auction ended | Winner |
| AuctionLostNotification | Auction ended | Non-winning bidders |
| AuctionSettledNotification | Payment complete | Seller, winner |
| NewAuctionNotification | New auction created | Followers, watchers |
| AuctionCancelledNotification | Auction cancelled | All bidders |

---

## 11. Implementation Phases

### Phase 1: Foundation (Week 1)
- [ ] Database migrations
- [ ] Core models (Auction, AuctionBid, AuctionWinner)
- [ ] Basic service layer
- [ ] Module configuration

### Phase 2: Core Features (Week 2)
- [ ] Public auction browsing
- [ ] Bidding functionality
- [ ] Vendor auction management
- [ ] Basic admin views

### Phase 3: Advanced Features (Week 3)
- [ ] Auto-bidding system
- [ ] Settlement workflow
- [ ] Notifications
- [ ] Scheduled jobs

### Phase 4: Polish (Week 4)
- [ ] Reports and analytics
- [ ] UI/UX improvements
- [ ] Testing
- [ ] Documentation

---

## 12. Integration Points

### 12.1 Existing Systems

| System | Integration |
|--------|-------------|
| Products | Link auctions to products, sync inventory |
| Orders | Create orders from settled auctions |
| Payments | Process winning bids |
| Notifications | Email/SMS/Push for auction events |
| Subscriptions | Module access for auction feature |
| Media | Auction images via MediaLibrary |
| Users | Role-based access |

### 12.2 Configuration Requirements

```php
// config/auction.php
return [
    'defaults' => [
        'duration_days' => 7,
        'extension_minutes' => 15,
        'bid_increment' => 1.00,
        'end_notification_hours' => 24,
    ],
    
    'limits' => [
        'max_auctions_per_vendor' => 50,
        'min_starting_price' => 0.01,
        'max_duration_days' => 30,
    ],
    
    'fees' => [
        'auction_fee' => 0.00,
        'commission_percent' => 5.0,
    ],
];
```

---

## 13. Testing Strategy

### 13.1 Unit Tests
- AuctionService business logic
- BiddingService validation
- SettlementService calculations

### 13.2 Feature Tests
- Full auction lifecycle
- Bid placement and validation
- Auto-bidding behavior

### 13.3 Integration Tests
- Payment processing
- Order creation from settlements
- Notification delivery

---

## 14. Security Considerations

1. **Bid Validation**: Server-side validation of all bid amounts
2. **Race Conditions**: Use database transactions for bid placement
3. **Price Manipulation**: Prevent bid sniping with extension system
4. **Fraud Detection**: Flag suspicious bidding patterns
5. **Access Control**: Strict role-based permissions
6. **Module Access**: Subscription-based feature gating
