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
**Governing DOP:** none written yet — run `/write-dop` (or `/start-discovery` for the fuller DOP+IRDs+sprint-plan treatment) before further feature work
**Governing IRDs:** none yet
**Predecessor state:** this codebase and its CI/CD/Ansible/Terraform tooling in the sibling `CPPProjects/` folder were built before the hub agent workflow existed — see hub's `memory/project_mu_online_web_state.md` for the full audit (unfinished deploy history, license considerations, etc.)

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
| Repo cleanup (untrack runtime data, remove dead files/obsolete kfc agent scaffolding, consolidate stale docs) | ✅ | this repo, commits `571610d` + follow-up | 2026-07-28 |
| Hub context import (this file) | ✅ | `CLAUDE.md` | 2026-07-28 |
| DOP/IRD for this project | ⬜ | - | - |

---

## 🛠 SESSION COMMANDS

- `/start-session`: Sync with `docs/WIP.md` and local daily logs.
- `/save-skill`: Format and store a local technical pattern for future Hub promotion.
- `/report`: End the day. Write `logs/daily/YYYY-MM-DD.md` and update Notion/Linear.
- `!hub-sync`: Check the Main Hub for new global skills (#new tags).
- `!scope-pivot`: Recalculate IRDs/Backlog based on a change in requirements.
