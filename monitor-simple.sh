#!/bin/bash
echo "=== Monitoring CI/CD Deployment ==="
echo "GitHub Actions: https://github.com/chau11ece/mu-online-web/actions"
echo ""
echo "Checking health endpoint every 10s..."
echo ""

for i in {1..60}; do
    http_code=$(curl -s -o /dev/null -w "%{http_code}" http://danangmu.com/health.php 2>/dev/null)
    response=$(curl -s http://danangmu.com/health.php 2>/dev/null)
    status=$(echo "$response" | jq -r '.status' 2>/dev/null || echo "unknown")
    
    echo "[$(date '+%H:%M:%S')] Attempt $i/60: HTTP $http_code | Status: $status"
    
    if [ "$status" = "ok" ]; then
        echo ""
        echo "✓ Deployment successful!"
        echo ""
        echo "$response" | jq '.' 2>/dev/null || echo "$response"
        exit 0
    fi
    
    sleep 10
done

echo ""
echo "✗ Timeout after 10 minutes"
