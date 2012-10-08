#!/bin/sh

# add mc_admins to /etc/group, like so:
# mc_admins:x:499:adrian,terry,bill,sean,simon

chown -R root:mc_admins /var/www
chmod -R 664 /var/www
find /var/www -type d -exec chmod 775 '{}' ';'
chown apache:mc_admins /var/www/php/local_settings.php
