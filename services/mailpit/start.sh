docker run -d --restart unless-stopped \
--name=mailpit \
-p 8025:8025 \
-p 1025:1025 \
-v /var/www/halemba.rocks/livewire-planning-poker/services/mailpit/data:/data \
-e MP_UI_AUTH_FILE=/data/mailpit.passwd \
-e MP_UI_TLS_KEY=/data/privkey.pem \
-e MP_UI_TLS_CERT=/data/cert.pem \
axllent/mailpit
