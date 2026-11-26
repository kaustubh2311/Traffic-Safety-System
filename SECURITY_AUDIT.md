# Security Audit

## Overall Rating
**High-risk prototype:** suitable for local demonstration only. It is not currently adequate for real reports, sensitive evidence or public deployment.

## Positive Controls
- PDO prepared statements for user-driven SQL.
- Password hashing with `password_hash()` and verification with `password_verify()`.
- CSRF token for report and authority mutations.
- Shared HTML output escaping.
- HttpOnly and SameSite session cookies; Secure enabled on HTTPS.
- Random evidence filenames and 5 MB upload limit.
- Server-side actual MIME detection through Fileinfo.
- Protected evidence endpoint and `.htaccess` directory denial.
- Session ID regeneration after authority login.
- Basic authority route guards.

## Critical / High Findings
| ID | Finding | Risk | Required Fix |
|---|---|---|---|
| SEC-01 | No citizen authentication/ownership | Unauthorized disclosure of report details | Add authenticated citizen model and ownership checks |
| SEC-02 | No login throttling or account lockout | Brute force | Add persisted attempt counters, backoff and audit |
| SEC-03 | No inactivity timeout or centralized policy | Stolen sessions remain usable | Use configurable timeout and idle invalidation |
| SEC-04 | `require_admin` does not implement officer/admin permission matrix | Privilege escalation or unauthorized action | Implement RBAC middleware/services and permission records |
| SEC-05 | Setup route remains in deployed source and can race | Unauthorized first-admin creation | Remove setup route after provisioning; lock with deployment secret or CLI |
| SEC-06 | Officer remarks public tracking | Internal data disclosure | Split public response and private notes |
| SEC-07 | Evidence authorization is role-only | Confidential evidence accessible beyond case scope | Assignment/role/object authorization |
| SEC-08 | Status updates allow invalid transitions | Data integrity/business abuse | Central transition validator and transactions |
| SEC-09 | Hard-coded penalty estimate | Misleading enforcement record | Remove; use active offence-rule DECIMAL amount |
| SEC-10 | No general audit log | Actions not attributable/traceable | Append-only audit log with actor/object/change summary |
| SEC-11 | No password reset or account-status controls | Account recovery/termination gaps | Add reset tokens, activation/deactivation, status checks |
| SEC-12 | Upload checks need stronger content validation | Malicious/corrupt file risk | Verify signatures, image decode, dimensions and safe reprocessing |

## Medium Findings
- Logout is GET and no CSRF. Change to POST.
- Database credentials in source. Move to environment variables and exclude `.env`.
- Tracking lookup can disclose data with a reference/email pair. Require approved citizen verification or stronger rate limiting/minimal response.
- `.htaccess` protection is Apache-specific and direct evidence URL routing must remain server-controlled.
- No security headers/CSP/HSTS.
- No duplicate submission/rate-limit controls.
- No server-side evidence hash or metadata table.
- Search/filter parameters are bounded by code but reporting should include pagination and limits.
- No formal privacy retention/anonymization process.
- No delivery/content provider adapters or secret handling.
- No automated SQL/XSS/CSRF/IDOR tests.

## Secure Coding Plan
1. Add server-side session and CSRF middleware to all mutations.
2. Regenerate sessions after login and privilege changes.
3. Normalize role checks and default deny all protected actions.
4. Add generic not-found/permission responses.
5. Stream protected files only after a data-level authorization check.
6. Store evidence outside web root and use immutable random storage keys.
7. Add transactions for report submission, status changes and notice creation.
8. Escape all output according to HTML context and return no raw exceptions.
9. Use environment configuration and production error logs only.
10. Add audit logging for login failures, role changes, evidence access, review decisions and notices.
11. Add security headers and HTTPS-only cookies in non-local deployments.
12. Add automated tests for IDOR, invalid transitions, uploads, CSRF and SQL/XSS payloads.

## Deployment Checks
- Remove setup route.
- Use a dedicated least-privilege MySQL account.
- Set strong database/cookie secrets and HTTPS.
- Disable display_errors and configure logs outside public paths.
- Block direct access to uploads/config/backups.
- Verify backups and restore in an isolated environment.
- Use synthetic demo data only.
