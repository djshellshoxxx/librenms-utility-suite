# LibreNMS Utility Suite v0.1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an upgrade-safe modular LibreNMS plugin that provides eight independently switchable operational utilities: device notes history, bookmarks, port notes history, device QR codes, ping/SNMP diagnostics, description auditing, port flap counting, and device tags.

**Architecture:** Implement the project as a Composer/Laravel-style LibreNMS plugin package with a small core module registry and one self-contained module per feature. Plugin-owned tables use the `lus_` prefix, disabled modules do not register unnecessary UI or work, and all device/port actions pass through access-control services before execution.

**Tech Stack:** PHP 8.x, Composer, Laravel/LibreNMS plugin APIs, Blade views, PHPUnit/Pest-compatible tests, standard LibreNMS database models and helpers, server-side ICMP/SNMP execution through bounded service wrappers.

**Spec:** `docs/superpowers/specs/2026-10-04-librenms-utility-suite-design.md`

## Global Constraints

- Do not modify LibreNMS core files.
- Use supported LibreNMS/Laravel plugin mechanisms.
- All plugin tables use the `lus_` prefix.
- Every feature module must be independently enableable/disableable.
- Disabled modules must not register unnecessary UI hooks, routes, scheduled work, or collectors.
- All device/port requests must verify current-user access.
- SNMP credentials must never be rendered to the browser or written to logs.
- Mutating web requests must use CSRF protection.
- Diagnostics run only on demand and must use strict execution timeouts.
- No external SaaS, Redis requirement, Elasticsearch/OpenSearch, AI, packet capture, or continuously running daemon in v0.1.

## Review Focus

- Disabled modules still leaving reachable routes or actions: tests must prove disabled modules return unavailable/404 behavior and do not expose navigation entries.
- Unauthorized device or port IDs: tests must prove direct URL access cannot bypass LibreNMS visibility rules.
- Credentials leaking through diagnostics errors/logging: tests must exercise failures with sentinel secrets and assert they never appear in responses or logs.
- Expensive flap/audit queries on larger datasets: tests should verify bounded query/service behavior and caching interfaces where appropriate rather than poller integration.
- Duplicate/history noise: tests must prove unchanged notes, duplicate bookmarks, duplicate tags, and repeated same-state port observations do not create duplicate records.

---

### Task 1: Package Skeleton, Test Harness, and Module Registry

**Files:**
- Create: `composer.json`
- Create: `src/UtilitySuiteServiceProvider.php`
- Create: `src/UtilitySuitePlugin.php`
- Create: `src/Contracts/UtilityModule.php`
- Create: `src/Core/ModuleRegistry.php`
- Create: `config/utility-suite.php`
- Create: `tests/Unit/Core/ModuleRegistryTest.php`
- Create: `phpunit.xml`

**Interfaces:**
- Produces: `UtilityModule::key(): string`, `name(): string`, `enabled(): bool`, `boot(): void`.
- Produces: `ModuleRegistry::__construct(iterable $modules)`, `enabled(): array`, `find(string $key): ?UtilityModule`.

- [ ] **Step 1: Write failing module-registry tests** covering enabled filtering, disabled filtering, lookup by key, unknown key, and duplicate-key rejection.
- [ ] **Step 2: Run the focused test** with `vendor/bin/phpunit tests/Unit/Core/ModuleRegistryTest.php` and verify failure before implementation.
- [ ] **Step 3: Implement the package metadata, module contract, registry, service provider shell, and default module configuration** with all eight modules represented in config.
- [ ] **Step 4: Re-run the focused test** and verify PASS.
- [ ] **Step 5: Commit** with `feat: add plugin core and module registry`.

### Task 2: Settings Persistence and Module Enable/Disable Semantics

**Files:**
- Create: `database/migrations/2026_10_04_000001_create_lus_settings_table.php`
- Create: `src/Models/Setting.php`
- Create: `src/Services/SettingsService.php`
- Create: `tests/Unit/Services/SettingsServiceTest.php`
- Modify: `src/Core/ModuleRegistry.php`

