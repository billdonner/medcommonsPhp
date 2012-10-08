# We should be dropping the original binary S/Key column,
# and adding the new base64-encoded S/Key column, like so:
#
# ALTER TABLE users
# DROP COLUMN skey
# ADD COLUMN enc_skey CHAR(12);
#
# But then we'd lose data.
#
# Instead, run the 092_update_skey.py script

