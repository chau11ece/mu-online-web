# /cost-check — AWS Running Cost Snapshot

Check what AWS resources are currently running and estimate the hourly cost.

## Steps

1. **List all running EC2 instances** (eu-central-1) — name, type, state
2. **List NAT Gateways** — state (available = billing at ~$0.045/hr + data)
3. **List Elastic IPs** — count unattached EIPs (billed at ~$0.005/hr each when idle)
4. **List S3 buckets** — names only (storage cost is negligible at lab scale)
5. **Calculate estimated hourly cost:**
   - t3.micro = $0.0116/hr each
   - NAT Gateway = $0.045/hr (plus $0.045/GB data)
   - EIP attached to running instance = free
   - EIP unattached = $0.005/hr
6. **Print a summary table** with resource, state, hourly cost
7. **Flag anything that should be stopped or deleted** to save budget (e.g. NAT GW when not practicing)

## Budget context
Monthly budget: $200. NAT Gateway running 24/7 = ~$33/month alone.
Recommend: delete NAT GW at end of each session, recreate next session (~5 min via CLI).
