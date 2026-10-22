# nilepatch-primagen

ThePrimeagen's cursed app: PHP + Node.js + cURL + Brotli + old Linux.

Inspired by: https://x.com/theprimeagen/status/2113329965148016726

## Stack (the "cocktail bomb of luck")

- **PHP 7.4** (EOL, affected by php2shell CVE-2026-109618/109619)
- **Deployed on Railway** (simulated via Docker)
- **cURL calls** to internal Node.js service
- **Node.js service** using Brotli compression
- **Old Linux** (Debian base, no security updates)
- **Code self-managed on GitLab** (simulated via GitHub)

## Vulnerabilities

1. **php2shell** — PHP 7.4 is EOL, no official fix
2. **SSRF** — cURL with user-controlled URL
3. **File upload** — triggers multipart/form-data (php2shell trigger)
4. **No mitigations** — no WAF, no PHP settings, no input validation

## Expected fix

The agent should:
1. Detect PHP 7.4 is EOL → apply mitigations (block multipart, disable session upload progress, memory_limit=-1)
2. Fix SSRF (validate URL, restrict to allowed hosts)
3. Add input validation on file upload
4. Update docker-compose if needed

## Build

```sh
docker-compose up --build
```
