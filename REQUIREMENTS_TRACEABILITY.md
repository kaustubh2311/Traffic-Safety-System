# Requirements Traceability

## Legend
- **Implemented**: inspected working code
- **Partial**: some behavior exists
- **Missing**: no meaningful implementation
- **Conflict**: implementation differs from SRS

| Requirement | Status | Evidence / Gap |
|---|---|---|
| FR-001 Citizen registration/unique login | Missing | No citizen account table/workflow; authority-only users |
| FR-002 Authenticated sign-in/generic failure | Partial | Authority login password_verify and generic user/password message; no citizen login or throttling |
| FR-003 Server-side RBAC | Partial | `require_admin()` protects authority pages; no CITIZEN/OFFICER permission matrix |
| FR-004 Sign out/inactivity | Partial | Logout exists; no timeout configuration or POST/CSRF logout |
| FR-005 Password reset | Missing | No reset-token workflow |
| FR-006 Security-event audit | Missing | Only status history exists |
| FR-007 Report fields | Conflict | Category/location/description exist; vehicle wrongly required; no coordinates/map/GPS |
| FR-008 Required-field validation | Partial | Server validation exists but no date/time coordinate model |
| FR-009 Location map/coordinates/address | Partial | Text address only |
| FR-010 Tracking reference | Implemented | Random 6-byte suffix plus date prefix; unique constraint |
| FR-011 Submission receipt | Implemented | Session confirmation after commit |
| FR-012 Duplicate warning | Missing | No duplicate detection |
| FR-013 Own-report status | Conflict | Reference+email lookup; no authenticated citizen ownership |
| FR-014 File types/count/size | Partial | 5 MB, type/count one file, WebP extra outside baseline |
| FR-015 Content/type validation | Partial | MIME finfo only; no real signature/dimension verification |
| FR-016 Unpredictable evidence filename | Implemented | 24 random bytes; metadata path in database |
| FR-017 Authority review queue | Partial | List exists; no assignments/status queue separation |
| FR-018 Evidence/decision/internal notes | Partial | Evidence and remarks exist; no private-review separation |
| FR-019 Need information | Missing | No NEED INFO state/request workflow |
| FR-020 Verify/reject reason | Partial | Verify/reject statuses exist; reject reason not mandatory/structured |
| FR-021 Status history | Partial | Initial and changed statuses recorded; no audit actor for every sensitive action |
| FR-022 Assignment/workload | Missing | No assignments table/dashboard |
| FR-023 Duplicate linking | Missing | No duplicate records/action |
| FR-024 Offence catalogue | Missing | No rule table |
| FR-025 Admin rule management/audit | Missing | No rule admin |
| FR-026 Notice draft | Missing | No notices/challans |
| FR-027 Notice eligibility/authorization | Missing | No notice workflow |
| FR-028 Notice status/actor/timestamps | Missing | No notice entity |
| FR-029 Due date/overdue | Missing | No notice workflow |
| FR-030 Notice lifecycle statuses | Missing | No notice workflow |
| FR-031 Official-challan disclaimer | Implemented | UI disclaimers clearly identify prototype status |
| FR-032 Dispute/correction/cancellation | Missing | No notice workflow |
| FR-033 Status timeline/request | Partial | Status history exists for authority changes; no citizen request model/timeline page |
| FR-034 In-app notifications | Missing | No notifications |
| FR-035 Email/SMS adapter | Missing | None |
| FR-036 Minimum notification details | Missing | No notification implementation |
| FR-037 Map reports | Missing | No coordinates or Leaflet |
| FR-038 Map/report filters | Partial | Authority report search/status only; no date/map/geographic filtering |
| FR-039 Geographic aggregation | Missing | Location-text grouping, not geographic aggregation |
| FR-040 Hotspot basis labels | Partial | Text analytics warn it is not geospatial/verified |
| FR-041 Authority dashboard counts | Partial | Total/submitted/review/verified, no rejected/notices/date period |
| FR-042 Trends/category intervals | Partial | Category/status totals; no time trends |
| FR-043 CSV/print | Missing | No export |
| FR-044 Public aggregated analytics | Missing | Public analytics not exposed |
| FR-045 Reporting period/timezone | Missing | No filters/timezone labels |
| FR-046 Account activation/role | Missing | Authority setup and login only |
| FR-047 Sensitive audit logs | Missing | No audit log |
| FR-048 Audit actor/object summary | Missing | No general audit entries |
| FR-049 Retention | Missing | No data lifecycle controls |
| FR-050 Backup/restore procedure | Missing | No procedure/test evidence |
| NFR-001 3-second representative actions | Not verified | No load testing |
| NFR-002 Availability | Not verified | Local prototype only |
| NFR-003 Usability | Partial | Labels, inline validation and responsive CSS; not end-to-end tested |
| NFR-004 Browser/mobile compatibility | Partial | Responsive CSS; no documented test matrix |
| NFR-005 HTTPS/security controls | Partial | Local secure cookie; no HSTS/deployment |
| NFR-006 Privacy/evidence/retention | Partial | Evidence protected; no citizen accounts/retention |
| NFR-007 Integrity/transactions | Partial | Report and status writes transactional; no transition service |
| NFR-008 Auditability | Missing | Status history only |
| NFR-009 Maintainability | Partial | Shared helpers but no MVC/services/config abstraction |
| NFR-010 Scalability/pagination | Partial | Report list hard-limits 250; no pagination |
| NFR-011 Recovery | Missing | No backup/restore testing |
| NFR-012 Accessibility | Partial | Labels and status text; no formal keyboard/contrast validation |

## Priority SRS Gaps
- P1 authentication/RBAC, citizen tracking ownership, complete report validation, evidence, review workflow, notices, dashboard/maps baseline and audit.
- P2 assignment, duplicate, reset, notifications and reporting/export.
