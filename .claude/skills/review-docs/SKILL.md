---
name: review-docs
description: |
  Runs a horizontal review across all project DOPs and IRDs to find structural gaps,
  repeated rules that need centralizing, and undefined decisions that will block implementation.
  Run this after all IRDs for a sprint are written, before any code is written.
  Triggers on "review docs", "review IRDs", "check for gaps", "/review-docs".
---

# /review-docs — Cross-Document Review

Run this after writing all IRDs for a sprint, before implementation starts.

This skill does what a horizontal review does: it looks across the whole document set —
not just "is each IRD complete?" but "is the document system well-structured?"

---

## When to Run

- After writing all IRDs for a sprint
- Before the first implementation task (T-01, F-01, etc.) begins
- When a new service is added to the project
- When a tutor or developer asks "what's missing?"

---

## Step 1 — Load the Full Document Set

Read all project docs in this order:
1. `docs/DOP-001.md` — the product DOP
2. `docs/IRD-000.md` — shared standards (if it exists)
3. `docs/IRD-001.md`, `IRD-002.md`, `IRD-003.md`, ... — all service IRDs
4. `docs/sprint-01.md` — the current sprint plan

If any of these are missing, flag them before proceeding.

---

## Step 2 — Horizontal Review (4 checks)

### Check A — Repetition scan
Read across all IRDs. For every rule that appears in more than one IRD:
- List the rule and which IRDs contain it
- Mark it as: → Move to IRD-000

### Check B — Gap checklist
For each item below, check: is this decided and written somewhere?

| # | Decision | Where it should live |
|---|----------|---------------------|
| 1 | Error response format (`{ "error": "..." }` or other) | IRD-000 |
| 2 | UUID version (v4 / v7) and who generates (DB or app) | IRD-000 |
| 3 | Date/time format in API responses (ISO 8601 / Unix / other) | IRD-000 |
| 4 | Pagination strategy (limit+offset / cursor / none) | IRD-000 or per-service IRD |
| 5 | Soft delete vs hard delete policy | IRD-000 or per-service IRD |
| 6 | Request correlation / trace ID across services | IRD-000 |
| 7 | Field length limits (title, description, comment content) | Per-service IRD |
| 8 | Folder structure (src/routes/, src/middleware/, src/db/) | IRD-000 |
| 9 | File naming convention (kebab-case / camelCase) | IRD-000 |
| 10 | DB naming convention (snake_case tables and columns) | IRD-000 |
| 11 | Test file naming and structure | IRD-000 |
| 12 | Dockerfile pattern (multi-stage, USER node, npm ci) | IRD-000 |
| 13 | Health check response shape | IRD-000 |
| 14 | Logging format (what fields, what level, which library) | IRD-000 |
| 15 | Local dev setup (how to run the full stack from scratch) | IRD-000 or README |
| 16 | Password policy (min length, complexity) | Per-service IRD |
| 17 | Token storage (localStorage / httpOnly cookie) | IRD-004 (frontend) |
| 18 | Security headers (set by gateway or per service) | IRD-003 (gateway) |
| 19 | Seed/fixture data for tests | IRD-000 or per-service IRD |
| 20 | Migration naming convention (001_create_users.sql or timestamp) | IRD-000 |

For each item: ✅ decided and documented | ⬜ undecided | ⚠️ decided but not documented

### Check C — DOP–IRD boundary audit
For each IRD, check: does it contain anything that belongs in the DOP?
- API contracts → IRD ✅
- Data model → IRD ✅
- Business goals → DOP (remove from IRD if found)
- User-visible acceptance criteria → DOP (remove from IRD if found)

For the DOP, check: does it contain anything that belongs in an IRD?
- Endpoint details → IRD (remove from DOP if found)
- Schema details → IRD (remove from DOP if found)

### Check D — Missing document check
Ask for each:
- Does IRD-000 exist? If not, does the content need it? (yes if Check A found repetition)
- Is there a cross-service flow that has no diagram? (login → gateway → user-service)
- Is there a sprint task that references a decision that isn't documented anywhere?

---

## Step 3 — Output a Gap Report

Format:

```
=== Cross-Document Review ===

REPEATED RULES (move to IRD-000):
  ⚠ Error format { "error": "..." } — in IRD-001 §4, IRD-002 §4, IRD-003 §5
  ⚠ Dockerfile multi-stage pattern — in IRD-001 §6, IRD-002 §6

UNDECIDED (no document owns this):
  ⬜ UUID version — not specified anywhere
  ⬜ Date/time format in API responses — not specified anywhere
  ⬜ Pagination — GET /tasks has no limit/offset standard

DECIDED BUT NOT DOCUMENTED:
  ⚠ Soft delete — IRD-002 DELETE endpoint exists but no policy stated

DOP–IRD BOUNDARY:
  ✅ Clean — no violations found
  (or) ⚠ IRD-002 §3 contains business-level goal — move to DOP-001

MISSING DOCUMENTS:
  ⬜ IRD-000 — does not exist; needs to be created before Sprint 1
  ✅ All service IRDs present

ACTIONS REQUIRED BEFORE SPRINT 1:
  1. Create IRD-000 with: [list of rules to centralize]
  2. Decide and document: UUID version, date format, pagination
  3. State delete policy in IRD-002
```

---

## Step 4 — Resolve Each Gap

For each gap found, ask the user:

**Repeated rule:**
> "Error format appears in 3 IRDs. Move it to IRD-000 and replace with reference? (yes / skip)"

**Undecided:**
> "UUID version is not specified. Recommend: PostgreSQL gen_random_uuid() (UUID v4) — DB generates it. Lock this? (yes / decide differently)"

**Missing document:**
> "IRD-000 doesn't exist. Create it now with the centralized rules? (yes / skip)"

If yes: make the change immediately. If skip: add to gap log for next session.

---

## Rules

- Never mark a gap as resolved unless the document is updated and saved
- Never start Sprint 1 implementation if Check B has ⬜ items that will be hit in Sprint 1
- If a gap will NOT affect Sprint 1 work, it can be deferred — but must be logged
- Run this skill again whenever a new IRD is written
- The output gap report should be saved to `docs/gap-log.md` if there are open items
