# Project Audit

## Audit Scope
- SRS: `T6_SRS.docx` (Team 6, v1.0)
- Supplied archive: `Team_6_Traffic_Safety_System.zip`
- Extracted under: `source_audit/traffic-safety-system/`
- Review date: 2026-11-25

## Executive Summary
The starter is a compact MySQL/MariaDB PHP prototype, not a PostgreSQL application. It provides a usable public report submission, reference/email tracking, authority login, report queue, basic review and text-based analytics. It does not implement the SRS’s citizen account model, dedicated officer/admin roles, MVC architecture, geolocation/maps, offence-rule/notice management, notifications, or comprehensive audit/security controls.

## Architecture Analysis
- Server-rendered PHP pages; shared bootstrap, functions and header/footer includes.
- Procedural page-controller style rather than MVC.
- MySQL/MariaDB schema with PDO prepared statements.
- Sessions used for authentication and flash data.
- Direct relative paths and URL constants; no router or dependency container.
- Evidence stored on local disk with metadata only as a report-relative path.
- No framework, migrations framework, tests or external service adapters.

## Existing Working Modules
- Public homepage.
- Report submission with required-field validation and CSRF protection.
- JPG, PNG and WebP MIME checks and 5 MB upload limit.
- Random evidence filename and random report tracking suffix.
- Submission confirmation and reference/email lookup tracking.
- Authority login/logout and session login guard.
- Dashboard summary and recent reports.
- Status filtering and text search.
- Report detail and evidence view for signed-in authority users.
- Status history insertion for initial submission and review changes.
- Text-based category, status and location summaries.
- Shared output escaping and responsive CSS.

## Incomplete / Missing Modules
- Citizen registration, login, profile, notifications and account ownership.
- Dedicated CITIZEN, TRAFFIC_OFFICER and ADMIN role model; officer provisioning; user management.
- Password reset, account activation/deactivation and login throttling.
- Session timeout and account-status enforcement.
- Latitude/longitude fields, GPS/map pin selector and Leaflet/OpenStreetMap.
- Evidence metadata table, integrity hash and evidence count enforcement.
- Duplicate detection and duplicate-linking workflow.
- Required status lifecycle: DRAFT, SUBMITTED, UNDER REVIEW, NEED INFO, VERIFIED, REJECTED, NOTICE ISSUED and CANCELLED.
- Valid transition matrix, reason codes, internal notes and request-information workflow.
- Officer assignment, queues and workload monitoring.
- Offence-rule catalogue and rule-effective-date lookup.
- Notice drafting, approval/issue state, dispute/cancellation workflow.
- Chart.js, CSV export, print report, date/geographic/status filters and trend analytics.
- Notification entity/delivery, password-reset tokens and in-app notifications.
- Comprehensive audit log, audit-sensitive access and configuration settings.
- CSRF coverage on sign-out and route-hardening review.
- Database migrations, seeders, test suite and restore-test evidence.
- HTTPS/deployment configuration and environment-based secrets.

## Feature Completion Estimate
An evidence-based weighted estimate is **approximately 28% complete**.

| Area | Completion |
|---|---:|
| Public information and UI | 50% |
| Citizen reporting basics | 45% |
| Authentication and RBAC | 20% |
| Officer review workflow | 25% |
| Administration | 15% |
| Offence rules/notices | 5% |
| Maps/hotspots | 5% |
| Analytics/reporting | 20% |
| Notifications | 0% |
| Security/data integrity | 35% |
| Testing/recovery | 5% |

This is not a claim of production readiness; it represents the current prototype’s visible and auditable implementation.

## Database Discrepancy
The SRS correctly targets MySQL 8.x/MariaDB-compatible relational design. The supplied archive also uses MySQL/MariaDB, so it does **not** exhibit the PostgreSQL-to-MySQL discrepancy described in the project brief. A conversion was therefore not needed. The existing schema is nevertheless incomplete relative to the SRS.

