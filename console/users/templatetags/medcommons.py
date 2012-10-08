#!/usr/bin/env python

from django import template
from datetime import datetime

register = template.Library()

def mcid(value):
    # preceded by zeroes
    value = ('0000000000000000' + str(value))[-16:]

    # separate by dashes
    return '%s-%s-%s-%s' % (value[0:4], value[4:8], value[8:12], value[12:])

def timestamp(value):
    if value:
        return datetime.fromtimestamp(value).strftime('%Y-%m-%d %T')
    else:
        return ''

register.filter('mcid', mcid)
register.filter('timestamp', timestamp)
