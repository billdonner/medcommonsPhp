#!/usr/bin/env python

from django.db import models

class IdentityProvider(models.Model):
    source_id = models.CharField(maxlength = 40,
                                 db_index = True)

    name = models.CharField(maxlength = 80)

    domain = models.CharField(maxlength = 64,
                              null = True)

    logouturl = models.URLField(maxlength = 128,
                                null = True)

    website = models.URLField(maxlength = 64,
                              null = True)

    format = models.CharField(maxlength = 64,
			      null = True)

    class Meta:
        db_table = 'identity_providers'
