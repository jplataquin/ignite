# Ignite - Project Instructions & Guidelines

## 1. Project Overview
Ignite is an enterprise IT service management and ticketing platform built with **Laravel 11+**, **PHP 8.3+**, **Bootstrap 5.3**, and **Vite**. It provides ticket tracking, SLA monitoring, multi-tier categorization, organizational scoping (Divisions & Departments), chunked file uploads, and role-based access control.

---

## 2. Core Architecture & Domain Concepts

### Ticket Lifecycle, Stages, and Statuses
- **Ticket Stages (`TicketStage`)**: Represents the workflow step of a ticket via the `stage_id` foreign key.
  - Core slugs: `open`, `assigned`, `review`, `closed`, `canceled` (protected from deletion).
  - Admins can define custom stages with distinct display colors.
  - Workflow transitions:
    - New ticket: Stage defaults to `open`.
    - Ticket accepted/assigned: Stage updates to `assigned`.
    - Submitted for author review: Stage updates to `review`.
    - Review resolution: Stage transitions to `closed` or `canceled`, or reassigns back to `assigned`.
- **Ticket Statuses (`tickets.status`)**: A system-managed operational flag (`Valid`, `Done`, `Lapsed`).
  - `Valid`: The default status for active tickets undergoing processing across stages (`open`, `assigned`, `review`, etc.).
  - `Done`: Set when tickets are completed or canceled (`closed`, `canceled`).
  - `Lapsed`: Set automatically by `tickets:process-lapsed` when a ticket exceeds its calculated priority SLA deadline.
  - **Important**: Do not confuse `status` with `stage`. The dashboard "Open Tickets" card represents tickets in the `open` stage, not all `Valid` status tickets.

### Scoping & Permissions
- **Admin Users (`user_type === 'admin'`)**: Unrestricted access across all divisions, departments, ticket types, and master data settings.
- **Regular Users (`user_type === 'regular'`)**: Scoped to their assigned `division_id` and `department_id` (plus tickets in `review` stage assigned directly to them).

### File Attachments & Chunked Uploads
- Large file uploads utilize chunking (`staging/{temp_token}/{chunk}.part`).
- Allowed formats: WebP, PDF, Excel (`.xls`, `.xlsx`, `.csv`), and Word (`.doc`, `.docx`).
- On form submission, chunks are merged and moved to `attachments/{ticket_id}/{filename}` inside a database transaction.

### Audit Trails & Comments
- Changes to critical ticket attributes (stages, assignees, priorities) are observed by `TicketObserver` and logged as system event comments (`TicketComment` with `type = 'system_event'`).

---

## 3. Development Commands

### Environment & Setup
```bash
# Full initial setup
composer run setup

# Install dependencies manually
composer install
npm install

# Build / watch front-end assets
npm run dev
npm run build
```

### Testing
Run PHPUnit tests using the in-memory SQLite database:
```bash
# Run all tests
DB_CONNECTION=sqlite DB_DATABASE=:memory: ./vendor/bin/phpunit

# Run a specific test file
DB_CONNECTION=sqlite DB_DATABASE=:memory: ./vendor/bin/phpunit tests/Feature/DashboardTest.php

# Run a specific test method
DB_CONNECTION=sqlite DB_DATABASE=:memory: ./vendor/bin/phpunit tests/Feature/DashboardTest.php --filter=test_dashboard_open_tickets_card_only_counts_tickets_in_open_stage
```

### Code Formatting & Quality
```bash
# Run Laravel Pint to check and fix PHP code style
./vendor/bin/pint
```

### Scheduled Artisan Commands
```bash
# Check and mark expired tickets as Lapsed
php artisan tickets:process-lapsed

# Clean up orphaned chunk staging files
php artisan purge:staging-files

# Seed or create initial admin user
php artisan user:create-admin
```

---

## 4. Coding Standards & Conventions

1. **PHP 8.3 Features**: Use typed properties, return type declarations, constructor property promotion, and PHP 8 attributes (`#[Fillable]`, `#[ObservedBy]`) consistently.
2. **Database Transactions**: Any multi-step database action (e.g., ticket creation/update + attachment handling + comments) must be wrapped in `DB::transaction()`.
3. **Validation**: Validate requests with explicit validation rules and authorization checks before executing operations.
4. **Testing Mandatory**: Every new feature or bug fix must include comprehensive tests in `tests/Feature` or `tests/Unit`.
   - Verify both positive and negative cases.
   - Ensure tests use `RefreshDatabase` and execute cleanly against SQLite in-memory without external database dependencies.
5. **UI & Blade Consistency**:
   - Use Bootstrap 5 utility classes and Ignite theme conventions (`.fd-card`, `.badge-open`, `.badge-assigned`, `.badge-review`, `.badge-critical`).
   - Avoid executing direct database queries inside Blade views; pass all necessary data from controllers.