**Interfaces:**
- Consumes: module keys from Task 1.
- Produces: `SettingsService::get(string $key, mixed $default = null): mixed`, `set(string $key, mixed $value): void`, `moduleEnabled(string $moduleKey): bool`.

- [ ] **Step 1: Write failing settings tests** for defaults, persisted overrides, boolean normalization, unknown module fallback, and independent toggling of all eight modules.
- [ ] **Step 2: Run the focused settings test** and verify failure.
- [ ] **Step 3: Implement settings migration/model/service and connect registry enablement to persisted settings** while keeping configuration defaults as fallback.
- [ ] **Step 4: Add a test proving a disabled module is absent from `ModuleRegistry::enabled()`**.
- [ ] **Step 5: Run the focused tests** and verify PASS.
- [ ] **Step 6: Commit** with `feat: add modular settings persistence`.

### Task 3: Authorization Boundary and Plugin Routes

**Files:**
- Create: `src/Services/AccessService.php`
- Create: `src/Http/Middleware/RequireEnabledModule.php`
- Create: `routes/web.php`
- Create: `tests/Feature/AuthorizationBoundaryTest.php`
- Create: `tests/Feature/DisabledModuleRouteTest.php`

**Interfaces:**
- Produces: `AccessService::canViewDevice(User $user, int $deviceId): bool`, `canViewPort(User $user, int $portId): bool`, `requireAdmin(User $user): void`.
- Produces: module middleware that receives a module key and rejects requests when disabled.

- [ ] **Step 1: Write failing feature tests** for allowed device access, denied device access, denied port access, admin-only settings, and disabled-module routes.
- [ ] **Step 2: Run the focused feature tests** and verify failure.
- [ ] **Step 3: Implement access wrappers using LibreNMS-native authorization mechanisms where available, keeping all project code behind `AccessService`**.
- [ ] **Step 4: Implement module middleware and route groups** so disabled modules cannot be invoked by direct URL.
- [ ] **Step 5: Re-run feature tests** and verify PASS.
- [ ] **Step 6: Commit** with `feat: enforce module and device authorization`.

### Task 4: Bookmarks Module

**Files:**
- Create: `database/migrations/2026_10_04_000002_create_lus_bookmarks_table.php`
- Create: `src/Modules/Bookmarks/BookmarksModule.php`
- Create: `src/Modules/Bookmarks/Bookmark.php`
- Create: `src/Modules/Bookmarks/BookmarkService.php`
- Create: `src/Modules/Bookmarks/BookmarkController.php`
- Create: `resources/views/bookmarks/index.blade.php`
- Create: `tests/Feature/BookmarksTest.php`

**Interfaces:**
- Produces: `BookmarkService::add(int $userId, int $deviceId): Bookmark`, `remove(int $userId, int $deviceId): void`, `forUser(int $userId): Collection`.

- [ ] **Step 1: Write failing tests** for add, duplicate prevention, remove, user isolation, inaccessible device rejection, and disabled-module behavior.
- [ ] **Step 2: Run the focused tests** and verify failure.
- [ ] **Step 3: Implement bookmark migration/model/service/controller/view and device-page action hook**.
- [ ] **Step 4: Re-run tests** and verify PASS.
- [ ] **Step 5: Commit** with `feat: add per-user device bookmarks`.

### Task 5: Device Tags Module

**Files:**
- Create: `database/migrations/2026_10_04_000003_create_lus_tags_tables.php`
- Create: `src/Modules/DeviceTags/Tag.php`
- Create: `src/Modules/DeviceTags/DeviceTag.php`
- Create: `src/Modules/DeviceTags/TagService.php`
- Create: `src/Modules/DeviceTags/TagController.php`
- Create: `resources/views/tags/index.blade.php`
- Create: `tests/Feature/DeviceTagsTest.php`

**Interfaces:**
- Produces: `TagService::create(string $name): Tag`, `assign(int $deviceId, int $tagId): void`, `remove(int $deviceId, int $tagId): void`, `devicesMatching(array $tagIds): Collection` with AND semantics.

