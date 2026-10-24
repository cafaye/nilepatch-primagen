FROM php:7.4-apache
# Mitigations for php2shell (CVE-2026-109618/109619) on EOL PHP 7.4
RUN { \
      echo "session.upload_progress.enabled = Off"; \
      echo "session.upload_progress.cleanup = On"; \
      echo "file_uploads = Off"; \
      echo "memory_limit = -1"; \
    } > /usr/local/etc/php/conf.d/99-mitigations.ini
RUN apt-get update && apt-get install -y --no-install-recommends curl nodejs npm && rm -rf /var/lib/apt/lists/*
COPY . /var/www/html/
RUN chmod -R 755 /var/www/html
EXPOSE 80
