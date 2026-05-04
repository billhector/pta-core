# pta-core

Core WordPress plugin for [PropertyTaxAppealGuides.com](https://propertytaxappealguides.com).

## Subsystems

- **Commerce** — Stripe Checkout, R2 pre-signed URLs, magic-link redownload, order store, refund handling
- **Mail** — Mailjet SMTP + REST API, transactional templates, contact list sync
- **Blocks** — Dynamic blocks (county-contacts, municipality-dropdown, conditional-lookup, buy-button)
- **Schema** — Product JSON-LD for county-guide CPT
- **Hooks** — auto-assign state taxonomy, MIME type allowlist, etc.

## Requirements

- WordPress 6.5+
- PHP 8.2+ (8.4 in dev/prod)
- Companion theme: [pta-blocks](https://github.com/billhector/pta-blocks)

## Local development

Built and tested on Local-by-Flywheel. Production: Dreamhost VPS.

No composer. Manual PSR-4 autoload (`autoload.php`) maps `Pta\Core\` → `src/`. REST API calls use `wp_remote_post()`; no SDKs.

## Deploy

GitHub Actions → Dreamhost VPS via SSH/rsync. See `.github/workflows/deploy.yml` (added in Plan E).
