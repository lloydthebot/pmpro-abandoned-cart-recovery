#!/usr/bin/env python3
import subprocess
import json
import urllib.request
import os

token = subprocess.check_output(['gh', 'auth', 'token']).decode().strip()

# Fork the repo
req = urllib.request.Request(
    'https://api.github.com/repos/strangerstudios/pmpro-abandoned-cart-recovery/forks',
    method='POST',
    headers={
        'Authorization': f'token {token}',
        'Accept': 'application/vnd.github.v3+json'
    }
)

try:
    resp = urllib.request.urlopen(req)
    data = json.loads(resp.read())
    clone_url = data.get('clone_url')
    full_name = data.get('full_name')
    print(f'FORKED: {full_name}')
    print(f'CLONE_URL: {clone_url}')
except urllib.error.HTTPError as e:
    body = json.loads(e.read())
    msg = body.get('message', str(body))
    if 'already forked' in msg.lower():
        print('ALREADY_FORKED')
    else:
        print(f'ERROR: {msg}')