- [ ] **Step 1: Write failing tests** for create, normalized duplicate prevention, assign, duplicate assignment, remove, AND filtering, permissions, and disabled-module behavior.
- [ ] **Step 2: Run focused tests** and verify failure.
- [ ] **Step 3: Implement migrations, models, service, controller, filtering, and UI**.
- [ ] **Step 4: Re-run tests** and verify PASS.
- [ ] **Step 5: Commit** with `feat: add manual device tags`.

### Task 6: Device QR Codes Module

**Files:**
- Create: `src/Modules/QrCodes/QrCodesModule.php`
- Create: `src/Modules/QrCodes/QrTargetService.php`
- Create: `src/Modules/QrCodes/QrCodeController.php`
- Create: `resources/views/qr/label.blade.php`
- Create: `tests/Unit/Modules/QrCodes/QrTargetServiceTest.php`
- Create: `tests/Feature/QrCodesTest.php`

**Interfaces:**
- Produces: `QrTargetService::forDevice(Device $device, string $targetType, ?string $custom = null): string`.

- [ ] **Step 1: Write failing tests** for LibreNMS URL, hostname, management IP, management URL, custom target validation, invalid device, unauthorized device, HTML escaping, and disabled module.
- [ ] **Step 2: Run focused tests** and verify failure.
- [ ] **Step 3: Add a maintained QR library through Composer and implement target generation, rendering controller, SVG/PNG response handling, and printable label view**.
- [ ] **Step 4: Re-run tests** and verify PASS.
- [ ] **Step 5: Commit** with `feat: add device QR labels`.

### Task 7: Description Auditor Module

**Files:**
- Create: `src/Modules/DescriptionAudit/DescriptionAuditModule.php`
- Create: `src/Modules/DescriptionAudit/DescriptionRuleSet.php`
- Create: `src/Modules/DescriptionAudit/DescriptionAuditService.php`
- Create: `src/Modules/DescriptionAudit/DescriptionAuditController.php`
- Create: `resources/views/description-audit/index.blade.php`
- Create: `tests/Unit/Modules/DescriptionAudit/DescriptionAuditServiceTest.php`

**Interfaces:**
- Produces: `DescriptionAuditService::audit(iterable $ports, DescriptionRuleSet $rules): array` returning findings with device, port, description, and reason.

- [ ] **Step 1: Write failing tests** for blank, too-short, temp/test/unknown/unused, generic/default descriptions, same-device duplicates, cross-device non-duplicates, exclusions, and a large synthetic input set.
- [ ] **Step 2: Run focused tests** and verify failure.
- [ ] **Step 3: Implement immutable rules and audit service, then the controller/view over existing LibreNMS port data**.
- [ ] **Step 4: Add configurable rule settings through `SettingsService`**.
- [ ] **Step 5: Re-run tests** and verify PASS.
- [ ] **Step 6: Commit** with `feat: add interface description auditor`.

### Task 8: Port Flap Counter Module

**Files:**
- Create: `src/Modules/PortFlaps/PortFlapsModule.php`
- Create: `src/Modules/PortFlaps/PortStateTransition.php`
- Create: `src/Modules/PortFlaps/PortFlapService.php`
- Create: `src/Modules/PortFlaps/PortFlapHistoryRepository.php`
- Create: `src/Modules/PortFlaps/PortFlapController.php`
- Create: `resources/views/port-flaps/index.blade.php`
- Create: `resources/views/port-flaps/show.blade.php`
- Create: `tests/Unit/Modules/PortFlaps/PortFlapServiceTest.php`

**Interfaces:**
- Produces: `PortFlapService::count(iterable $states): int`, `severity(int $count, FlapThresholds $thresholds): string`.
- Repository isolates the exact LibreNMS event/state-history storage so schema changes do not leak into module logic.

- [ ] **Step 1: Write failing tests** for UP→DOWN→UP, UP→UP, DOWN→DOWN, unknown-state handling, 24h/7d/30d windows, thresholds, administratively-disabled exclusion, explicit exclusions, and large history input.
- [ ] **Step 2: Run focused tests** and verify failure.
- [ ] **Step 3: Implement transition counting and repository adapter against current LibreNMS history/event sources**.
- [ ] **Step 4: Implement list/detail views and configurable thresholds/exclusions**.
- [ ] **Step 5: Re-run tests** and verify PASS.
- [ ] **Step 6: Commit** with `feat: add port flap analysis`.

