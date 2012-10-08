#!/usr/bin/env python

from django.db import models

class ExternalApplication(models.Model):
	ea_id = models.AutoField('ea_id', primary_key = True)
	ea_key = models.CharField(blank = True, maxlength=765)
	ea_code = models.CharField(blank = True, maxlength=90)
	ea_name = models.CharField(blank=True, maxlength=765)
	ea_active_status = models.CharField(blank=True, maxlength=90)
	ea_ip_address = models.CharField(blank=True, maxlength=180)
	ea_create_date_time = models.DateTimeField()
	ea_secret = models.CharField(blank=True, maxlength=120)
	ea_web_site_url = models.CharField(blank=True, maxlength=255)
	ea_contact_email = models.CharField(blank=False, maxlength=255)
	class Meta:
		db_table = 'external_application'

class AuthenticationToken(models.Model):
	at_id = models.AutoField('at_id', primary_key = True)
	at_token = models.CharField(blank=True, maxlength=120)
	at_account_id = models.CharField(blank=True, maxlength=96)
	at_create_date_time = models.DateTimeField()
	at_es_id = models.ForeignKey(ExternalApplication, db_column='at_es_id',
				     null=True, blank=True)
	at_parent_at_id = models.IntegerField(null=True, blank=True)
	at_secret = models.TextField(blank=True)
	class Meta:
		db_table = 'authentication_token'
