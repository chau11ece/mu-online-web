# /save-skill — Capture an Acquired Technical Pattern

Run this whenever you've just solved a non-obvious technical problem and want to preserve the solution for future sessions and future projects.

**Trigger phrases:** "save this pattern", "log this skill", "save skill", `/save-skill`

---

## When to Use

Use this command when you have just:
- Gotten a config to work after debugging
- Found the non-obvious flag/option that made something click
- Figured out a sequencing or dependency order that wasn't obvious from docs
- Solved a debugging problem using a repeatable diagnostic sequence

Do NOT use for:
- General theory (add to `memory/<topic>.md` instead)
- Behavioral preferences (auto-memory handles those)
- One-off hacks that only apply to this exact file

---

## Steps

### 1. Identify the skill

Ask the user (or infer from context):
- **Domain:** Docker / Docker Compose / AWS / Terraform / K8s / Bash / CI/CD / Observability
- **Problem statement:** What situation does this skill solve? (one sentence)
- **Solution:** The exact command, config, or sequence that worked
- **Why it works:** The mental model (not "it just does" — explain the mechanism)
- **Gotcha:** What breaks if you get this wrong

### 2. Format the skill entry

```markdown
### [Short name — what the skill does]

**Problem:** [one sentence — when does this apply?]

**Solution:**
[exact command, config, or code block]

**Why it works:** [mechanism — the WHY]

**Gotcha:** [what breaks if misconfigured]

**First solved:** [project name] — [Day XX or date]
```

### 3. Append full entry to the domain file

Map the domain to its file:

| Domain | File |
|--------|------|
| Bash / Scripting | `memory/skills/bash.md` |
| Docker / Docker Compose / Swarm | `memory/skills/docker.md` |
| Kubernetes (core, minikube) | `memory/skills/k8s.md` |
| Helm | `memory/skills/helm.md` |
| EKS (multi-node, ingress, lifecycle, networking) | `memory/skills/eks.md` |
| AWS / VPC | `memory/skills/aws.md` |
| Python CLI (Click, Typer) | `memory/skills/python-cli.md` |
| Playwright / E2E | `memory/skills/playwright.md` |
| Notion / MCP API | `memory/skills/notion-mcp.md` |
| Debugging Patterns | `memory/skills/debugging.md` |
| CI/CD | `memory/skills/ci-cd.md` |
| Node.js, Microservices, Teaching, Browser, CORS | `memory/skills/misc.md` |

Append the full skill entry (Problem / Solution / Why it works / Gotcha / First solved) to the correct domain file.

If the domain doesn't exist yet, create a new `memory/skills/<domain>.md` file.

**After appending, check the line count:**
```bash
wc -l memory/skills/<domain>.md
```
If the file exceeds 400 lines, warn the user: *"<domain>.md is now N lines — consider splitting into sub-domain files (e.g. eks-lifecycle.md, eks-networking.md) before the next session."*

### 4. Add one-line index entry to skills-ledger.md

Open `memory/skills-ledger.md` and add a bullet under the correct domain heading:
```
- [Short Skill Name](skills/<domain>.md) — trigger condition one-liner (when does this apply?)
```

The trigger condition must be specific enough that you can decide whether to open the file from the index alone — without opening it.

If the domain heading doesn't exist yet in the index, add it before the bullet.

### 5. Confirm

Print:
```
✅ Skill saved: [short name]
   Domain: [domain]
   Domain file: memory/skills/<domain>.md  (N lines)
   Index entry added: memory/skills-ledger.md
   Reusable in: [list any current projects where this applies]
```

---

## Rules

- Never add theory — only patterns that have been verified to work
- If the solution has a prerequisite (e.g., "only works if X is configured"), state it in the Gotcha
- The "Why it works" section must explain the mechanism, not restate the solution
- Each entry must be self-contained — someone reading it cold should be able to apply it
