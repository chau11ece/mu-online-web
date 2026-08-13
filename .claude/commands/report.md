# /report — Close Session + Generate Report

Run this once at the end of every session. It handles everything in order:
daily log → memory → DOP ACs → skill gaps → commit all repos → trainer report.

---

## Phase 0 — Check for a project-specific override

Before Phase 1, check the current working directory's own `CLAUDE.md` for a project-specific session-closing convention (e.g. a "What Has Been Built" table, its own DOP/IRD numbering not tied to day-XX).

**If found** (a real/non-mock project with its own tracking, not the day-XX training curriculum):
- **Phase 1 (Daily Log)** → instead of `daily-logs/day-XX.md`, update the project's own tracking table/section in its `CLAUDE.md` with what was done this session
- **Phase 3 (DOP ACs)** → there is no `program-standards/day-XX/`; instead sync whichever project-specific DOPs/IRDs were touched this session directly against their real Notion pages (create/update as needed, same Notion-sync requirement applies — a DOP/IRD only on GitHub is still invisible to anyone checking Notion)
- **Phase 5 (Cloud Cost Check)** → run whichever provider script(s) actually apply (AWS and/or DigitalOcean); each self-skips via exit code 2 if that provider's credentials aren't present — note the real provider(s) used in the closing summary
- **Phase 6 "Trainer Report"** → there is no trainer on a real client/business project; produce a plain session summary instead (what changed, artifacts with paths, real blockers, next steps) — do not force the DOP-XX/trainer-specific format
- Phases 2 (Memory), 4 (Skill Gap Check), and 6 "Commit All Repos" apply unchanged regardless of project type

**If not found:** this is a training/curriculum-tracked session — run Phases 1-6 as written below.

---

## Phase 1 — Daily Log

Check `daily-logs/` for today's log (highest day number).

**If missing:** Write it now using the session. Include:
- Today's Assignment checklist (what was planned)
- Completed (what actually got done)
- Not Completed (what didn't, and why)
- Issues Hit and How We Solved Them (real blockers only)
- Extra Things Explored
- Artifacts Built Today (with exact paths)
- How I Used Claude Code Today
- Blockers / Questions for Mentor (one real question)
- Self Score (Completion / Understanding / Energy out of 10)
- One Thing I Learned Today That Surprised Me
- Tomorrow's Context Block (3 sentences: where I am, what's unfinished, first thing tomorrow)

**If exists:** Verify the Tomorrow's Context Block is filled and reflects actual end-of-session state (not planned state).

---

## Phase 2 — Memory Update

Scan the session for things worth persisting across future sessions. For each item found, check if a memory file already covers it — update existing files before creating new ones.

Write or update memory files for:

| Type | Write when |
|------|-----------|
| `feedback` | User corrected my approach, confirmed a non-obvious choice, or said "don't do X" / "always do Y" |
| `project` | New decision made, scope changed, IRD gap found or fixed, sprint state changed |
| `user` | Learned something new about how the user works, what they know, or what they prefer |
| `reference` | Learned where something lives (GitHub repo URL, Notion page, Linear team, etc.) |

Update `memory/MEMORY.md` index for any new files added.

**Do not write memory for:** things already in CLAUDE.md, things in git history, code patterns the agent can derive by reading files.

---

## Phase 3 — DOP Acceptance Criteria Update + Notion Sync

Read today's program DOP from `program-standards/day-XX/DOP-XX-*.md`.

For each AC:
- Mark ✅ if the artifact exists, is complete, and is verifiable right now
- Leave ⬜ if not done — do not guess or estimate
- Save the updated file

**Notion sync (mandatory — do not skip):**

For every DOP and IRD created or updated this session:
1. Check if a Notion page exists — look for a `Notion:` row in the property table of the local file
2. If missing: create the page in Notion immediately using the MCP Notion tools
   - DOPs → parent page `337dde5fafa98179857ae78c395c56a5` (📦 DOPs)
   - IRDs → parent page `337dde5fafa98115b11bc2c8ae01cdff` (📐 IRDs)
