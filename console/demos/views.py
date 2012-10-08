#!/usr/bin/env python
# demodata.py

from urlparse import urljoin

import SOAPpy

from config.properties import get_properties
from config.models import MCProperty
from users.models import User
from groups.models import Group, Practice, Practiceccrevent, Groupadmin, \
                          delete_group

from utils import sql_execute, default_context

from django.db import transaction
from django.http import HttpResponseRedirect
from django.shortcuts import render_to_response


#	     First name	Last name    Email
#	     ----------	---------    -----
#		Current CCR
#		Reason
#		[CCR List]
PATIENTS = [('Jane',   'Hernandez', 'jhernandez@medcommons.net',
		"3ea6d35fa4c9d81352b0b3266562e58b878aa6e1",
		'3D Imaging Consult', []),

	    ('Jim',    'Jones',     'jjones@medcommons.net', None, '', []),

	    ('Jane',   'Bewell',    'jbewell@medcommons.net',
		"83244f558bbd74021fe9b13da9cad52840fc3ace",
		'Update for Diabetes Checkup',
		["f198a83583d6a6428e74acd34592efaff7c59abd"]),

	    ('Stella', 'Paterson',  'spaterson@medcommons.net',
		"d5af8707f5ab103a6f31c3a48e44f9dc89c5bb26",
		'For the patient', []),
]

ACCOUNT_RLS_SQL = """
INSERT INTO account_rls (ar_accid, ar_rls_url)
VALUES (%s, %s)
"""

DOCUMENT_TYPE_SQL = """
INSERT INTO document_type (dt_id, dt_account_id, dt_type, dt_tracking_number,
			   dt_privacy_level, dt_guid, dt_create_date_time,
			   dt_comment)
VALUES (NULL, %s, 'CURRENTCCR', '', 'Private', %s, CURRENT_TIMESTAMP,
	'Demo Current CCR')
"""
		
CCRLOG_SQL = """
INSERT INTO ccrlog (id, accid, idp, guid, status, date, src, dest,
		    subject, einfo, tracking, merge_status)
VALUES (NULL, %s, 'idp', %s, 'Complete', {ts '2005-11-01 18:24:18.000'},
	'UNKNOWN', '', %s, NULL, %s, NULL)
"""

TODIR_SQL = """
INSERT INTO todir (groupid, xid, alias, contactlist, sharedgroup,
		   pinstate, accid)
VALUES (%s, '', %s, %s, 0, 0, %s)
"""

URL = 'http://mcid.internal:1080/mcid'
NS = 'http://www.medcommons.net/locals'

mcid_generator = SOAPpy.SOAPProxy('http://mcid.internal:1080/mcid',
				  namespace = NS)

tn_generator = SOAPpy.SOAPProxy('http://mcid.internal:1080/tracking_number',
				namespace = NS)

def index_req(request):
	properties = get_properties()

	context = default_context(request)

	if 'DemoDoctor' in properties:
		patients = []
		for first_name, last_name, email, ccr, reason, ccrs in PATIENTS:
			patients += User.objects.filter(first_name = first_name,
							last_name = last_name,
							email = email)

		context['mcid'] = properties['DemoDoctor']
		context['doctor'] = User.objects.get(mcid = context['mcid'])
		context['patients'] = patients

	return render_to_response('demos/index.html', context)

def create_req(request):
	if 'create' in request.POST:
		properties = get_properties()
		if 'Site' in properties:
			url_root = properties['Site']
		else:
			url_root = request.META['SERVER_NAME']

		create_data(url_root)

	return HttpResponseRedirect('.')

def delete_req(request):
	if 'delete' in request.POST:
		delete_data()

	return HttpResponseRedirect('.')

