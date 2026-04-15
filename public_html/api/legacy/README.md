# API Legacy Layer

This directory documents the legacy API layer behavior.

- Canonical API modules live in ../canonical and expose action functions like `api_<resource>_<method>_action`.
- `api/index.php` resolves canonical actions first.
- Existing resource/method endpoint files under ../<resource>/<method>.php are treated as legacy wrappers for backward compatibility.
- Legacy do-* action mapping remains supported through `api/index.php`.

When adding new API behavior:
1. Implement business flow in ../canonical/<resource>.php.
2. Keep legacy wrappers thin and side-effect free.
3. Do not duplicate business logic in legacy endpoint files.
