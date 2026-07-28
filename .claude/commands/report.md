# /report — Close Session + Generate Report

Run this once at the end of every session. It handles everything in order:
daily log → memory → DOP ACs → skill gaps → commit all repos → trainer report.

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

## Phase 5 — AWS Cost Check

Run the cost audit script:

```bash
bash /Users/mac/Desktop/chautv-proops2026/scripts/aws-cost-check.sh
```

**If exit code is 2 (no credentials):** skip silently — session was not AWS-related.

**If exit code is 0 (nothing running):** print `✅ AWS: no chargeable resources.` and continue.

**If exit code is 1 (resources found):** the script already printed the cost table and teardown plan.
Then ask:

> "AWS resources are still running. Tear down now?
> Options: **yes** (run teardown commands) / **no** (leave running) / **later** (save commands to teardown-now.sh)"

- **yes** — execute each teardown command from the script output, one group at a time, confirming before destructive steps (eksctl delete cluster, rds delete-db-instance)
- **no** — note in session summary: "⚠️ AWS resources left running — estimated cost: $X/h"
- **later** — write the teardown commands to `/tmp/teardown-now.sh`, print the path

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
