#!/usr/bin/env python

import os, random, stat, sys

table = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_'
pword = ''
for i in range(32):
    pword += table[random.randint(0, len(table) - 1)]

spword = """\
sed -i -e 's|^127\.0\.0\.1[ \t].*|127.0.0.1 localhost.localdomain localhost mysql.internal mcid.internal|' /etc/hosts
/sbin/chkconfig mysqld on
/etc/init.d/mysqld start
mysqladmin -u root password %(pword)s
"""

os.system(spword % {'pword':pword})

ini = """\
[client]
host=mysql.internal
user=root
password=%(pword)s
"""

os.mkdir('/root/.mcdb', 0700)
fd = open('/root/.mcdb/mcx.ini', 'w')
fd.write(ini % {'pword':pword})
fd.close()
os.chmod('/root/.mcdb/mcx.ini', stat.S_IRUSR | stat.S_IWUSR)

bashp = """\
alias mysql="mysql --defaults-extra-file=~/.mcdb/mcx.ini"
alias mysqladmin="mysqladmin --defaults-extra-file=~/.mcdb/mcx.ini"
"""

fd = open('/root/.bash_profile', 'a')
fd.write(bashp % {'pword':pword})
fd.close()
os.chmod('/root/.bash_profile', stat.S_IRUSR | stat.S_IWUSR)
