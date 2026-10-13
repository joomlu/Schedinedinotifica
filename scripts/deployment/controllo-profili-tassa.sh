#!/bin/sh
# Processo applicativo dedicato; invocare con identità tanggosoftware e lock C5 condiviso.
set -eu
cd /home/tanggosoftware/repos/schedinedinotifica
[ -f app/Console/Commands/VerificaProfiliTassa.php ] || exit 0
exec /opt/remi/php83/root/usr/bin/php -d opcache.enable_cli=0 artisan tassa:verifica-profili
