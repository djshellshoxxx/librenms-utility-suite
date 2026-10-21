# LibreNMS Utility Suite

An upgrade-safe, modular plugin that adds small operational and documentation features to LibreNMS without modifying LibreNMS core. Each feature is an independent module that can be enabled or disabled from the plugin settings.

**Status: specification only.** This repository holds the v0.1 design specification and an implementation plan. There is no plugin code yet, so nothing can be installed or run from here.

## Planned modules (v0.1)

1. Device Notes History
2. Device Bookmarks
3. Port Notes
4. Device QR Codes
5. Ping/SNMP Diagnostics
6. Description Auditor
7. Port Flap Counter
8. Device Tags

The plan also includes centralised settings, permission enforcement, a unified dashboard, namespaced database migrations and internal service boundaries for a later API.

## Documents

- [Design specification](docs/superpowers/specs/2026-10-04-librenms-utility-suite-design.md): purpose, core requirements, architecture, module contract, per-module behaviour, database, permissions, API and performance.
- [Implementation plan](docs/superpowers/plans/2026-10-04-librenms-utility-suite-v0.1.md).

## Contributing

Change the specification before changing the design. Keep the core requirements (no LibreNMS core changes, respect
device permissions, no SNMP credentials in the browser, CSRF protection on writes, clean uninstall) intact.

## Licence

No licence file is present, so no licence is granted by this README. The owner should add one.

## Wiki

Wiki source pages are in [docs/wiki/](docs/wiki/Home.md).
