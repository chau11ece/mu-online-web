# !scope-pivot — Impact Audit + Sprint Reschedule on Scope Change

Run this whenever a feature is added to or removed from a project mid-sprint. It prevents the common failure mode where the DOP and sprint backlog say one thing but the IRDs say another, and half the sprint tasks become either unnecessary or missing.

**Trigger:** `!scope-pivot [add|remove] "[feature description]" --project [project-path]`

Example:
```
!scope-pivot add "wishlist feature — save products for later" --project /Users/mac/Desktop/proops2026-ecommerce
!scope-pivot remove "payment-service — deferred to Phase 2" --project /Users/mac/Desktop/proops2026-ecommerce
```

---

## When to Use

- A feature is added to the project that wasn't in the original DOP
- A feature is removed, deferred, or descoped
- A service is split or merged
- A status transition rule changes (e.g., adding a new order state)
- An AC in the DOP is revised and downstream IRDs have not been updated

Do NOT run for small wording fixes or typos. Run it when a functional boundary changes.

---

## Steps

### Step 1 — Parse the Pivot Request

Extract from the command:
- **Direction:** `add` or `remove`
- **Feature description:** what is changing
- **Project path:** where to find the docs

If `--project` is omitted, use the current working directory.

---

### Step 2 — Load the Full Document Set

Read in this order:
1. `docs/DOP-001.md` (or the project DOP)
2. `docs/IRD-000-shared-standards.md`
3. All service IRDs (`docs/IRD-00N-*.md`)
4. `docs/sprint-01.md` (or the active sprint file — check frontmatter for `status: active`)
5. `docs/architecture.md`
6. `docs/gap-log.md`

---

### Step 3 — Impact Audit

Scan every document loaded in Step 2.

For each document, search for any mention of the changed feature by:
- Exact name match
- Related concept match (e.g., "wishlist" → also check "saved items", "favorites", "product bookmarks")
- Endpoint match (e.g., adding wishlist → scan for `/wishlists` or `GET /users/:id/wishlist`)
- Data model match (e.g., new table name, new column name)
- Status enum match (e.g., new order status value)
- Sprint task match (e.g., tasks referencing the feature)

Build an Impact Map:

```
IMPACT MAP — [add|remove]: "[feature description]"
─────────────────────────────────────────────────────
DOCUMENT                          | IMPACT
docs/DOP-001.md                   | AC-07 references this feature — must [add/remove/update]
docs/IRD-002-product-service.md   | No impact
docs/IRD-003-order-service.md     | Status enum must include new "WISHLISTED" state — add
docs/sprint-01.md                 | T-18, T-19 reference this feature — [add/reschedule/remove]
docs/architecture.md              | Diagram missing new service node — add
docs/gap-log.md                   | No open gaps related
─────────────────────────────────────────────────────
Total documents affected: [N]
Sprint tasks affected:    [N]
```

---

### Step 4 — Classify Each Impact

For each affected item, classify:

| Class | Meaning | Action |
|-------|---------|--------|
| `REQUIRED` | Without this change, the sprint task cannot be implemented correctly | Must fix before confirming pivot |
| `RECOMMENDED` | The doc is inconsistent with the new scope; won't break a task today but will cause confusion | Fix during pivot |
| `DEFERRED` | Minor inconsistency; non-blocking | Log to gap-log.md |

Print the classified impact list and ask:
```
Proceed with pivot? This will:
  • Update [N] documents (REQUIRED + RECOMMENDED)
  • Log [N] items to gap-log.md (DEFERRED)
  • Reschedule sprint backlog ([N] tasks added / [N] tasks removed)

Confirm? (yes / adjust: ...)
```

---

### Step 5 — Document Updates

Apply changes for every REQUIRED and RECOMMENDED item.

**For `add` pivots:**
- DOP: add new AC(s) in the Acceptance Criteria table
- Relevant IRD: add endpoint(s) to API contract, add table(s) to data model, add validation rules
- IRD-000: add any new shared standard if the feature introduces a cross-cutting concern
- architecture.md: update diagram if a new service or data flow is introduced

**For `remove` pivots:**
- DOP: move removed AC(s) to Out of Scope section with note: `Deferred — [reason] — removed [date]`
- Relevant IRD: strike through or remove endpoint(s), data model, validation rules
- architecture.md: update diagram if a service or data flow is removed

For every edit, print the before/after diff and wait for confirmation before writing.

---

### Step 6 — Sprint Backlog Reschedule

Read the active sprint file. For each task in the backlog:

**If direction is `add`:**
1. Identify tasks that the new feature depends on (prerequisites)
2. Generate new task stubs for the added feature — one task per logical unit:
   - Scaffold (if new service or major module)
   - Implement endpoints
   - Write integration tests
   - Docker Compose entry (if new service)
3. Insert new tasks at the correct epic, with estimate (default M = 1-2 days) and status ⬜
4. Recalculate total estimate

**If direction is `remove`:**
1. Find tasks linked to the removed feature
2. Mark them as `🚫 REMOVED — [reason]` (do not delete — preserve for audit trail)
3. Recalculate total estimate, subtract removed tasks

Print the sprint diff:
```
SPRINT BACKLOG CHANGES
──────────────────────────────────────────────────
ADDED:
  T-18 | Scaffold wishlist-service: Express + TS + PostgreSQL + Docker | 2h | US-07 | ⬜
  T-19 | Implement POST /wishlists + GET /wishlists (paginated) | 3h | US-07 | ⬜
  T-20 | Write integration tests for wishlist-service endpoints | 2h | US-07 | ⬜

REMOVED:
  T-14 | [old task name] | 🚫 REMOVED — feature deferred

ESTIMATE DELTA: +7h / -3h = net +4h
NEW TOTAL: 45h across 20 tasks
──────────────────────────────────────────────────
Accept sprint changes? (yes / adjust: ...)
```

---

### Step 7 — Log to gap-log.md

Append DEFERRED items to `docs/gap-log.md` under a new section:

```markdown
## Pivot: [add|remove] "[feature]" — [date]

| # | Item | Class | Owner | Target |
|---|------|-------|-------|--------|
| G-[N] | [description] | DEFERRED | chau_tv | Sprint [N] |
```

---

### Step 8 — Notion Sync Flag

For every doc that was updated and has a `notion_url` frontmatter field, print:
```
⚠️  Notion sync needed for:
  • docs/DOP-001.md          → https://www.notion.so/[id]
  • docs/IRD-003-order-service.md → https://www.notion.so/[id]
Run /report to push all changes.
```

---

### Step 9 — Confirm

Print:
```
✅ scope-pivot complete.
   Direction:         [add|remove]
   Feature:           "[feature description]"
   Documents updated: [N]
   Tasks added:       [N]
   Tasks removed:     [N]
   Items deferred:    [N] (see gap-log.md)
   Net estimate delta: [+/-Nh]
   Notion sync needed: yes — run /report
```

---

## Rules

- Never delete a sprint task — mark as `🚫 REMOVED` with reason, preserve for audit
- Never update IRDs without showing the before/after diff first
- Never reschedule tasks without printing the sprint diff and receiving confirmation
- If a `remove` pivot affects a task already in progress (status: 🔄), flag it as a blocker before proceeding
- If the pivot introduces a new service, automatically trigger `/review-docs` after Step 5 to catch new cross-IRD gaps
