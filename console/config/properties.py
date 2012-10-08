#!/usr/bin/env python

from django.conf import settings
import os.path

from django.db import connection, transaction

def get_properties():
    map = {}

    c = connection.cursor()
    c.execute("SELECT property, value FROM mcproperties")

    for property, value in c.fetchall():
        if property.startswith('ac'):
            map[property[2:]] = parse_property(value)

    return map

def get_idps():
    c = connection.cursor()
    c.execute("SELECT source_id, name, display_login FROM identity_providers")

    return [dict(source_id=x[0], name=x[1], display_login=x[2]) for x in c.fetchall()]

def parse_property(str):
    try:
        return int(str)
    except ValueError:
        return str
