#!/usr/bin/env python
#
#vim: tabstop=8 shiftwidth=8 noexpandtab

from MySQLdb import connect
from ConfigParser import ConfigParser

from os.path import join, expanduser

def main():
    #cfg = ConfigParser({'host': 'localhost', 'user': 'root',
    #                    'password': '', 'database': 'mcx'})
    cfg = ConfigParser({'host': 'mysql.internal', 'user': 'root',
                        'password': 'purple44', 'database': 'mcx'})

    cfg.add_section('client')
    cfg.read(join(expanduser('~'), '.mcdb', 'mcx.ini'))

    db = connect(host = cfg.get('client', 'host'),
                 user = cfg.get('client', 'user'),
                 db = cfg.get('client', 'database'),
                 passwd = cfg.get('client', 'password'))

    c = db.cursor()

    c.execute('SELECT tracking_number, encrypted_pin, pin, rights_id, expiration_time FROM tracking_number')

    for tracking_number, encrypted_pin, pin, rights_id, expiration_time in c.fetchall():
        print "Processing row %s" % tracking_number
        if rights_id:
            pin_value = '';
            if(pin):
                    pin_value = pin;
            c.execute("insert into external_share (es_id, es_identity, es_identity_type, es_create_date_time) values (NULL,%s,%s,%s)",
                      (tracking_number + "/" + pin_value, "PIN", expiration_time))
            es_id = db.insert_id();
            print "migrated rights_id %s to es_id %s" % (rights_id, es_id)
            c.execute("update rights set es_id = %s where rights_id = %s",(es_id,rights_id))
            c.execute("update tracking_number set es_id = %s where tracking_number = %s",(es_id, tracking_number))

    c.execute('alter table tracking_number drop column rights_id');
    db.commit()

if __name__ == '__main__':
    main()