### Task 9: Device Notes History Module

**Files:**
- Create: `database/migrations/2026_10_04_000004_create_lus_device_note_history_table.php`
- Create: `src/Modules/NotesHistory/DeviceNoteHistory.php`
- Create: `src/Modules/NotesHistory/DeviceNoteHistoryService.php`
- Create: `src/Modules/NotesHistory/NotesHistoryModule.php`
- Create: `src/Modules/NotesHistory/DeviceNoteHistoryController.php`
- Create: `resources/views/notes-history/device.blade.php`
- Create: `tests/Feature/DeviceNotesHistoryTest.php`

**Interfaces:**
- Produces: `DeviceNoteHistoryService::record(int $deviceId, int $userId, ?string $before, ?string $after, string $source): ?DeviceNoteHistory` where unchanged values return null.

- [ ] **Step 1: Write failing tests** for first note, changed note, unchanged note, ordering, diff inputs, append-only behavior, unauthorized access, and disabled module.
- [ ] **Step 2: Run focused tests** and verify failure.
- [ ] **Step 3: Determine the supported LibreNMS note-update hook/event path and implement history interception without core edits**.
- [ ] **Step 4: Implement history/diff view**.
- [ ] **Step 5: Re-run tests** and verify PASS.
- [ ] **Step 6: Commit** with `feat: add device note history`.

### Task 10: Port Notes History Module

**Files:**
- Create: `database/migrations/2026_10_04_000005_create_lus_port_note_history_table.php`
- Create: `src/Modules/PortNotes/PortNoteHistory.php`
- Create: `src/Modules/PortNotes/PortNoteHistoryService.php`
- Create: `src/Modules/PortNotes/PortNotesModule.php`
- Create: `src/Modules/PortNotes/PortNoteController.php`
- Create: `resources/views/port-notes/show.blade.php`
- Create: `tests/Feature/PortNotesHistoryTest.php`

**Interfaces:**
- Produces: `PortNoteHistoryService::record(int $portId, int $deviceId, int $userId, ?string $before, ?string $after): ?PortNoteHistory`.

- [ ] **Step 1: Write failing tests** for changed/unchanged notes, port/device association, ordering, unauthorized port access, and disabled module.
- [ ] **Step 2: Run focused tests** and verify failure.
- [ ] **Step 3: Integrate with the supported LibreNMS native port-note update path without replacing the native note value**.
- [ ] **Step 4: Implement port note/history UI**.
- [ ] **Step 5: Re-run tests** and verify PASS.
- [ ] **Step 6: Commit** with `feat: add port note history`.

### Task 11: Ping and SNMP Diagnostics Module

**Files:**
- Create: `src/Modules/Diagnostics/DiagnosticsModule.php`
- Create: `src/Modules/Diagnostics/PingService.php`
- Create: `src/Modules/Diagnostics/SnmpDiagnosticService.php`
- Create: `src/Modules/Diagnostics/DiagnosticResult.php`
- Create: `src/Modules/Diagnostics/DiagnosticsController.php`
- Create: `resources/views/diagnostics/result.blade.php`
- Create: `tests/Unit/Modules/Diagnostics/PingServiceTest.php`
- Create: `tests/Unit/Modules/Diagnostics/SnmpDiagnosticServiceTest.php`
- Create: `tests/Feature/DiagnosticsSecurityTest.php`

**Interfaces:**
- Produces: `PingService::test(Device $device): DiagnosticResult`.
- Produces: `SnmpDiagnosticService::test(Device $device): DiagnosticResult` querying `sysName`, `sysDescr`, `sysObjectID`, and `sysUpTime` only.
- `DiagnosticResult` exposes status, safe summary, metrics, and elapsed time but no credentials/raw command line.

