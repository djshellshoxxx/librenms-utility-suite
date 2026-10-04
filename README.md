# LibreNMS Utility Suite

A modular LibreNMS package plugin that adds device note history, per-user bookmarks, port note history, device QR labels, on-demand ping/SNMP diagnostics, interface description auditing, port-flap analysis, and manual device tags.

## Status

Early v0.1 implementation. The core domain services, schema, settings, hooks, and dashboard wiring are present. Feature-specific persistence/controllers are being kept isolated so each module can evolve without patching LibreNMS core.

## Modules

All modules default to enabled and can be toggled independently:

- `notes_history`
- `bookmarks`
- `port_notes`
- `qr_codes`
- `diagnostics`
- `description_audit`
- `port_flaps`
- `device_tags`

## LibreNMS integration

This project targets the current distributable plugin package model using `librenms/plugin-interfaces`. It does not require changes to LibreNMS core files.

## Development

With Composer available:

```bash
composer install
composer test
composer test:syntax
```

A dependency-free domain smoke suite is also included for constrained environments:

```bash
php tests/run.php
```

## Security

Diagnostics are designed to execute server-side. SNMP community strings and SNMPv3 authentication/privacy secrets must never be rendered into browser responses or logs. Diagnostic results include a redaction path for failure text before presentation.

## Design and implementation plan

See:

- `docs/superpowers/specs/2026-10-04-librenms-utility-suite-design.md`
- `docs/superpowers/plans/2026-10-04-librenms-utility-suite-v0.1.md`
