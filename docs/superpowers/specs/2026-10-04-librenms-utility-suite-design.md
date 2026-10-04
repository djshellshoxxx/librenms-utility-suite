# LibreNMS Utility Suite v0.1 Specification

Version: 0.1  
Status: Initial implementation specification  
Repository: `djshellshoxxx/librenms-utility-suite`

## 1. Purpose

LibreNMS Utility Suite is an upgrade-safe modular plugin that adds small operational and documentation features to LibreNMS without modifying LibreNMS core.

Every feature is implemented as an independent module and can be enabled or disabled from the plugin settings interface.

The initial release contains:

1. Device Notes History
2. Device Bookmarks
3. Port Notes
4. Device QR Codes
5. Ping/SNMP Diagnostics
6. Description Auditor
7. Port Flap Counter
8. Device Tags

The suite also provides centralized configuration, module enable/disable controls, permission enforcement, a unified dashboard, database migrations, upgrade-safe module loading, and API-ready internal service boundaries.

## 2. Core Requirements

The plugin MUST:

- install without modifying LibreNMS core files
- use supported LibreNMS/Laravel plugin mechanisms
- tolerate LibreNMS upgrades as far as the public plugin interface permits
- keep all plugin tables clearly namespaced
- allow every feature module to be disabled independently
- avoid loading disabled module hooks, scheduled jobs, routes, or UI elements where practical
- respect LibreNMS authentication and device access permissions
- never expose SNMP credentials through the browser
- use CSRF protection for modifying operations
- validate all device and port IDs against the current user's LibreNMS access
- support clean uninstall without silently deleting operational history unless explicitly requested

## 3. Architecture

Suggested structure:

```text
librenms-utility-suite/
├── composer.json
├── README.md
├── LICENSE
├── CHANGELOG.md
├── src/
│   ├── UtilitySuiteServiceProvider.php
│   ├── UtilitySuitePlugin.php
│   ├── Contracts/
│   │   └── UtilityModule.php
│   ├── Modules/
│   │   ├── NotesHistory/
│   │   ├── Bookmarks/
│   │   ├── PortNotes/
│   │   ├── QrCodes/
│   │   ├── Diagnostics/
│   │   ├── DescriptionAudit/
│   │   ├── PortFlaps/
│   │   └── DeviceTags/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   ├── Services/
│   ├── Policies/
│   └── Console/
├── config/
│   └── utility-suite.php
├── database/
│   └── migrations/
├── routes/
│   └── web.php
├── resources/
│   ├── views/
│   └── lang/
└── tests/
    ├── Unit/
    ├── Feature/
    └── Integration/
```

## 4. Module Contract

All modules implement a common interface:

```php
interface UtilityModule
{
    public function key(): string;
    public function name(): string;
    public function enabled(): bool;
    public function boot(): void;
}
```

A module registry determines which modules are enabled. Disabled modules MUST NOT register unnecessary navigation items, scheduled work, device widgets, port widgets, or background collectors. Existing historical data is preserved when a module is disabled.

## 5. Settings

The suite provides a Tools > Utility Suite area with Dashboard, Bookmarks, Description Audit, Port Stability, Tags, QR Labels, and Settings.

Each module can be independently enabled or disabled. Module-specific settings live under the same settings surface and are stored in plugin-owned persistence rather than requiring manual LibreNMS config-file edits.

## 6. Device Notes History

Maintain an audit trail of LibreNMS device notes with timestamp, user, previous text, new text, source where available, human-readable history, diff view, and chronological timeline.

Suggested table:

```text
lus_device_note_history
id
device_id
user_id
previous_notes
new_notes
source
created_at
```

Records are append-only during normal operation.

## 7. Device Bookmarks

Provide per-user favorite devices. Bookmarks are sortable, removable, isolated by user, and restricted to devices the user can currently access. Schema should permit bookmark folders later, but folders are not required for v0.1.

## 8. Port Notes

Enhance LibreNMS native port notes without replacing the native value. Add note history, author, timestamps, optional tags, and a change timeline.

Suggested table:

```text
lus_port_note_history
id
port_id
device_id
user_id
previous_notes
new_notes
created_at
```

## 9. Device QR Codes

Produce QR labels linking physical hardware to its LibreNMS record. Supported targets: LibreNMS device page, hostname, management IP, management URL, and configurable custom target.

v0.1 includes single-device QR, PNG/SVG rendering, and printable browser view. Bulk label generation and PDF export are deferred.

## 10. Device Diagnostics

Device pages receive Ping and SNMP Test actions. A lightweight DNS test may be included.

Ping displays sent/received/loss/min/avg/max and PASS/WARNING/FAIL/TIMEOUT status.

SNMP Test requests harmless system information using the existing LibreNMS device SNMP configuration: `sysName`, `sysDescr`, `sysObjectID`, and `sysUpTime`.

The browser MUST NOT receive community strings, SNMPv3 authentication passwords, or SNMPv3 privacy passwords. Tests execute server-side with strict timeouts and execution limits.

## 11. Description Auditor

Detect poor interface documentation. Initial configurable checks:

