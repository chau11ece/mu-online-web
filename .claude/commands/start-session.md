# /start-session — Begin a New Session

Run this at the start of every session to restore full context and confirm what needs to be done.

---

## Steps

### 1. Load context

- Read the latest daily log from `daily-logs/` (highest day number)
- Read `memory/MEMORY.md` — load all relevant memory files
- Print the **Tomorrow's Context Block** from the last daily log — this is where we left off

### 2. Check today's program DOP and IRD (System A)

Determine today's day number from the daily log filename.
Check `program-standards/day-XX/` for:

- `DOP-XX-*.md` — today's program DOP
- `IRD-XX-*.md` — today's program IRD

**If EITHER is missing:**
```
⚠️  Program DOP-XX or IRD-XX not yet created for today.
    These must be created before any implementation work.
    Run /write-dop to create DOP-XX, then /write-ird to create IRD-XX.
    Both go in program-standards/day-XX/ and push to Notion.
```
Do not proceed to Step 3 until both exist.

**If both exist:**
- Read the DOP — print the AC checklist with current status (✅ / ⬜)
- Identify which ACs are incomplete

### 3. Cross-document review gate (first sprint day only)

If today is the **first day of a new sprint** (sprint-01 not yet started, or a new sprint just defined):

Run `/review-docs` before any implementation task begins.

This is mandatory — not optional. Sprint 1 implementation must not start with undecided or undocumented decisions.
The `/review-docs` output will show gaps. Resolve blocking ones before proceeding.

Skip this step if: sprint is already in progress and /review-docs was run at sprint start.

### 4. Identify in-progress work

From the last daily log "What's in progress / unfinished" section:

- List any tasks still open
- List any blockers carried over
- List any IRD gaps flagged

### 5. Print a ready-to-go session plan

Format:
```
=== Session Ready ===

Context:    [one sentence — where we left off]

DOP-XX ACs:
  ✅ AC-01: [done]
  ⬜ AC-02: [not done — work on this next]
  ⬜ AC-03: [not done]

In progress:
  - [task or blocker carried from yesterday]

AWS needed today? [yes/no — only prompt MFA if yes]

First action:
  → [specific next step — create DOP, fix IRD gap, implement T-01, etc.]
```

### 6. AWS check (only if today's work involves AWS)

If today's DOP or daily log mentions AWS, EC2, VPC, Terraform, or EKS:
- Remind user to run: `source ./scripts/aws-session-init.sh`
- After MFA confirmed: check running EC2s, NAT Gateway state, any IP changes

Skip this step entirely if today's session is code-only (Docker, K8s manifests, CI/CD pipelines, mock project implementation).

---

## Decision Logic

```
Has DOP-XX?  No  → Create DOP-XX first (/write-dop)
Has IRD-XX?  No  → Create IRD-XX next (/write-ird)
ACs complete? No → Continue working on incomplete ACs
ACs complete? Yes → Ask: "Today's assignment complete.
                          Do you have day-XX+1.html to start next topic,
                          or is there mock project work to continue?"
```

---

## Rules

- Never skip the DOP/IRD check — it is the first gate every session
- Never mark an AC complete unless it is verifiable right now (file exists, test passes, push confirmed)
- If the daily log says "MISSING" for any artifact — that is the first thing to fix
- If there is no day-XX.html yet — resume the previous day's incomplete ACs or mock project sprint tasks
