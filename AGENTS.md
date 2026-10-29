# Ignite - AI Agent Guidelines & Instructions

## 1. Core Testing Mandate

> **CRITICAL RULE - Targeted Unit Testing Only**:
> Do NOT waste time running unit tests across the whole application.
> Always identify and run **only** the targeted test file(s) or specific test method(s) relevant to the code you touched, changed, or created.

### Targeted Testing Commands
Run targeted PHPUnit tests using SQLite in-memory:
```bash
# Run a specific test file relevant to your changes
DB_CONNECTION=sqlite DB_DATABASE=:memory: ./vendor/bin/phpunit tests/Feature/TicketManagementTest.php

# Run a specific test method relevant to your changes
DB_CONNECTION=sqlite DB_DATABASE=:memory: ./vendor/bin/phpunit tests/Feature/TicketManagementTest.php --filter=test_ticket_creator_can_view_edit_page
```

---

## 2. Project Architecture & Standards

- **Stack**: Laravel 11+, PHP 8.3+, Bootstrap 5.3, Vite.
- **PHP 8.3 Standards**: Use typed properties, return type declarations, constructor property promotion, and PHP 8 attributes consistently.
- **Transactions**: Multi-step database actions must be wrapped in `DB::transaction()`.
- **Validation**: Enforce authorization and explicit request validation.
- **UI & Blade Consistency**: Use Bootstrap 5 utility classes and project conventions (`.fd-card`, `.badge-open`, `.badge-assigned`, `.badge-review`, `.badge-critical`). No direct DB queries inside Blade views.
