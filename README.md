# pta-core

Core WordPress plugin for [PropertyTaxAppealGuides.com](https://propertytaxappealguides.com).

## Subsystems

- **Commerce** — Stripe Checkout, R2 SigV4 pre-signed URLs (10-min TTL), magic-link redownload, custom `wp_pta_orders` table, refund handling
- **Mail** — Mailjet SMTP override of `wp_mail()`, Mailjet REST `Client` + `Send` (v3.1) + `Contacts` (managecontactslists), branded transactional templates (order confirmation + refund)
- **Schema** — Product JSON-LD for `county-guide` and `download` post types
- **Blocks** — Six dynamic blocks: `county-contacts`, `municipality-dropdown`, `conditional-municipality-lookup`, `county-purchase` (EDD; removed Plan E), `related-guide-posts`, `buy-button` (Stripe Checkout)
- **Hooks** — `upload_mimes` (woff/woff2/ico), 4-week remember-me cookie, login form auto-check, `save_post` auto-assign state taxonomy
- **Analytics** — GA4 server-side Measurement Protocol on `pta_core_order_paid` (no-op when GA4 constants unset)

## Requirements

- WordPress 6.5+
- PHP 8.2+ (8.4 in dev/prod)
- Companion theme: [pta-blocks](https://github.com/billhector/pta-blocks)

## Local development

Built and tested on Local-by-Flywheel. Production: Dreamhost VPS.

No composer. Manual PSR-4 autoload (`autoload.php`) maps `Pta\Core\` → `src/`. REST API calls use `wp_remote_post()`; no SDKs.

## Deploy

GitHub Actions → Dreamhost VPS via SSH/rsync. See `.github/workflows/deploy.yml` (added in Plan E).
