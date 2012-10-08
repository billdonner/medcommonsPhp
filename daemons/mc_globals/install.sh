#!/bin/sh

INSTALL=/usr/bin/install
TARGET=/opt/mc_globals

$INSTALL -d $TARGET
$INSTALL -m 755 -t $TARGET mc_globals.py
$INSTALL -m 644 -t $TARGET mc_globals.rc
$INSTALL -m 755 -t /etc/init.d mc_globals
/usr/sbin/useradd -c "MC global allocator" -M -s /sbin/nologin -d $TARGET mc_globals
