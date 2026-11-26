# Database Design — Current State and Target Plan

## Current Engine
The supplied starter uses **MySQL/MariaDB through PDO**. It is already compatible with the SRS’s database engine requirement. Do not silently migrate; preserve the existing `traffic_safety_db` if approval is obtained for application changes.

## Current Tables
### `users`
- `id` BIGINT-compatible UNSIGNED INT
- full name, unique email, password hash
- ENUM: `admin`, `officer`
- created timestamp

Problems:
- No citizen role.
- No account status, updated timestamp or mobile field.
- No password-reset records or permissions.

### `reports`
- `id`, unique tracking reference
- denormalized reporter name/email
- vehicle number, violation type text, location text
- incident/submission timestamps, description
- evidence path, status, officer notes, illustrative penalty
- timestamps and sparse indexes

Problems:
- No reporter foreign key or latitude/longitude.
- Vehicle should be optional.
- Category and status should be lookup/reference entities.
- Officer notes/public response are mixed.
- Penalty should be on offence rule, not report.
- No assignment or evidence metadata.

### `report_status_history`
- report, old/new status, notes, changed actor and timestamp

Problems:
- ENUM is too narrow and incomplete.
- It allows status updates with no transition rules.
- It may record unchanged status as a pseudo-transition.

## Target Core Tables
- `users`, `roles`, `permissions`, `role_permissions`
- `violation_categories`
- `violations`
- `evidence_files`
- `reviews`
- `report_assignments`
- `report_status_history`
- `offence_rules`
- `notices`
- `notifications`
- `notifications` delivery metadata
- `audit_logs`
- `password_reset_tokens`
- `system_settings`
- optional `report_duplicates`

## Target Relationship Summary
- User 1:N Violations as reporter.
- Violation 1:N Evidence_Files.
- Violation 1:N Reviews and Status_History.
- Violation 1:N Report_Assignments or 1 active assignment.
- OffenceRule 1:N Notices.
- Violations 1:N Notices only under explicit exception handling.
- User 1:N Notifications/Audit entries.
- Role N:M Permissions.

## Integrity Requirements
- BIGINT UNSIGNED primary keys.
- Foreign keys with deliberate cascade/restrict policies.
- Unique normalized email and public tracking reference.
- DECIMAL(12,2) penalty/amount plus currency.
- Latitude DECIMAL/validated range -90 to 90.
- Longitude DECIMAL/validated range -180 to 180.
- UTC DATETIME or TIMESTAMP storage plus configured display timezone.
- Effective-date and overlap constraints for offence rules.
- Unique active rule model or exclusion/transaction enforcement.
- Versionless migration scripts and seeders.

## Proposed Indexes
- `users(email)` unique
- `users(role_id, account_status)`
- `violations(public_reference)` unique
- `violations(status_id, submitted_at)`
- `violations(reporter_id, submitted_at)`
- `violations(category_id, observed_at)`
- `violations(assigned_officer_id, status_id)`
- `violations(latitude, longitude)` or spatial index if workload supports
- `evidence_files(violation_id)`
- `status_history(violation_id, changed_at)`
- `offence_rules(code, jurisdiction, effective_from, effective_to)`
- `notices(notice_reference)` unique
- `audit_logs(actor_id, timestamp)`, `audit_logs(object_type, object_id)`

## Safe Migration Plan
1. Back up the existing database and evidence directory.
2. Add new tables without destructive edits.
3. Create migration/backfill scripts for existing users/reports/status history.
4. Map legacy role values to `CITIZEN/OFFICER/ADMIN` roles.
5. Map legacy report statuses to the final lifecycle.
6. Add report ownership only when citizen accounts are provisioned; do not invent identities.
7. Preserve evidence paths for legacy rows while introducing evidence metadata.
8. Add constraints and remove legacy fields only after dual-write/compatibility validation.
9. Seed demo users/rules/reports only in demo database.
10. Execute and restore-test migrations in a copy before production use.

No PostgreSQL conversion is needed for the supplied artifact.
