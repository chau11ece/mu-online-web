# /do-cost-check — DigitalOcean Running Cost Snapshot

Check what DigitalOcean resources are currently running, ranked by monthly cost, with a teardown plan.

## Steps

1. Run the audit script:
   ```bash
   bash /Users/mac/Desktop/chautv-proops2026/scripts/do-cost-check.sh
   ```
2. **Exit code 2** (no `doctl`/credentials) — tell the user DigitalOcean isn't configured here; stop.
3. **Exit code 0** — print `✅ DigitalOcean: no chargeable resources.`; stop.
4. **Exit code 1** — the script already printed the ranked cost table and teardown plan. Ask:

   > "DigitalOcean resources are running (~$X/mo if left running). Tear down now?
   > Options: **yes** (run teardown commands) / **no** (leave running) / **later** (save commands to a script)"

   - **yes** — run each teardown command from the script output one at a time, confirming before each. If a resource is Terraform-managed (check the sibling `terraform/` repo's state), prefer `terraform destroy -target=<resource>` over calling `doctl` directly, so state stays accurate.
   - **no** — tell the user the estimated ongoing monthly cost.
   - **later** — write the teardown commands to `/tmp/teardown-now.sh` and print the path.

## Important DigitalOcean-specific note

Unlike AWS EC2, DigitalOcean bills droplets/volumes/load balancers by the hour for as long as the resource **exists** — powering off a droplet does NOT stop billing. The only way to actually stop the charge is to destroy the resource (snapshot first if it needs to come back later).

## Also runs automatically

This same script is invoked by `/report`'s Phase 5 (Cloud Cost Check) at the end of every session, alongside the AWS equivalent — this command is for checking mid-session without doing a full session close.
