# Skill: /setup-docs

Triggers on: `/setup-docs`, "setup docs", "create my DOP and IRD databases", "initialize project docs"

---

## What this skill does

Creates two linked Notion databases — **DOPs** and **IRDs** — under your workspace.
Run this once before writing your first DOP or IRD.

What gets created:
- Parent page: `📄 Project Docs — [Project Name]`
- **DOPs** database — Title, ID (auto `DOP-N`), Status, Area, Priority, Impact Type, Owner, GitHub Repo, Linked IRDs (relation), Parent DOP (self-relation), Sub-DOPs (self-relation)
- **IRDs** database — Title, ID (auto `IRD-N`), Type, Status, Owning Team, GitHub Repo, DOPs (relation back to DOPs)

After creation: saves the database IDs into `CLAUDE.md` so `/write-dop` and `/write-ird` know where to create pages.

---

## Prerequisites

- Notion MCP is connected. Run `/mcp` to verify.
- `/setup-notion` has been run (so you have a root Notion page). If not, run that first.

---

## Instructions for Claude

### Step 1 — Gather inputs

Read `CLAUDE.md`. Extract:
- Trainee name (Identity & Role section)
- Project name (mock project context — e.g. "Task Management System")
- Root Notion page URL or ID (Doc System section, set by `/setup-notion`)

If project name is not in CLAUDE.md, ask: "What is your project name? (e.g. Task Management System)"
If root Notion page ID is missing, ask: "What is your Notion workspace URL from when you ran /setup-notion?"

---

### Step 2 — Create parent page

Use `mcp__notion__notion-create-pages`:
- Parent: root page ID
- Title: `📄 Project Docs — [Project Name]`
- Icon: 📄

Save returned `page_id` as `PARENT_PAGE_ID`.

---

### Step 3 — Create IRDs database (first — DOPs will relate to it)

Use `mcp__notion__notion-create-database`:
- Parent: `PARENT_PAGE_ID`
- Title: `IRDs`
- Schema:

```sql
CREATE TABLE (
  "Title" TITLE,
  "ID" UNIQUE_ID PREFIX 'IRD',
  "Type" SELECT('Architecture':blue, 'Design':purple, 'Standard':green),
  "Status" SELECT('Draft':gray, 'In Review':yellow, 'Approved':green, 'Deprecated':red),
  "Owning Team" RICH_TEXT,
  "GitHub Repo" URL
)
```

From the response, extract the data source ID (the UUID inside `collection://...`).
Save as `IRDS_DS_ID`.

---

### Step 4 — Create DOPs database (relates to IRDs)

Use `mcp__notion__notion-create-database`:
- Parent: `PARENT_PAGE_ID`
- Title: `DOPs`
- Schema — substitute the actual `IRDS_DS_ID` value into the RELATION call:

```sql
CREATE TABLE (
  "Title" TITLE,
  "ID" UNIQUE_ID PREFIX 'DOP',
  "Status" SELECT('Draft':gray, 'In Review':yellow, 'Approved':green, 'Deprecated':red),
  "Area" RICH_TEXT,
  "Priority" SELECT('High':red, 'Medium':yellow, 'Low':green),
  "Impact Type" SELECT('Platform':blue, 'Service':purple, 'Feature':green),
  "Owner" RICH_TEXT,
  "GitHub Repo" URL,
  "Linked IRDs" RELATION('[IRDS_DS_ID]', DUAL 'DOPs')
)
```

From the response, extract the data source ID.
Save as `DOPS_DS_ID`.

---

### Step 5 — Add Parent DOP self-relation (two-step — requires DB to exist first)

Use `mcp__notion__notion-update-data-source` on `DOPS_DS_ID` with statements:

```sql
ADD COLUMN "Parent DOP" RELATION('[DOPS_DS_ID]', DUAL 'Sub-DOPs' 'sub_dops');
ADD COLUMN "Sub-DOPs" RELATION('[DOPS_DS_ID]', DUAL 'Parent DOP' 'parent_dop')
```

Check the response schema. If duplicate columns were created (e.g. "Parent DOP 1", "Sub-DOPs 1"), drop them:

```sql
DROP COLUMN "Parent DOP 1";
DROP COLUMN "Sub-DOPs 1"
```

---

### Step 6 — Save IDs to CLAUDE.md

Use the Edit tool to add (or replace) a `## Doc System (Notion Databases)` section in `CLAUDE.md`:

```
## Doc System (Notion Databases)
DOPs data source: collection://[DOPS_DS_ID]
IRDs data source: collection://[IRDS_DS_ID]
DOPs page: [DOPs database URL from response]
IRDs page: [IRDs database URL from response]
```

---

### Step 7 — Report

Output to the trainee:

```
✓ Project doc system ready for [Project Name]

  📄 Project Docs — [Project Name]
    ├── DOPs database  →  [URL]
    └── IRDs database  →  [URL]

DOPs ↔ IRDs are two-way linked.
Setting "Linked IRDs" on a DOP automatically appears under that IRD's "DOPs" property.

Next steps:
  /write-dop   — create your first DOP (start with the product-level DOP for your whole project)
  /write-ird   — create an IRD after you define the technical approach for a service
```

---

## Error handling

- **Notion MCP not connected**: "Run `/mcp`. Notion must be listed and show as connected."
- **Page creation fails**: Verify the root Notion page is shared with your Notion integration. Open the page in Notion → `...` → Connections → add your integration.
- **Duplicate self-relation columns**: Drop "Parent DOP 1" and "Sub-DOPs 1" using `notion-update-data-source`.
- **IRDS_DS_ID not found in response**: Re-fetch the IRDs database URL using `notion-fetch` to get the data source ID from the `<data-source>` tag.
