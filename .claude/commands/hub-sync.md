# !hub-sync — Propagate New Hub Skills into Active Project IRDs

Run this after `/save-skill` has added one or more `#new` patterns to `memory/skills-ledger.md`. It prevents new cross-cutting knowledge from staying siloed in the hub while active project IRDs stay outdated.

**Trigger:** `!hub-sync`

---

## When to Use

- You just ran `/save-skill` and the new skill applies to more than one service
- A sprint retrospective produced a new implementation standard (e.g., retry pattern, health-check shape)
- You upgraded a shared tool (Node.js version, Docker base image, Jest config) and need to propagate it

Do NOT run if the new skill is purely project-specific and has no cross-project applicability.

---

## How Skills Get Tagged `#new`

When `/save-skill` writes an entry to `memory/skills-ledger.md`, the agent appends `#new` to the entry's `**First solved:**` line. Example:

```
**First solved:** E-commerce System — 2026-04-18 #new
```

`!hub-sync` scans for this tag and clears it after the proposal is accepted. This is the handshake: save creates the tag, hub-sync consumes it.

---

## Steps

### Step 1 — Scan for `#new` Skills

Read `memory/skills-ledger.md`. Find every entry whose `**First solved:**` line contains `#new`.

For each tagged skill, extract:
- **Skill name** (the `###` heading)
- **Domain** (the `##` section it belongs to)
- **Problem statement**
- **Solution block** (the code or config)
- **Why it works**
- **Gotcha**

If no `#new` tags are found, print:
```
ℹ️  hub-sync: No new skills to propagate. skills-ledger is up to date.
```
and stop.

---

### Step 2 — Identify Active Projects

Read the workspace CLAUDE.md (`/Users/mac/Desktop/proops2026-ecommerce/CLAUDE.md` and any other active project CLAUDE.md files) to get the list of active projects and their IRD paths.

For each active project, load:
1. `docs/IRD-000-shared-standards.md` (or equivalent shared standards doc)
2. The service IRDs listed in the project CLAUDE.md

---

### Step 3 — Impact Analysis per Skill

For each `#new` skill, determine which documents it touches. Use this mapping:

| Skill domain | Likely IRD target |
|---|---|
| Docker / Dockerfile | IRD-000 §Dockerfile Pattern (or §10) |
| Docker Compose | IRD-000 §Health Check + project's IRD-005 (infrastructure) |
| Node.js runtime | IRD-000 §Tech Stack |
| PostgreSQL | IRD-000 §UUID Standard + per-service data model |
| Test patterns | IRD-000 §Test Standards |
| Microservices / Project Setup | IRD-000 (new section if needed) |
| Bash / Scripting | Hub CLAUDE.md §My Standards only |
| Debugging | Hub CLAUDE.md §My Operating Rules only |
| AWS / Terraform / K8s | Hub CLAUDE.md + training IRDs only |

For each affected IRD section, generate a **proposed diff**: exactly what text would be added, changed, or replaced.

---

### Step 4 — Present Proposals

For each `#new` skill, print a proposal block:

```
────────────────────────────────────────────────────────────
SKILL:   [Skill name]
DOMAIN:  [Domain section]

AFFECTED DOCS:
  • [Project]/docs/IRD-000-shared-standards.md §[Section]
  • [Project]/docs/IRD-00N-[service].md §[Section]  (if service-specific)

PROPOSED CHANGE:
  [Document path] §[Section heading]

  BEFORE:
  [existing text, or "— section does not exist —"]

  AFTER:
  [proposed new text]

Accept this change? (yes / skip / edit: ...)
────────────────────────────────────────────────────────────
```

Present proposals one at a time and wait for confirmation.

---

### Step 5 — Apply Accepted Changes

For each accepted proposal:
1. Edit the target IRD file directly (use Edit tool)
2. If the target IRD has a `notion_url` field in its frontmatter, flag it:
   ```
   ⚠️  Notion sync needed: [IRD path] was updated. Run /report to push changes.
   ```

---

### Step 6 — Clear `#new` Tags

After all proposals are processed (accepted or skipped), remove the `#new` tag from every processed entry in `skills-ledger.md`.

Update `**First solved:**` line: remove ` #new` suffix.

---

### Step 7 — Confirm

Print:
```
✅ hub-sync complete.
   Skills processed: [N]
   Changes applied:  [N] (to [list of files])
   Skipped:          [N]
   Notion sync needed: [yes/no — run /report to push]
```

---

## Rules

- Never apply a change without showing the before/after diff and receiving confirmation
- Never modify a skill entry in skills-ledger.md except to remove the `#new` tag
- If the proposed change would contradict an existing IRD rule, flag the conflict instead of overwriting
- Hub-only skills (Bash, AWS, Terraform, K8s) never propagate to project IRDs — they stay in hub memory only
