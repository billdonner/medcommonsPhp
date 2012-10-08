#!/usr/bin/env python

from MySQLdb import connect
from ConfigParser import ConfigParser

from os.path import join, expanduser

def main():
    cfg = ConfigParser({'host': 'mysql.internal', 'user': 'root',
			'password': 'purple44', 'database': 'mcx'})

    cfg.add_section('client')
    cfg.read(join(expanduser('~'), '.mcdb', 'mcx.ini'))

    db = connect(host = cfg.get('client', 'host'),
		 user = cfg.get('client', 'user'),
		 db = cfg.get('client', 'database'),
		 passwd = cfg.get('client', 'password'))

    c = db.cursor()

    c.execute("ALTER TABLE users ADD COLUMN enc_skey CHAR(12);")
    c.execute('SELECT mcid, skey FROM users')

    for mcid, skey in c.fetchall():
	if skey:
	    c.execute("UPDATE users SET enc_skey = %s WHERE mcid = %s",
		      (skey.encode('base64').strip(), mcid))

    c.execute("ALTER TABLE users DROP COLUMN skey;")
    db.commit()

if __name__ == '__main__':
    main()
