# Mu Online Web — Claude Context

## Inheritance

This session inherits from the hub agent. Before answering anything, read:

1. **Identity + Rules:** `/Users/mac/Desktop/chautv-proops2026/CLAUDE.md`
2. **Acquired Skills:** `/Users/mac/Desktop/chautv-proops2026/memory/skills-ledger.md`
3. **Domain Memory:** `/Users/mac/Desktop/chautv-proops2026/memory/<relevant-topic>.md` (esp. `cicd.md`, `iac.md`, `bash-scripting.md`)
4. **This file** — project-specific overrides below

Rules in this file override hub rules only for this project. All hub rules apply unless explicitly overridden here.

---

## Project Identity

**Project:** Mu Online Web — the account/shop/wcoin website for a private Mu Online game server business (danangmu.com)
**Role:** Real revenue-generating product, NOT a training mock — see hub's `memory/project_goals.md` (Goal 2). Treat production risk (real players, real payments, real DDoS exposure) accordingly.
**Governing DOP:** [DOP-28 — Mu Online Web](https://app.notion.com/p/DOP-Mu-Online-Web-3abdde5fafa981db88aac8e98955bc75)
**Governing IRDs:** [IRD-27 — Mu Online Web: Deployment and Secrets Standards](https://app.notion.com/p/IRD-Mu-Online-Web-Deployment-and-Secrets-Standards-3abdde5fafa9819ca588fda3f984fe97)
**Predecessor state:** this codebase and its CI/CD/Ansible/Terraform tooling in the sibling `CPPProjects/` folder were built before the hub agent workflow existed — see hub's `memory/project_mu_online_web_state.md` for the full audit (unfinished deploy history, license considerations, etc.)
**Sibling component docs (2026-08-01):** the full danangmu.com launch spans 4 components, each with its own DOP+IRD in Notion — this repo (DOP-28/IRD-27), [mu-online-server](https://app.notion.com/p/3afdde5fafa981968677c9a16b38d3cf) ([IRD](https://app.notion.com/p/3afdde5fafa98147bc98f3242d087403)), [mu-database](https://app.notion.com/p/3afdde5fafa981348f8bf498cdf612db) ([IRD](https://app.notion.com/p/3afdde5fafa9815ca9ebf1b31abfb836)), and [Infra Scaling and Hosting](https://app.notion.com/p/3afdde5fafa981e29d23f9efa0934a37) ([IRD](https://app.notion.com/p/3afdde5fafa98144abcaf81709ef285b)) — all currently Draft. Those 3 sibling repos (`mu-online-server`, `ansible-mu`, `terraform`) have no `CLAUDE.md` of their own yet.

---

## Architecture

```
mu-web (PHP 7.4 + Apache, DmN CMS / MuCMS Pro — proprietary, ionCube-encoded)
  ├── depends on: MSSQL (shared schema with mu-online-server / mu-database — NOT decoupled)
  ├── payment libs already in composer.json: stripe/stripe-php, omnipay/coinbase
  └── deploy path: GitHub Actions (.github/workflows/ci-cd-mu-web.yml) → Ansible (../ansible-mu) → DigitalOcean droplet → danangmu.com
```

- Dev: `docker-compose.dev.yml` — web + local MSSQL, live volume mount on `Web/`, port 8080
- Prod reference: `docker-compose.prod.yml` — port 80, health check via `Web/health.php`
- `Web/Dockerfile` builds from `Web/` context only — anything outside `Web/` never ships in the image
- Git: `develop` (active work) → `main` (triggers CI/CD deploy)

---

## Locked Design Decisions

- **DmN CMS is a licensed, proprietary, ionCube-encoded product** (dmncms.net, license: proprietary in `composer.json`) — do not assume source-level changes are possible in ionCube-encoded files; check license validity (domain/IP-locked?) before any redeploy to new infra
- **DB schema is shared with the game server** (`mu-database`) — account/wcoin/item changes on the web side must stay compatible with what `mu-online-server` expects; this is not an independent data model
- **Payment integration already has libraries wired in** (Stripe, Coinbase via composer) — don't rebuild payment plumbing from scratch; audit what's actually configured/used first
- **Runtime data is never committed** — `Web/application/data/sessions/`, `Web/application/data/cache/`, `Web/application/logs/` are gitignored (fixed 2026-07-28, were previously tracked by mistake)

---

## Session Startup Routine

1. Read hub CLAUDE.md (identity, rules, memory sync protocol)
2. Read this file (project context)
3. Check for an open DOP/IRD in Notion for this project — if none, that's the first task
4. Check Linear for open issues in this project
5. Print: "Session ready. Project: Mu Online Web. Governing DOP: [none yet / URL]. Next task: [task]"

---

## Skills Already Inherited

From `skills-ledger.md`, directly relevant here:

- [ ] [GHA Test Job Against a DB-Backed App Needs an Explicit DB Service Container](../../chautv-proops2026/memory/skills/ci-cd.md) — CI/CD section
- [ ] [.gitignore Paths Can't Be Matched by GitHub Path Filters](../../chautv-proops2026/memory/skills/ci-cd.md) — CI/CD section
- [ ] [.gitignore negation requires `dir/*` not `dir/`](../../chautv-proops2026/memory/skills-ledger.md) — check before adding new ignore rules
- [ ] Docker multi-stage build patterns — Docker section (current `Web/Dockerfile` is single-stage; revisit for prod image size/security)
- [ ] Ansible + secrets handling patterns — Ansible/Secrets Management sections (relevant to `../ansible-mu`)

---

## Project-Specific Rules

- Never assume the last known prod deploy succeeded — `deploy-production-errors.txt` shows the most recently recorded Ansible run ended mid-task ("operation was canceled"); verify actual droplet state before treating anything as "live"
- Any change touching `application/data/`, `application/logs/`, or `application/config/` — check `git status` first; these have a history of accidental commits
- The domain `danangmu.com`'s DNS currently points at Cloudflare for the Infisical secrets vault (`secrets.danangmu.com`) — relaunching this site means adding a new subdomain/record, not reclaiming the root without coordinating with that existing setup (see hub's `memory/project_secrets_management_infisical.md`)

---

## What Has Been Built

| Task | Status | Artifact | Date |
|------|--------|----------|------|
| Repo cleanup (untrack runtime data, remove dead files/obsolete kfc agent scaffolding, consolidate stale docs) | ✅ | this repo, commits `571610d` + `e32ff7f` | 2026-07-28 |
| Hub context import (this file) | ✅ | `CLAUDE.md` | 2026-07-28 |
| DOP/IRD for this project | ✅ | DOP-28 + IRD-27 in Notion (linked above) | 2026-07-28 |
| Full hub command/skill set copied (`/start-session`, `/report`, `/save-skill`, `!hub-sync`, `!scope-pivot`, `/start-discovery`, `/create-milestone`, `/milestone-report`, `/cost-check` + all 11 hub skills) | ✅ | `.claude/commands/`, `.claude/skills/`, commit `c01ccd9` | 2026-07-28 |
| FR-1..4 / ACs from DOP-28 | ⬜ | - | - |
| Infra scaling plan (DB → anti-DDoS/proxy → game farm → website order, cost-tier sizing) agreed | ✅ | hub memory `infra_scaling_plan.md` | 2026-08-01 |
| Hardcoded prod MSSQL `sa` password removed from `ansible-mu` (was committed in plaintext, vault key itself was also tracked) | ✅ | `ansible-mu` commit `e3e47f1` | 2026-08-01 |
| Verified actual prod state — all 3 droplets confirmed down (web app not running, MSSQL not listening, game server fully unreachable) | ✅ | this session | 2026-08-01 |
| Update `ANSIBLE_VAULT_PASSWORD` GitHub secret on this repo to match rotated vault password | ✅ | GitHub secret updated from local `ansible-mu/vault/.vault_pass` (verified against `vault/secrets.yml` before pushing) | 2026-08-09 |
| kfc agent scaffolding — actually delete untracked files (`571610d`/`e32ff7f` only untracked them, files still on disk) | ⬜ | `.claude/agents/kfc/`, `.claude/settings/kfc-settings.json`, `.claude/system-prompts/` | - |
| DOP/IRD scaffold extended beyond the website — 3 more component pairs created (game server, shared DB, infra/hosting), closing the gap where only DOP-28/IRD-27 existed | ✅ | Notion: DOP+IRD for mu-online-server, mu-database, Infra Scaling and Hosting (all Draft) | 2026-08-01 |
| Hardcoded MSSQL `sa` password fallback default in `ansible-mu/roles/mssql/defaults/main.yml` (survived the earlier `e3e47f1` fix as a silent fallback) removed, fail-loud assert added | ✅ | `ansible-mu` commit `4d374e6` | 2026-08-01 |
| `main`/`develop` reconciled — discovered they had unrelated git histories (`develop` was rewritten at some point, `main` frozen since March); backed up old `main` as a tag, force-synced `main` to `develop` | ✅ | tag `main-backup-2026-03-29`, `main`/`develop` both at `5fe5f55` | 2026-08-09 |
| Live Resend API key found already-published in git history on both branches (predates the CSRF refactor commit) — rotated at Resend, blanked from `email_config.json`, added `getenv('SMTP_PASSWORD')` override at all 4 `sendmail()` call sites | ✅ | commit `5fe5f55` | 2026-08-09 |
| `GHCR_TOKEN` GitHub secret was an expired classic PAT, blocking the `push` job — regenerated and updated | ✅ | GitHub secret updated | 2026-08-09 |
| First real CI/CD run since March: `build`/`push` now green; `deploy-production` fails at Ansible SSH — `mu-deploy` key rejected on the web droplet (`159.65.12.125`), matching a previously-flagged-but-unresolved "SSH host-key fingerprints changed" note | ⬜ | run `31279359200`, blocked | 2026-08-09 |
| Infra Scaling IRD revised — adopt/extend the `terraform/infrastructure/` split-state variant (web/db/game each with independent state) instead of archiving it, after the shared-state model forced manual `-target` scoping during today's droplet recreation attempt | ✅ | Notion IRD-31 updated; hub memory `infra_scaling_plan.md` | 2026-08-09 |
| New `mu-deploy` SSH keypair generated and registered in DO account's SSH Keys under the Terraform-expected name (`mu-deploy-key`) — replaces an orphaned/mismatched key | ✅ | scratchpad `mu-deploy-new(.pub)`, DO SSH key ID `58355243` | 2026-08-10 |
| `doctl` installed and authenticated — but the authenticated DO API token sees **zero droplets/volumes** and only a `fra1` VPC, meaning it's scoped to a different account/team than the one running production (`sgp1`) — blocks the planned web-droplet recreation until resolved | ⬜ | — needs correct team/project token | 2026-08-10 |
| `mu_db_user`/`mu_db_password` added to local `terraform.tfvars` (gitignored; reused the existing `ansible-mu` vault `sa` credential) | ✅ | local file only, not committed | 2026-08-10 |

---

## 🛠 SESSION COMMANDS

- `/start-session`: Sync with `docs/WIP.md` and local daily logs.
- `/save-skill`: Format and store a local technical pattern for future Hub promotion.
- `/report`: End the day. Write `logs/daily/YYYY-MM-DD.md` and update Notion/Linear.
- `!hub-sync`: Check the Main Hub for new global skills (#new tags).
- `!scope-pivot`: Recalculate IRDs/Backlog based on a change in requirements.
