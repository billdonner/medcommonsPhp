#!/usr/bin/env python

from django.db import models
from django.db.models import Q

import re
from sha import sha

# 4 groups of 4 digits, optionally separated by space, tab, period, or dash
VALID_MCID = '^[0-9]{4}([\. \t-]?[0-9]{4}){3}$'

delimiters = re.compile('[\. \t-]')
valid_mcid = re.compile(VALID_MCID)

def all_query_set(order_by = ('-since',)):
    return User.objects.exclude(acctype='DISABLED').order_by(*order_by)

def search_query_set(qstr, order_by=('-since',)):
    qstr = qstr.strip()
    qs = User.objects

    for q in qstr.split():
        if '@' in q:
            qs = qs.filter(email=q)

        elif is_mcid(q):
            qs = qs.filter(mcid=normalize_mcid(q))

        else:
            qs = qs.filter(Q(first_name=q) | Q(last_name=q))

    return qs.order_by(*order_by)

ACCTYPE_CHOICES = [
	('USER',	'User'),
	('GROUP',	'Group'),
	('SPONSORED',	'Sponsored'),
	('CLAIMED',	'Claimed'),
	('UNVALIDATED',	'Unvalidated'),
	('TEMPORARY',	'Temporary'),
	('EXPIRED',	'Expired'),
        ('DISABLED',    'Disabled')]

class User(models.Model):
    mcid = models.CharField(maxlength = 16,
                            primary_key=True)

    email = models.CharField(null=True, blank=True, maxlength=192,
			     db_index=True, unique=True)

    sha1 = models.CharField(null=True, blank=True, maxlength=120)

    since = models.DateTimeField(auto_now_add=True)
    first_name = models.CharField(blank=True, maxlength=96)
    last_name = models.CharField(blank=True, maxlength=96)
    mobile = models.CharField(blank=True, maxlength=192)
    smslogin = models.IntegerField(null=True, blank=True)
    updatetime = models.IntegerField()
    ccrlogupdatetime = models.IntegerField()
    chargeclass = models.CharField(blank=True, maxlength=765)
    rolehack = models.CharField(blank=True, maxlength=765)
    affiliationgroupid = models.IntegerField(null=True, blank=True)
    startparams = models.CharField(blank=True, maxlength=765)
    stylesheetUrl = models.CharField(blank=True, maxlength=765)
    picslayout = models.CharField(blank=True, maxlength=765)
    photoUrl = models.CharField(blank=True, maxlength=765)
    acctype = models.CharField(choices = ACCTYPE_CHOICES,
			       blank = True, maxlength = 255,
			       default = 'USER')

    def claimable(self):
        return self.acctype in ['DISABLED', 'SPONSORED', 'CLAIMED']

    persona = models.CharField(blank=True, maxlength=765)
    validparams = models.CharField(blank=True, maxlength=765)
    interests = models.CharField(blank=True, maxlength=765)
    email_verified = models.DateTimeField(null=True, blank=True)
    mobile_verified = models.DateTimeField(null=True, blank=True)

    # binary data
    enc_skey = models.CharField(blank=True, maxlength=12)

    enable_simtrak = models.BooleanField()
    enable_dod = models.BooleanField()

    def set_password(self, pw):
        sha1 = sha('medcommons.net')
        sha1.update(str(self.mcid))
        sha1.update(pw)
        self.sha1 = sha1.hexdigest().upper()

    def pretty_mcid(self):
        return pretty_mcid(self.mcid)

    class Meta:
        db_table = 'users'

    class Admin:
        pass

def normalize_mcid(mcid):
    """Remove common punctuation from a user-entered MCID.

    Examples:
    >>> normalize_mcid('0123456789012345')
    '0123456789012345'

    >>> normalize_mcid('0123 4567 8901 2345')
    '0123456789012345'

    >>> normalize_mcid('0123-4567-8901-2345')
    '0123456789012345'
    """
    if isinstance(mcid, User):
        mcid = mcid.mcid
    else:
        mcid = ''.join(delimiters.split(str(mcid)))

    return ('0000000000000000' + mcid)[-16:]

def pretty_mcid(mcid):
    """
    pre: len(mcid) == 16
    post: ''.join(__return__.split('-')) == mcid
    """
    mcid = normalize_mcid(mcid)
    return "%s-%s-%s-%s" % (mcid[0:4], mcid[4:8], mcid[8:12], mcid[12:16])

def is_mcid(mcid):
    """Tests if an MCID is a valid 16-digit number.

    >>> is_mcid('test')
    False

    >>> is_mcid('012345')
    False

    >>> is_mcid('this is 16 chars')
    False

    >>> is_mcid('0123456789012345')
    True

    >>> is_mcid('0123-4567-8901-2345')
    True
    """
    return bool(valid_mcid.match(mcid))