## Identified Security Vulnerabilities
1. **No citizen authentication or ownership model:** report email is used as a shared tracking secret; no account records reports to authenticated citizens.
2. **No login-rate limiting or failed-login audit:** brute-force resistance is absent.
3. **No inactivity timeout:** sessions expire only with logout/browser session lifecycle.
4. **No account-status enforcement:** disabled/pending accounts are not modeled.
5. **Role distinction is weak:** only `admin` and `officer`, with both shown as `admin` session and no server permission matrix.
6. **Initial setup route can remain exposed:** it is disabled after a user exists, but remains executable and should be removed after provisioning.
7. **Setup race condition:** concurrent setup requests may both pass the existence check.
8. **Reviewer can freely change between all available statuses:** there is no valid transition matrix or terminal-state enforcement.
9. **Reviewer remarks are directly shown in public tracking:** no separation exists between public response and private officer notes.
10. **Hard-coded illustrative penalty in the review form/database:** violates configurable-rule requirements.
11. **No general audit log:** no actor/time/object log for login, role, account or notice events.
12. **No login CSRF?** Login POST is CSRF-protected, but low-quality generic login handling may still benefit from pre-auth/session controls.
13. **Sign-out is an unauthenticated GET action:** could create benign logout-CSRF behavior but should use POST and CSRF.
14. **Source configuration contains database settings:** although local defaults are non-secret, secrets would be public if committed; environment configuration is required.
15. **Evidence download is authority-only but uses report ID:** authorization is role-only, not case/assignment scope.
16. **No evidence MIME corruption validation beyond finfo MIME:** file signatures, dimensions and re-encoding should be verified.
17. **No duplicate submission protection/rate limit:** repeated requests can consume storage or create duplicate records.
18. **No security headers:** CSP, Referrer-Policy and HSTS are not configured.
19. **Raw upload move precedes transaction commit:** storage file cleanup is implemented on application errors, but a crash may orphan evidence.
20. **Date handling uses server timezone, not configured UTC/timezone policy.**

## Data Integrity and Architectural Issues
- `vehicle_number` is mandatory in schema/form although the SRS makes it optional.
- The category is free stored text in reports rather than a foreign key to a category table.
- No latitude, longitude, date-range or coordinate constraints exist.
- No evidence_files table, assignment table, reviews table, users/role normalization or notice/rule tables.
- `officer_notes` mixes public and internal information.
- No `Need Info`, notice workflow or `DRAFT/SUBMITTED/NOTICE ISSUED/CANCELLED` states.
- Status history stores a row for unchanged status updates, despite status history being for transitions.
- `penalty_amount` is embedded in report data and user-editable.
- Public tracking discloses vehicle number, location and description when reference/email are known.
- SQL is generally parameterized, but MySQL exception text is not hidden in the UI; safe logging is mostly followed.
- Indexes are sparse for the eventual workload and no migrations exist.

## Requirement Conflict / Clarification Needed
- Is public track lookup by reference + email the approved secure verification step, or must citizen accounts be mandatory?
- Which actor may “issue” notices: only ADMIN, or also selected OFFICER accounts?
- Are `NEED INFO`, duplicate linking and reopening final-state exceptions in scope P1/P2?
- Confirm jurisdiction, notice terminology, evidence retention and demo-data policy.
- Confirm Leaflet provider/fallback and whether public analytics are enabled.

## Recommended Architecture
Retain the working prototype visually and as a source baseline, but implement a gradual modularization:
1. Introduce MVC-style controllers, repositories/models and services while preserving URLs as compatibility routes.
2. Use centralized bootstrap/configuration for environment variables, sessions and database.
3. Add normalized MySQL schema and migrations.
4. Implement auth/RBAC before all authority and citizen owner pages.
5. Centralize report workflow and notice transition services.
6. Add protected evidence metadata and authorized file streaming.
7. Use Leaflet/Chart.js only after the server-side filtering and reporting APIs exist.
8. Add PHPUnit/PHPUnit-like integration tests and browser smoke tests.

## Initial Feature Implementation Checklist
- [ ] Citizen account registration/login/profile
- [ ] Password reset and account-status controls
- [ ] Session timeout/rate limiting/CSRF audit
- [ ] Citizen-owned report dashboard/tracking
- [ ] Optional vehicle field and separate evidence metadata
- [ ] GPS/coordinates/address with Leaflet fallback
- [ ] Duplicate warning and controlled linking
- [ ] Complete status lifecycle and transition matrix
- [ ] Request-information and rejection reason
- [ ] Officer assignments and workloads
- [ ] Offence-rule and notice management
- [ ] Admin user/role management
- [ ] Audit and notification modules
- [ ] Date/category/status/geographic filters
- [ ] Chart.js and CSV/print reports
- [ ] Backups, migration and restore test
- [ ] Security hardening and automated tests
