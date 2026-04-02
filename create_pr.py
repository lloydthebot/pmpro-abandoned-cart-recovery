#!/usr/bin/env python3
import subprocess
import json
import urllib.request

token = subprocess.check_output(['gh', 'auth', 'token']).decode().strip()

pr_body = """## Description

This PR fixes the bug where email template variables are not being replaced correctly when sending a test email (live emails work fine).

## Root Cause

The `get_test_email_constructor_args()` method in all three reminder email template classes was using `global $current_user` to get the current user. This global may not be populated at the time the static method is called via AJAX, causing all user-related template variables (`!!display_name!!`, `!!user_login!!`, `!!user_email!!`, `!!opt_out_url!!`, etc.) to resolve to empty strings.

## Fix

- Use `wp_get_current_user()` instead of `global $current_user` for reliable user retrieval
- Provide a fallback `WP_User` placeholder when no user is available (so test emails always have valid data)
- Return a standalone membership level object instead of attaching it to the user object, matching the constructor signature
- Apply the same fix to all three reminder templates

## How to Test

1. Go to **Memberships > Email Templates** in WordPress admin
2. Choose any of the Abandoned Cart Recovery email templates
3. Click **Save Template and Send Test Email**
4. Verify that all template variables are replaced with dummy/real data in the received test email

Fixes #5

---

**Files Changed:**
- `classes/email-templates/class-pmpro-email-template-pmproacr-reminder-1.php`
- `classes/email-templates/class-pmpro-email-template-pmproacr-reminder-2.php`
- `classes/email-templates/class-pmpro-email-template-pmproacr-reminder-3.php`"""

pr_data = json.dumps({
    "title": "Fix email variables not replaced in test emails",
    "body": pr_body,
    "head": "lloydthebot:dev",
    "base": "dev"
}).encode()

req = urllib.request.Request(
    'https://api.github.com/repos/strangerstudios/pmpro-abandoned-cart-recovery/pulls',
    method='POST',
    headers={
        'Authorization': f'token {token}',
        'Accept': 'application/vnd.github.v3+json',
        'Content-Type': 'application/json'
    },
    data=pr_data
)

try:
    resp = urllib.request.urlopen(req)
    data = json.loads(resp.read())
    print(f'PR CREATED: {data.get("html_url")}')
except urllib.error.HTTPError as e:
    body = json.loads(e.read())
    msg = body.get('message', str(body))
    errors = body.get('errors', [])
    for err in errors:
        print(f'ERROR: {err.get("message", str(err))}')
    print(f'FALLBACK: {msg}')
