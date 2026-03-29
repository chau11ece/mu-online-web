#!/bin/bash

echo "=== MU Online Deployment Diagnostics ==="
echo "Date: $(date)"
echo ""

# Check GitHub repo
echo "📦 Latest Commits:"
git log --oneline -3
echo ""

# Check production site
echo "🌐 Production Status:"
echo -n "  danangmu.com: "
http_code=$(curl -s -o /dev/null -w "%{http_code}" http://danangmu.com/ 2>/dev/null)
echo "HTTP $http_code"
echo ""

# Check if SSH key exists
echo "🔑 SSH Configuration:"
if [ -f ~/.ssh/id_rsa ]; then
    echo "  ✓ SSH key exists"
else
    echo "  ✗ No SSH key found at ~/.ssh/id_rsa"
fi
echo ""

# Provide next steps
echo "📋 Manual Checks Required:"
echo ""
echo "1. Check GitHub Actions:"
echo "   https://github.com/chau11ece/mu-online-web/actions"
echo ""
echo "2. SSH to droplet (requires SSH key configured in GitHub Secrets):"
echo "   ssh root@139.59.192.19"
echo "   docker ps -a"
echo "   docker logs mu-web"
echo ""
echo "3. Check Ansible logs in GitHub Actions workflow output"
echo ""
echo "4. Verify GitHub Secrets are set:"
echo "   - SSH_PRIVATE_KEY"
echo "   - ANSIBLE_VAULT_PASSWORD"
echo "   - DOCKERHUB_USERNAME"
echo "   - DOCKERHUB_TOKEN"
echo ""

# Check if ansible-mu directory exists
echo "📁 Ansible Configuration:"
if [ -d "ansible-mu" ]; then
    echo "  ✓ ansible-mu directory exists"
    if [ -f "ansible-mu/playbooks/deploy-mu.yml" ]; then
        echo "  ✓ deploy-mu.yml playbook exists"
    else
        echo "  ✗ deploy-mu.yml playbook not found"
    fi
else
    echo "  ✗ ansible-mu directory not found"
    echo "  This could cause deployment failures!"
fi
