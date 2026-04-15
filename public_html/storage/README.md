# Storage Layout

This directory is runtime-only and denied by web server rules.

- `logs/`: rotated API and app logs by day.
- `cache/`: generated cache files.
- `tmp/`: temporary files for short-lived operations.
- `exports/`: generated reports/exports to be served through controlled endpoints.

Do not commit runtime data from this folder.
