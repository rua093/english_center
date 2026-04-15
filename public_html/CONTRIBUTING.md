# CONTRIBUTING

## Mandatory Read

Before changing code, read `docs/ARCHITECTURE.md`.

By contributing to this project, you agree to follow all architecture rules in that file.

## Pull Request Checklist

- [ ] New page links use feature-first slugs only.
- [ ] New API logic is implemented in `api/canonical/`.
- [ ] Legacy API files are wrappers without duplicated business logic.
- [ ] Upload/file changes are in `core/file_storage.php`.
- [ ] New config keys are placed in the correct `config/*.php` domain file.
- [ ] Runtime files are written under `storage/` only.
- [ ] Backward compatibility is preserved or fallback is documented.
