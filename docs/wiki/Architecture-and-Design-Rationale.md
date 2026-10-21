# Architecture and Design Rationale

Source: the v0.1 design specification, sections 2 and 3.

- **Plugin, not a fork.** The suite installs without modifying LibreNMS core and uses supported Laravel plugin mechanisms, so it tolerates LibreNMS upgrades as far as the public interface allows.
- **Modules that can be switched off.** Each feature is independent. Disabled modules should not load hooks, scheduled jobs, routes or UI.
- **Access control first.** The suite respects LibreNMS authentication and device permissions, validates device and port IDs against the current user's access, and never sends SNMP credentials to the browser.
- **Writes are protected.** Modifying operations need CSRF protection.
- **Clean uninstall keeps history.** Operational history is not deleted on uninstall unless the user asks for it.
- **Namespaced tables.** All plugin tables are clearly namespaced.
