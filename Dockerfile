FROM php:7.4-apache
# VULNERABLE: PHP 7.4 EOL + php2shell (CVE-2026-109618/109619)
# Also: old Debian base, no security updates
RUN apt-get update && apt-get install -y curl nodejs npm && rm -rf /var/lib/apt/lists/*
COPY . /var/www/html/
RUN chmod -R 755 /var/www/html
EXPOSE 80
