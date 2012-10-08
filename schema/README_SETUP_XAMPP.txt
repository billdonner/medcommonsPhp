If you're setting up a machine under Cygwin you can have some problems because of annoying MySQL / Cygwin issues.

Cygwin mysql wants to use domain sockets, but of course, windows will have none
of that.  So, you need to convince it to try and access the server remotely:

  mysql -u root -h <hostname> mcx

This may require new options to be set in mysql privileges, eg:

grant all privileges on *.* to 'root'@'192.168.0.0/255.255.0.0';
grant all privileges on *.* to 'root'@'<your hostname';


Then you should be able to run create.sh unders Cygwin to create your tables.
