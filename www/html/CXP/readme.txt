Readme.txt for CXP Prototype from MedCommons


You can choose to install CXP for yourself, or to try the CXP already set up by MedCommons.

To use the pre-installed MedCommons setup, goto http://virtual03.medcommons.net/cxp and follow the instructions there.

Instructions for installing the CXP Prototype from MedCommons:

Step1) copy the zip file from http://virtual03.medcommons.net/cxp to a directory on your disk and then expand the zip under .../cxp

Step2) if you don't want to run CXPServer, and only care about CXPCMD, go to Step 3

Prepare your server for CXPServer

	your server must have a public IP address and optionally a DNS name

	your server must have PHP5 installed as part of Apache

The location of the /cxp directory on the server is arbitrary, but you putting it at the top level makes it easier to follow the various examples and test cases

There is nothing to start - the CXPServer is run automatically as necessary by PHP

Step3) if you don't want to run CXPCMD go to Step 4

Otherwise, you will need to build some xml files to feed to CXPCMD:

	you will need to edit the IP address or DNS name of the CXPServer into the xml files

	you may want to change the CCRs and attachments that CXPCMD is sending

	you may want to setup a notification URL to receive responses to Queries and other status changes

Step4) you should observe the contents of /serverdata, /clientdata, and /notificationdata

You may want to remove files from here periodically as they will eventually fill your hard disk.

Please see separate files on CXPCMD input file formats and use of the utility
