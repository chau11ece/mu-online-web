#!/bin/bash

# Monitor deployment progress
# Usage: ./monitor-deployment.sh

echo "=== Monitoring CI/CD Deployment to danangmu.com ==="
echo "Started at: $(date)"
echo ""

# Function to check health endpoint
check_health() {
    local response=$(curl -s http://danangmu.com/health.php 2>/dev/null)
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" http://danangmu.com/health.php 2>/dev/null)
    local status=$(echo "$response" | jq -r '.status' 2>/dev/null || echo "unknown")

    echo "[$(date '+%H:%M:%S')] HTTP: $http_code | Status: $status"

    if [ "$status" = "ok" ]; then
        echo ""
        echo "✓ Deployment successful!"
        echo ""
        echo "Health check details:"
        echo "$response" | jq '.'
        return 0
    fi
    return 1
}

# Monitor for up to 10 minutes
max_iterations=60
interval=10
iteration=0

echo "Checking every ${interval}s (max ${max_iterations} attempts = ~10 minutes)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

while [ $iteration -lt $max_iterations ]; do
    if check_health; then
        exit 0
    fi

    sleep $interval
    iteration=$((iteration + 1))
done

echo ""
echo "✗ Deployment did not complete within timeout"
echo ""
echo "Next steps:"
echo "1. Check GitHub Actions: https://github.com/chau11ece/mu-online-web/actions"
echo "2. SSH to droplet and check Docker containers: docker ps -a"
echo "3. Check Ansible logs for deployment errors"