3. Add the returned Notion URL as a `Notion:` row in the local file's property table
4. Trainer report ARTIFACTS must include the Notion URL — not just the file path

**Why:** The trainer verifies progress via Notion, not GitHub. A DOP or IRD that exists only on GitHub is invisible to the trainer.

---

## Phase 4 — Skill and Workflow Gap Check

Ask: did any skill produce wrong output, miss a step, or need correction during this session?

For each gap found:
1. Name the skill/command file
2. State what was wrong
3. Propose the fix (1-3 lines)
4. Ask user: "Update this skill now? (yes / skip)"

If yes: edit the skill file, commit in Phase 5.
If no skill gaps found: skip this phase silently.

---

## Phase 5 — Cloud Cost Check

Run BOTH provider audit scripts — each is self-contained and exits 2 silently if that provider's credentials/tools aren't present, so it's safe to always run both rather than trying to detect which provider a session used:

```bash
bash /Users/mac/Desktop/chautv-proops2026/scripts/aws-cost-check.sh
bash /Users/mac/Desktop/chautv-proops2026/scripts/do-cost-check.sh
```

For each script, by its exit code:

**Exit code 2 (no credentials/tools):** skip silently — session was not on that provider.

**Exit code 0 (nothing running):** print `✅ <Provider>: no chargeable resources.` and continue.

**Exit code 1 (resources found):** the script already printed the ranked cost table and teardown plan.
Then ask:

> "<Provider> resources are still running (~$X/mo if left running). Tear down now?
> Options: **yes** (run teardown commands) / **no** (leave running) / **later** (save commands to teardown-now.sh)"

- **yes** — execute each teardown command from the script output, one group at a time, confirming before destructive/irreversible steps (eksctl delete cluster, rds delete-db-instance, doctl databases delete, doctl compute droplet delete). If the resource is Terraform-managed, prefer `terraform destroy -target=<resource>` over calling the provider CLI directly, so state stays accurate.
- **no** — note in session summary: "⚠️ <Provider> resources left running — estimated cost: $X/mo"
- **later** — write the teardown commands to `/tmp/teardown-now.sh`, print the path

If both scripts find chargeable resources, handle them as two separate yes/no/later decisions — don't force one answer to cover both providers.

---

## Phase 6 — Commit All Repos

### chautv-proops2026 (always)

```
git status
git add daily-logs/day-XX.md memory/ program-standards/ .claude/
git commit -m "Day XX — [topic]: daily log, memory update, DOP-XX ACs"
git push
```

### Service repos (if touched this session)

Check each repo that had work done today. As of the Day 38 audit
(2026-07-03), the task-manager services (`user-service`, `task-service`,
`api-gateway`, `frontend-service`, `notification-service`), `docs/`,
`agent-ops/`, `.claude/skills/`, and `runbooks/` all live inside **one**
consolidated repo — `~/Desktop/proops2026-taskmanager` (remote:
`proops2026-taskmanager/scripts`) — not separate repos. Check that one repo,
not per-service paths.

For each repo with uncommitted changes:
```
git status
git add [changed files]
git commit -m "[description of what was done]"
git push
```

Report any repo that still has uncommitted changes after this phase.

---

## Phase 6 — Trainer Report

Read `memory/daily-report-template.md` and fill it using actual state only.

Rules:
- `Commit:` — "pushed" only if git push ran successfully in Phase 5
- `ARTIFACTS` — one line per artifact: name · DONE/PARTIAL/MISSING · exact path
- `BLOCKED` — real blocker with root cause, or "None"
- `TOMORROW` — copy Tomorrow's Context Block from today's daily log word for word
- `QUESTION` — one question from "Blockers / Questions for Mentor" in the daily log

Print the report inside a code block, ready to copy-paste to trainer.

---

## Rules

- Run phases in order — never skip Phase 1 or 2
- Never mark an AC ✅ unless the artifact exists and is pushed right now
- Never write planned state as current state anywhere
- Memory update happens every session — not just when something "big" happened
- If a session had no new learnings worth persisting, write a one-line note in the relevant project memory confirming the session state