- blank description
- description shorter than configurable length
- duplicate description on the same device
- `temp`
- `test`
- `unknown`
- `unused`
- default/generic strings

v0.1 audits interface descriptions only. The architecture should permit later checks of device location, contact, hostname, asset information, and serial information.

## 12. Port Flap Counter

Identify unstable interfaces by counting operational-state transitions over 24 hours, 7 days, and 30 days. Thresholds are configurable, not hard-coded.

The module can ignore administratively disabled interfaces, explicitly excluded interfaces, and excluded device types. It should use existing LibreNMS state/event history first and add plugin-owned caching only if proven necessary for performance.

## 13. Device Tags

Provide manually assigned metadata independent of dynamic LibreNMS device groups. Support create/edit/delete-unused, assign/remove, filter, and search. Multiple tags use AND semantics by default.

## 14. Unified Dashboard

The landing page summarizes enabled modules: bookmarked devices, tagged devices, description problems, flapping ports, notes changed this week, and recent plugin activity. Cards for disabled modules disappear.

## 15. Database Namespace

All plugin-owned tables use the `lus_` prefix.

Initial tables:

```text
lus_settings
lus_device_note_history
lus_bookmarks
lus_port_note_history
lus_tags
lus_device_tags
lus_port_tags
```

Avoid duplicating LibreNMS data unnecessarily.

## 16. Permissions

Use LibreNMS authentication and device visibility wherever possible. Every request referencing a device or port must independently verify current user access.

Minimum permission concepts:

```text
utility.view
utility.configure
notes_history.view
bookmarks.use
port_notes.view
port_notes.edit
qr.generate
diagnostics.ping
diagnostics.snmp
description_audit.view
port_flaps.view
tags.view
tags.edit
```

If separate plugin permissions cannot integrate cleanly with the LibreNMS permission model, map behavior to existing user/admin privileges.

## 17. API

Internal service classes should be API-friendly even if v0.1 does not expose a complete public REST API. Public API support is not a blocker for v0.1.

## 18. Error Handling

User-facing diagnostics distinguish PASS, WARNING, FAIL, TIMEOUT, PERMISSION DENIED, and NOT SUPPORTED. Errors must never expose raw credentials or sensitive command strings.

## 19. Performance

The plugin MUST avoid causing normal LibreNMS polling delays. Diagnostics run only on demand; description auditing queries existing data; flap processing initially uses existing stored history; expensive global calculations may use caching; no module may block the poller; no continuously running daemon is required for v0.1.

## 20. Logging

Log module enable/disable, diagnostic requests/timeouts, tag lifecycle events, migration failures, and module initialization failures. Passwords and SNMP credentials must never appear in logs.

## 21. Testing Requirements

Tests are required for every module.

Core: installation, migrations, module registration, enable/disable behavior, settings persistence, authorization, route access.

Notes: first note, changed note, unchanged note, history ordering, diff generation.

Bookmarks: add, duplicate prevention, remove, user isolation, inaccessible-device handling.

Port Notes: history creation, unchanged note handling, permissions.

QR: encoded destination, invalid device, unauthorized device, escaping.

Diagnostics: successful ping, lost packets, timeout, successful SNMP, invalid response, timeout, process execution failure.

Description Auditor: blanks, flagged patterns, duplicate detection, configurable exclusions.

Port Flaps: known transitions count predictably and repeated same-state observations do not count as flaps.

Tags: creation, assignment, removal, duplicate assignment, filtering, permissions.

## 22. Non-Goals for v0.1

Do not add AI analysis, cloud dependencies, external SaaS, packet capture, Elasticsearch/OpenSearch, Redis requirement, machine learning, topology analysis, MAC tracing, root-cause analysis, or Oxidized configuration parsing.

## 23. Upgrade Philosophy

The suite behaves like a guest inside LibreNMS: own its tables, routes, and views; use supported hooks; avoid monkey-patching core classes; avoid editing LibreNMS templates directly; fail gracefully if an optional integration changes. LibreNMS core modifications are not acceptable for normal installation.

## 24. Development Order

Phase 1: plugin skeleton, module registry, settings, migrations, navigation, test framework.

Phase 2: bookmarks, device tags, QR codes.

Phase 3: description auditor, port flap counter.

Phase 4: device notes history, port notes history.

Phase 5: ping/SNMP diagnostics.

Phase 6: unified dashboard, hardening, permissions review, integration testing, documentation.

## 25. v0.1 Completion Criteria

Version 0.1 is complete when the plugin installs on a supported LibreNMS installation without core changes; all eight modules can independently be enabled/disabled; bookmarks, tags, QR codes, description auditing, port flap counts, device note history, port note history, ping, and SNMP diagnostics work; authorization is enforced; credentials are never disclosed; automated tests pass; installation and uninstall behavior are documented; and the plugin does not interfere with LibreNMS polling.

## 26. Future Module Compatibility

The module system should permit later additions such as Quick Maintenance, MAC/IP Path Trace, Alert Rule Explainer, Poller Profiler, Monitoring Coverage Auditor, Asset Resolver, Topology Auditor, Optical Health Analyzer, and Configuration Change Intelligence without redesigning the core suite.
