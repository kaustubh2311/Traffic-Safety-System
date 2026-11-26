# Implementation Roadmap

## Governance Constraint
Do not restructure or migrate destructively until approval. Preserve the extracted starter and existing database data. The supplied starter is already MySQL/MariaDB, so no engine migration is presently indicated.

## Phase 1: Audit and Baseline — COMPLETE
- [x] Inspect SRS and extracted starter.
- [x] Inventory PHP, CSS, JavaScript and SQL.
- [x] Identify implemented and missing modules.
- [x] Record database and security discrepancies.
- [x] Produce initial audit documents.

## Phase 2: Database Architecture and Safe Migration Planning
- Define final normalized schema and foreign keys.
- Compare existing tables/columns and preserve legacy values.
- Create forward-only migrations from the existing MySQL schema.
- Add transaction-safe seeders and synthetic demo accounts.
- Add backups and restore verification.
- Approval needed before changing the live database or moving evidence paths.

## Phase 3: MVC and Configuration
- Add environment configuration, routes and controllers.
- Separate validation, services, repositories and views.
- Centralize session, error, CSRF and flash handling.
- Keep existing public URLs during migration for compatibility.
- No feature rewrite until database compatibility is established.

## Phase 4: Authentication and RBAC
- CITIZEN, TRAFFIC_OFFICER and ADMIN role tables/permissions.
- Citizen registration/login/logout and password reset.
- Officer/admin provisioning only by approved administrators.
- Account activation, session timeout and login throttling.
- Server-side permission checks for every route/action.

## Phase 5: Citizen Portal
- Citizen dashboard and profile.
- Ownership-based report list and tracking.
- Optional vehicle registration.
- Observed date/time/address/GPS fields.
- Submission receipt and status timeline.

## Phase 6: Violation Reporting and Evidence
- Normalize categories/evidence metadata.
- Add transaction and orphan cleanup rules.
- Strengthen upload signatures, count and size validation.
- Protected evidence streaming with assignment/role authorization.
- Duplicate warnings and unique tracking references.

## Phase 7: Officer Workspace
- Assigned/unassigned/incoming queues.
- Central state transition service.
- Request information, verify and reject actions.
- Mandatory rejection reason; private notes; public response separation.
- Assignment and duplicate-linking audit actions.

## Phase 8: Rules and Notices
- Offence-rule CRUD and effective-date validation.
- Draft preparation for verified reports.
- Permission-gated approval/issue workflow.
- Notice status, due date, dispute/correction/cancellation and audit.
- Explicit prototype disclaimer and no official e-challan claims.

## Phase 9: Administration
- User, role and account management.
- Officer assignment/workload.
- Audit log viewer and system settings.
- Retention and backup controls.

## Phase 10: Maps and Analytics
- Leaflet/OpenStreetMap adapter and graceful fallback.
- Server-side date/category/status/geographic filters.
- Marker/cluster and hotspot aggregation distinguished from confirmed incidents.
- Chart.js trends/distributions and officer workload.
- CSV export and print-friendly reports.

## Phase 11: Notifications and Integrations
- In-app notifications for material events.
- Optional provider adapters for email/SMS.
- Minimal notification content and delivery failure logging.

## Phase 12: Security and QA
- Security headers, session hardening and environment secrets.
- SQL injection/XSS/CSRF/upload/IDOR test cases.
- Transition, upload and transaction tests.
- Browser, viewport and accessibility checks.

## Phase 13: Documentation and Deployment
- Complete README, API, test, database and deployment docs.
- Backup/restore evidence.
- Demo-data reset and smoke test.

## Priority Delivery Sequence
P1: auth/RBAC, citizen submission ownership, complete review states, evidence protection, rules/notices, dashboard/map baseline and backup.
P2: assignment, duplicate linking, password reset, notifications, trends, export and advanced filters.
P3: SMS/email providers and optional external integrations.