def create_data(url_root):
	gateway = urljoin(url_root, 'router/')

	transaction.enter_transaction_management()

	doctor = User()
	doctor.mcid = mcid_generator.next_mcid()
	doctor.first_name = 'Demo'
	doctor.last_name = 'Doctor'
	doctor.email = 'demodoctor@medcommons.net'
	doctor.updatetime = 0
	doctor.ccrlogupdatetime = 0
	doctor.save()

	physician = User()
	physician.mcid = mcid_generator.next_mcid()
	physician.first_name = 'Demo'
	physician.last_name = 'Physician'
	physician.email = 'demophysician@medcommons.net'
	physician.updatetime = 0
	physician.ccrlogupdatetime = 0
	physician.save()

	g, p = create_group('Demo Group Worklist',
			    'group-demodoctor@medcommons.net', url_root,
			    doctor.mcid)

	add_to_group(g, physician.mcid)

	patients = []
	for first_name, last_name, email, currentccr, reason, ccrs in PATIENTS:
		user = User()
		user.first_name = first_name
		user.last_name = last_name
		user.email = email
		user.mcid = mcid_generator.next_mcid()
		user.acctype = 'USER'
		user.rolehack = 'ccrlhm'
		user.updatetime = 0
		user.ccrlogupdatetime = 0
		user.save()

		patients.append(user)

		# Set worklist
		sql_execute(ACCOUNT_RLS_SQL, user.mcid, p.practiceRlsUrl)

		if not currentccr:
			continue

		sql_execute(DOCUMENT_TYPE_SQL, user.mcid, currentccr)

		ev = Practiceccrevent()
		ev.practiceid = p
		ev.PatientGivenName = user.first_name
		ev.PatientFamilyName = user.last_name
		ev.PatientIdentifier = user.mcid
		ev.PatientIdentifierSource = 'Patient Medcommons ID'
		ev.Guid = currentccr
		ev.Purpose = reason
		ev.SenderProviderId = 'idp'
		ev.ReceiverProviderId = 'idp'
		ev.DOB = '16 Jan 1968 05:00:00 GMT'
		ev.CXPServerURL = ''
		ev.CXPServerVendor = 'Medcommons'
		ev.ViewerURL = urljoin(gateway, 'access?g=%s' % currentccr)
		ev.Comment = '\n            3D Imaging Consult\n            '
		ev.CreationDateTime = 1162365858
		ev.ConfirmationCode = tn_generator.next_tracking_number()
		ev.RegistrySecret = ''
		ev.PatientSex = 'Female'
		ev.PatientAge = ''
		ev.Status = 'New'
		ev.ViewStatus = 'Visible'
		ev.save()

		sql_execute(CCRLOG_SQL, user.mcid, currentccr, 'CCR',
			    ev.ConfirmationCode)

		for ccr in ccrs:
			sql_execute(CCRLOG_SQL, user.mcid, ccr, 'CCR',
				    tn_generator.next_tracking_number())

	sql_execute(TODIR_SQL, g.groupinstanceid, doctor.email,
		    doctor.email, doctor.mcid)

	sql_execute(TODIR_SQL, g.groupinstanceid, physician.email,
		    physician.email, physician.mcid)

	demoCCR = 'fdfbbb9cf53f8577b420ed72567cd2104589fb0d'

	sql_execute(CCRLOG_SQL, doctor.mcid, demoCCR, 'DICOM Import',
		    tn_generator.next_tracking_number())

	sql_execute(CCRLOG_SQL, patients[0].mcid, demoCCR, 'DICOM Import',
		    tn_generator.next_tracking_number())

	sql_execute(CCRLOG_SQL, patients[0].mcid, PATIENTS[0][3],
		    'DICOM Import',
		    tn_generator.next_tracking_number())


	# Secondary group
	if 0:
		g2, p2 = create_group('Healthy Doctors',
				      'group2-demodoctor@medcommons.net',
				      url_root, doctor.mcid)

	p = MCProperty()
	p.property = 'acDemoDoctor'
	p.value = doctor.mcid
	p.save()

	transaction.leave_transaction_management()

def delete_data():
	properties = get_properties()

	delete_user(properties['DemoDoctor'])

	for first_name, last_name, email, ccr, reason, ccrs in PATIENTS:
		users = User.objects.filter(first_name = first_name,
					    last_name = last_name,
					    email = email)

		for user in users:
			delete_user(user.mcid)

	sql_execute("DELETE FROM mcproperties WHERE property='acDemoDoctor'")

def delete_user(mcid):
	for ga in Groupadmin.objects.filter(adminaccid = mcid):
		g = Group.objects.get(groupinstanceid = ga.groupinstanceid)

		delete_group(g)
		ga.delete()

	sql_execute("DELETE FROM ccrlog WHERE accid = %s", mcid)
	sql_execute("DELETE FROM todir WHERE accid = %s", mcid)
	sql_execute("DELETE FROM groupmembers WHERE memberaccid = %s", mcid)
	sql_execute("DELETE FROM document_type WHERE dt_account_id = %s", mcid)
	sql_execute("DELETE FROM users WHERE mcid = %s", mcid)

def create_group(name, email, url_root, owner):
	"""
	_name_		Name of group
	_email_		Email address for group (?)
	_url_root_	acSite
	_owner_		MCID of user/doctor that controls this group
	"""
	u = User()
	u.mcid = mcid_generator.next_mcid()
	u.email = email
	u.set_password(str(u.mcid))
	u.first_name = name
	u.last_name = 'Group'
	u.rolehack = 'rls'
	u.acctype = 'GROUP'
	u.updatetime = 0
	u.ccrlogupdatetime = 0
	u.save()

	g = Group()
	g.grouptypeid = 0
	g.name = name
	g.accid_id = u.mcid
	g.save()
	
	p = Practice()
	p.providergroupid = g
	p.practicename = name
	p.accid_id = u.mcid
	p.save()

	p.practiceRlsUrl = urljoin(url_root,
				   'acct/ws/R.php?pid=%d' % p.practiceid)
	p.save()

	g.parentid = p.practiceid
	g.save()

	ga = Groupadmin()
	ga.groupinstanceid = g.groupinstanceid
	ga.adminaccid = owner
	ga.save()

	add_to_group(g, owner)

	return g, p

def add_to_group(g, mcid):
	sql_execute("""INSERT INTO groupmembers (groupinstanceid, memberaccid)
			VALUES (%s, %s)""", g.groupinstanceid, mcid)

#
#  $rightsResult = file_get_contents($GLOBALS['Commons_Url']."/demodata.php");
#  if($rightsResult==false) {
#    err("Unable to grant rights for $janesEmail to access current ccr $demoCcrGuid");
#  }
#
#  $gwResetURL = gpath('Default_Repository')."/ResetDemoData.action";
#  $gwResult = file_get_contents($gwResetURL);
#  if($gwResult==false) {
#    err("Unable to reset demo data on gateawy using ".$gwResetURL);
#  }
