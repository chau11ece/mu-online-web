---
name: write-ird
description: |
  Creates a new IRD (Implementation Requirements Definition) page in the trainee's IRDs Notion database.
  An IRD records settled design decisions and standards the agent follows during implementation.
  Triggers on "write IRD", "create IRD", "define standards for [topic]", "/write-ird".
---

# Write IRD

You are a **standards architect**. Help the trainee define the implementation rules their agent will follow — before the agent writes a single line of config or code.

An IRD answers: **How exactly will this be implemented? What are the decisions, naming conventions, and non-negotiable rules?**

## Prerequisites

The IRDs Notion database must exist. Check `CLAUDE.md` for a `Doc System (Notion Databases)` section with `IRDs data source`.
If missing, tell the trainee: "Run `/setup-docs` first to create your project doc databases."

## MCP Server

All Notion operations use `mcp__notion`.

## IRD Types

| Type | When to write | Example |
|------|--------------|---------|
| Architecture | Defines how services/components are structured | "Backend Service Architecture" |
| Design | Specifies how a particular feature or system works | "Notification Service Design", "Database Schema" |
| Standard | Cross-cutting rules all services must follow | "REST API Standards", "CI/CD Pipeline Convention", "Naming Conventions" |

## Core Rules

1. **Decisions are settled.** An IRD is not a discussion doc. It records what has been decided. The agent implements from the IRD — not from general knowledge.
2. **Tables over prose.** Naming conventions, config values, required flags — always in tables. Tables are agent-readable. Prose hides assumptions.
3. **One home per fact.** If a standard appears in two IRDs, one is wrong or will drift. Put it in the most specific IRD.
4. **Show draft before creating.** Trainee reviews and approves all decisions before the IRD is written.
5. **IRDs evolve.** When the standard changes, update the IRD — don't append notes at the bottom.

## Flow

### 1. Understand the topic

Ask:
- What is this IRD defining? (service architecture / API standard / naming convention / CI/CD pattern / etc.)
- What type is it: Architecture, Design, or Standard?
- Which DOP(s) does this IRD govern? (to set the DOPs relation)
- What GitHub repo does this apply to?
- What have they learned about this topic that informs the decisions?

### 2. Draft the IRD content

Use this structure — adapt sections to the IRD type:

```
## Decision

[Numbered list of settled decisions — one decision per line, one sentence each.]

1. [Settled decision]
2. [Settled decision]
3. [Settled decision]

---

## Summary
[2–3 sentences. What does this IRD define? What does it NOT cover?]

---

## Context
[Why this IRD exists. What problem does it solve? What constraints shaped these decisions?]

---

## Scope

**This IRD covers:**
- [Topic or concern]

**This IRD does not cover:**
- [Adjacent topic handled in a different IRD]

---

## Proposal

### 1. [Section name — e.g. Naming Convention]

[Table or description of the standard]

| Resource | Pattern | Example |
|----------|---------|---------|
| [type]   | [pattern] | [example] |

### 2. [Section name — e.g. Required Configuration]

| Setting | Value | Notes |
|---------|-------|-------|
| [key]   | [value] | [applies to: all / dev / prod] |

### 3. [Section name — e.g. Rules the Agent Must Follow]

- ALWAYS: [rule]
- ALWAYS: [rule]
- NEVER: [anti-pattern]
- NEVER: [anti-pattern]

---

## Impact
[What changes for other services, pipelines, or team members when this IRD is followed.]
```

**For Architecture IRDs:** focus on component diagram, layer structure, database ownership rules, inter-service communication model.
**For Design IRDs:** focus on data flow, schema tables, event model, failure handling.
**For Standard IRDs:** focus on naming tables, required config values, allowed/forbidden patterns.

### 3. Confirm and create in Notion

Show the full draft. Once approved:

1. Read `CLAUDE.md` → get the `IRDs data source` UUID (format: `collection://[uuid]`)
2. Create the page with `mcp__notion__API-post-page`:
   - `parent`: `{"type": "database_id", "database_id": "<uuid from step 1>"}`
   - `properties` — check an existing IRD page's schema first via `mcp__notion__API-retrieve-a-page` if unsure of exact select-option names:
     - `Title`: `{"title": [{"text": {"content": "IRD title"}}]}`
     - `Type`: `{"select": {"name": "Architecture"}}` (or Design/Standard)
     - `Status`: `{"select": {"name": "Draft"}}`
     - `Owning Team`: `{"rich_text": [{"text": {"content": "trainee/team name"}}]}`
     - `GitHub Repo`: `{"url": "https://..."}`
     - `DOPs`: `{"relation": [{"id": "<governing DOP's page id>"}]}` — set this at creation time; it auto-populates the DOP's `Linked IRDs` two-way relation, no separate update needed
   - Do NOT pass a `content` param — `API-post-page` has no such field
3. Push the body with `mcp__notion__API-update-page-markdown` on the returned page id: `type: "replace_content"`, `replace_content: {"new_str": "<full IRD body, Notion-flavored markdown, starting at '## Decision'>"}`
   - **Never put a literal `|` character inside a table cell's content** (e.g. `dev | staging | prod`) — Notion's markdown parser reads it as an extra column separator and corrupts the row. Use `/` or a comma instead.
4. Return the Notion URL (in the `API-post-page` response)
5. Confirm to the trainee that the linked DOP's "Linked IRDs" now shows this IRD (two-way relation — no manual step needed on their end).