- [ ] **Step 1: Write failing ping tests** for success, packet loss, total timeout, malformed output, and process failure.
- [ ] **Step 2: Write failing SNMP tests** for success, timeout, invalid response, unsupported configuration, and process/library failure.
- [ ] **Step 3: Add sentinel-secret security tests** asserting community/auth/priv secrets never appear in HTTP responses, exceptions, serialized results, or test log output.
- [ ] **Step 4: Implement bounded server-side ping execution and SNMP access through LibreNMS-native helpers/config where possible**.
- [ ] **Step 5: Implement controller/UI and permission checks**.
- [ ] **Step 6: Run focused tests** and verify PASS.
- [ ] **Step 7: Commit** with `feat: add safe ping and SNMP diagnostics`.

### Task 12: Unified Dashboard and Navigation

**Files:**
- Create: `src/Http/Controllers/DashboardController.php`
- Create: `resources/views/dashboard.blade.php`
- Create: `resources/views/settings.blade.php`
- Modify: `src/UtilitySuitePlugin.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/DashboardTest.php`
- Create: `tests/Feature/SettingsTest.php`

**Interfaces:**
- Consumes: service summaries from Tasks 4-11.
- Produces: plugin landing page and admin settings page.

- [ ] **Step 1: Write failing tests** proving enabled-module cards appear, disabled-module cards disappear, settings require admin, and each toggle persists independently.
- [ ] **Step 2: Run focused tests** and verify failure.
- [ ] **Step 3: Implement dashboard aggregation, navigation hooks, and settings UI**.
- [ ] **Step 4: Re-run tests** and verify PASS.
- [ ] **Step 5: Commit** with `feat: add utility suite dashboard and settings`.

### Task 13: Installation, Upgrade, Logging, and Documentation

**Files:**
- Create: `README.md`
- Create: `CHANGELOG.md`
- Create: `docs/INSTALL.md`
- Create: `docs/UNINSTALL.md`
- Create: `docs/COMPATIBILITY.md`
- Create: `tests/Feature/InstallSmokeTest.php`
- Modify: `composer.json`

**Interfaces:**
- Produces: documented installation/uninstall/upgrade process and compatibility statement.

- [ ] **Step 1: Write/install smoke coverage** proving migrations run idempotently, plugin boot succeeds with all modules enabled, and boot succeeds with all modules disabled.
- [ ] **Step 2: Add logging tests** verifying module failures and diagnostic timeouts produce useful redacted logs.
- [ ] **Step 3: Write installation, update, uninstall, module-configuration, permissions, and troubleshooting documentation**.
- [ ] **Step 4: Document that uninstall preserves plugin history unless the administrator explicitly requests destructive cleanup**.
- [ ] **Step 5: Run the entire test suite** with `vendor/bin/phpunit` and require zero failures.
- [ ] **Step 6: Run Composer validation** with `composer validate --strict`.
- [ ] **Step 7: Commit** with `docs: complete v0.1 installation and operations guide`.

### Task 14: Final Integration and v0.1 Verification

**Files:**
- Modify as required by defects found during verification only.
- Update: `CHANGELOG.md`

**Interfaces:**
- Consumes all previous tasks.
- Produces a release-candidate v0.1 tree.

- [ ] **Step 1: Run complete automated tests** and capture results.
- [ ] **Step 2: Verify no LibreNMS core file modifications are required by comparing installation instructions and package contents**.
- [ ] **Step 3: Verify all eight feature toggles independently suppress their UI and action routes**.
- [ ] **Step 4: Run security-focused tests for direct-object access and credential redaction**.
- [ ] **Step 5: Run static analysis/linting supported by the project toolchain and fix defects**.
- [ ] **Step 6: Update changelog with v0.1 implemented modules, known limitations, and compatibility notes**.
- [ ] **Step 7: Commit** with `chore: prepare LibreNMS Utility Suite v0.1`.

## Plan Self-Review

Spec coverage is complete across the eight modules, core modular settings, authorization, dashboard, database namespace, diagnostics safety, logging, testing, installation, uninstall behavior, performance constraints, and future extensibility. Interfaces use consistent service names across dependent tasks. The implementation order validates the plugin core before modules with tighter LibreNMS integration, and no task requires modifying LibreNMS core.
