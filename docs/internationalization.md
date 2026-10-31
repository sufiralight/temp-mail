# Internationalization architecture

Status: Phase 0 proposal; English is the initial interface. The following 30-language registry is **not approved/final**. Obtain explicit user approval before treating codes/variants as final or releasing additional languages.

## Proposed registry

| Code | Language / native display name candidate | Direction |
| --- | --- | --- |
| en | English | LTR |
| ar | Arabic / العربية | RTL |
| bn | Bengali / বাংলা | LTR |
| zh | Chinese / 中文 | LTR |
| cs | Czech / Čeština | LTR |
| nl | Dutch / Nederlands | LTR |
| fr | French / Français | LTR |
| de | German / Deutsch | LTR |
| el | Greek / Ελληνικά | LTR |
| he | Hebrew / עברית | RTL |
| hi | Hindi / हिन्दी | LTR |
| id | Indonesian / Bahasa Indonesia | LTR |
| it | Italian / Italiano | LTR |
| ja | Japanese / 日本語 | LTR |
| ko | Korean / 한국어 | LTR |
| ms | Malay / Bahasa Melayu | LTR |
| fa | Persian / فارسی | RTL |
| pl | Polish / Polski | LTR |
| pt | Portuguese / Português | LTR |
| ro | Romanian / Română | LTR |
| ru | Russian / Русский | LTR |
| es | Spanish / Español | LTR |
| sw | Swahili / Kiswahili | LTR |
| sv | Swedish / Svenska | LTR |
| ta | Tamil / தமிழ் | LTR |
| th | Thai / ไทย | LTR |
| tr | Turkish / Türkçe | LTR |
| uk | Ukrainian / Українська | LTR |
| ur | Urdu / اردو | RTL |
| vi | Vietnamese / Tiếng Việt | LTR |

D-08 must settle zh script/region and pt regional policy; native names need native review. One future allowlisted registry owns code, names, dir, release-enabled and completeness state. No arbitrary code-to-file/view/DB interpolation.

## Fixed UI and selection

Semantic PHP translation groups such as common, install, auth, account, admin, validation, cms and mailbox; translate complete phrases with framework placeholders/plurals, not concatenation. English is source/fallback. Escape output; translated strings cannot execute markup. Fixed bundled text, administrator overrides and CMS translations are separate stores with provenance.

Proposed future selection precedence: valid explicit localized URL, authenticated saved locale, allowlisted cookie, then supported Accept-Language for first entry, then English. Do not auto-switch a stable explicit URL or create arbitrary redirects. User choice overrides browser detection. English-only Phase 1 remains unprefixed with no dead language switcher.

Proposed public multilingual routes: /{locale}/... with a separate per-language content slug, while install/auth/application routes remain stable and persist locale separately. English canonical prefix vs unprefixed URL, redirect rules and variant policy require D-08 approval before routing implementation. Do not silently ship this proposal.

## Content, SEO and completeness

UI missing keys fall back to English visibly in translation tooling, never count as complete. States: missing, draft, reviewed, release-ready; fallback and admin override have distinct provenance. Release languages only after critical flows are reviewed.

CMS has logical entities plus localized content rows; title/body/slug/SEO/publication per language. Unique language-scoped slugs and route resolution include locale and content type. Never reuse another language's slug/content as if translated. Missing public translation returns a deliberate unavailable/404 response with a language chooser where appropriate, not a fake equivalent.

Canonical, title/description, hreflang (reciprocal), sitemap and structured content use only real published localized URLs; no fallback duplicates. Slug edits need deliberate redirect history and loop prevention.

## Direction and formatting

Set html lang and dir from current registry on every render; RTL→LTR switching resets both and component/editor direction. Use logical CSS and direction-aware icon placement, not page-wide transforms. Mixed text/email/URLs/IDs use bdi or explicit LTR isolation; do not mirror brands/non-directional symbols. Rich editor direction follows its content locale independently of shell.

Inter is primary with script-aware fallback. Test Arabic/Hebrew, CJK/Indic, long labels and pseudo-locales. UTC storage; display with explicit locale and user timezone. Preserve placeholders and plural forms. No translations, locale switcher or registry code is implemented in Phase 0.
