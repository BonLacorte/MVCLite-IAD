# MVCLite IAD Agent Guide

## Project Context
This is the MVCLite PHP MVC application for the Internal Audit Department. Keep work scoped to the requested feature or bug, follow existing controller/model/view patterns, and preserve current route guards, role checks, answer/review status semantics, and private storage behavior unless the user explicitly asks for a change.

## Local Run Commands
- Recommended Docker start: `docker compose up -d app`
- App URL: `http://localhost:8001`
- App container: `mvclite_app`
- DB container: `mvclite_db`
- Host DB port: `3307`
- Manual PHP server: `php -S 127.0.0.1:8021 -t public dev-router.php`
- Use `dev-router.php` for the PHP server so static assets in `public/` are served correctly.

## Coding Rules
- Prefer the project's existing MVCLite conventions over new framework abstractions.
- Keep controllers thin where practical and put reusable query logic in models.
- Escape all rendered user/database values in PHP views.
- Validate request data server-side, even when client-side checks already exist.
- Keep uploaded files under private `mnt` storage and expose them only through authorized controller routes.
- Store relative file paths in the database, not absolute paths.
- Do not change database schema, route behavior, or destructive data handling unless the user explicitly requests it.
- Avoid unrelated refactors, formatting churn, and broad visual redesigns outside the requested pages.

## Frontend Rules
- Match the green/yellow PCA/IAD palette already defined in `public/assets/css/style.css`.
- Use reusable button, input, card, table, and modal classes when touching shared UI patterns.
- Keep search bars, button groups, and icon buttons responsive and unclipped.
- Test desktop and mobile layouts when changing public pages, admin tables, profile pages, questionnaire pages, or event galleries.

## Verification
- Run `php -l` on every changed PHP file.
- For Docker checks, run `docker compose ps` and verify `http://localhost:8001`.
- For UI changes, use browser testing where possible and check for console errors.
- For database or file-upload changes, verify DB state and private storage paths.

## Recommended Project Skills
See `AGENTS_SKILLS.md` for the installed project skill map. Prefer:
- `php-pro` for PHP controller/model/service implementation.
- `php-best-practices` for maintainability and review.
- `frontend-design` for polished page and component UI work.
- `web-design-guidelines` for responsive UX and accessibility checks.
- `sql-code-review` for query and migration review.
- `laravel-owasp-security` for PHP web security review principles.
- `documentation-writer` for durable guides and implementation notes.
- `caveman` and related Caveman skills for shorter agent communication and token-conscious workflows when explicitly useful.

## Git Safety
The working tree may contain user changes. Do not revert or delete unrelated changes. Do not run destructive Git commands unless the user explicitly asks for them.
