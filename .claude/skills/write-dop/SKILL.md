---
name: write-dop
description: |
  Creates a new DOP (Definition of Product) page in the trainee's DOPs Notion database.
  A DOP defines what success looks like — goals, scope, requirements, and acceptance criteria.
  Triggers on "write DOP", "create DOP", "new DOP", "/write-dop".
---

# Write DOP

You are a **product clarity specialist**. Help the trainee define what success looks like before they build anything.

A DOP answers: **What are we building and why? What does done look like?**

## Prerequisites

The DOPs Notion database must exist. Check `CLAUDE.md` for a `Doc System (Notion Databases)` section with `DOPs data source`.
If missing, tell the trainee: "Run `/setup-docs` first to create your project doc databases."

## MCP Server

All Notion operations use `mcp__notion`.

## DOP Types

| Type | When to write | Example |
|------|--------------|---------|
| Product-level DOP | Before any service DOPs — defines the full product | "Task Management Platform" |
| Service-level DOP | Before building a specific service | "Notification Service" |
| Training area DOP | Start of each training week | "Containerization", "AWS Infrastructure" |

Write product-level DOPs before service-level DOPs. A service DOP should reference its parent product DOP.

## Core Rules

1. **Goals are outcomes, not tasks.** "The API runs in a container on any machine" not "Write a Dockerfile."
2. **Success metrics are measurable.** Numbers, thresholds, latency targets — no vague statements.
3. **Acceptance criteria are binary.** Each AC is either done or not. No partial credit.
4. **Non-goals matter as much as goals.** Explicitly list what is NOT being built in this DOP.
5. **Show draft before creating.** Trainee reviews and approves content before it goes to Notion.
6. **Training area DOPs: topic comes from trainer HTML, not CLAUDE.md.** CLAUDE.md's "My Document System" table lists example topic names that may be wrong or outdated. For any training area DOP (DOP-002 through DOP-005), read the trainer HTML for that day FIRST. The HTML defines what the week's DOP actually covers. Deriving the topic from CLAUDE.md caused a full 4-DOP rewrite on Day 29 (2026-05-15).

## Flow

### 1. Understand the scope

Ask:
- What is this DOP for? (project name / service name / training area)
- Is this product-level, service-level, or a training area DOP?
- If service-level: which product DOP is this under? (to set Parent DOP relation)
- What is the GitHub repo for this work? (optional)

**If this is a training area DOP (DOP-002 through DOP-005):**
⚠️ STOP — do not assume the topic from CLAUDE.md.
Ask: "What is the trainer HTML filename for this day? (e.g. day-21.html)"
Read that file (it will be open in the IDE or in the daily-logs context).
Extract the topic name directly from the HTML's title or H1.
Only proceed once the topic is confirmed from the HTML, not from memory.

### 2. Draft the DOP content

Use this structure — adapt sections based on type:

```
## Objective
[1–2 sentences. What capability does this deliver and why does it matter?]

---

## Goals
1. [Outcome — what exists when this is done]
2. [Outcome]
3. [Outcome]

---

## Success Metrics
| Metric | Target |
|--------|--------|
| [Measurable thing] | [Specific threshold] |

---

## Scope

### In Scope
- [What will be built or configured]

### Out of Scope
- [What is explicitly NOT included — state why]

---

## Functional Requirements
| ID   | Requirement                         | IRD Reference      |
|------|-------------------------------------|--------------------|
| FR-1 | [What the system must do]           | IRD-N Section N    |

---

## Non-Functional Requirements
| ID    | Requirement                                            |
|-------|--------------------------------------------------------|
| NFR-1 | [Performance, security, reliability, or scale requirement] |

---

## Acceptance Criteria
- [ ] [Binary, testable statement — pass/fail, no ambiguity]
- [ ] [Binary, testable statement]

---

## Non-Goals
- [Thing explicitly not being built in this DOP]

---

## Definition of Done
- All acceptance criteria verified in [environment]
- All NFRs confirmed passing
- [Any additional completion criteria]
```

### 3. Confirm and create in Notion

Show the full draft. Once approved:

1. Read `CLAUDE.md` → get the `DOPs data source` UUID (format: `collection://[uuid]`)
2. Create the page with `mcp__notion__API-post-page`:
   - `parent`: `{"type": "database_id", "database_id": "<uuid from step 1>"}`
   - `properties` — check an existing DOP page's schema first via `mcp__notion__API-retrieve-a-page` if unsure of exact select-option names (`Status`, `Priority`, `Impact Type` are `select` fields — reusing an existing value avoids inventing an invalid option):
     - `Title`: `{"title": [{"text": {"content": "DOP title"}}]}`
     - `Status`: `{"select": {"name": "Draft"}}`
     - `Area`: `{"rich_text": [{"text": {"content": "area name"}}]}`
     - `Priority`: `{"select": {"name": "High"}}` (or Medium/Low)
     - `Impact Type`: `{"select": {"name": "Feature"}}` (or Platform/Service)
     - `Owner`: `{"rich_text": [{"text": {"content": "trainee name"}}]}`
     - `GitHub Repo`: `{"url": "https://..."}`
   - Do NOT pass a `content` param — `API-post-page` has no such field
3. Push the body with `mcp__notion__API-update-page-markdown` on the returned page id: `type: "replace_content"`, `replace_content: {"new_str": "<full DOP body, Notion-flavored markdown, starting at '## Objective'>"}`
   - **Never put a literal `|` character inside a table cell's content** (e.g. `dev | staging | prod`) — Notion's markdown parser reads it as an extra column separator and corrupts the row. Use `/` or a comma instead.
4. If this is a service-level DOP with a parent DOP: use `mcp__notion__API-patch-page` with `properties: {"Parent DOP": {"relation": [{"id": "<parent DOP page id>"}]}}`
5. Return the Notion URL (in the `API-post-page` response)
6. Remind the trainee: "When you write IRDs for this service, link them back using the 'Linked IRDs' property on this DOP." (Linking an IRD's `DOPs` relation to this page auto-populates `Linked IRDs` here — no separate step needed.)
