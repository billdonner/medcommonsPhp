#!/usr/bin/env python

from socket import getfqdn, gethostname
from tempfile import mkdtemp
from os import close, fdopen, remove, rmdir, pipe, waitpid
from os.path import join, exists
from time import strftime

from django import newforms as forms # change to 'from django import forms'
from django.shortcuts import render_to_response
from django.http import HttpResponse, HttpResponseRedirect
from django.conf import settings
from django.contrib.auth.models import User as Admin
from django.core import serializers

from utils import login_required, default_context
from process import spawn
from config.models import MCProperty, parse_property
from security.models import Certificate
from idps.models import IdentityProvider

from latlon import parse_latitude, parse_longitude

class NameForm(forms.Form):
	Domain = forms.RegexField(r'^[a-zA-Z0-9\.-]+$',
				  help_text = "Fully-Qualified Domain Name of this machine")

	Organization = forms.CharField(help_text = "Legal Name of Organization")

	OrganizationalUnit = forms.CharField(help_text = "Organizational Unit, e.g. department name",
					     required = False)

	ApplianceName = forms.CharField(help_text = "Short name, appears in web pages and emails")

	CommonName = forms.CharField(help_text = "Short name, appears in web pages")

class LocationForm(forms.Form):
	Locality = forms.CharField(help_text = "Locality, City, or County Name")
	State = forms.CharField(help_text = "State or Province")
	Country = forms.RegexField('^[a-zA-Z][a-zA-Z]$', max_length = 2,
				   help_text = "2 Letter Country Code")

	GoogleMapKey = forms.CharField(required = False,
				       help_text = "<a target='_new' href='http://www.google.com/apis/maps/signup.html'>Sign up for a Google Map API Key</a>")

	Latitude = forms.CharField(required = False,
				   help_text = r"""Examples: 22N55'23" and 22 N 55.38' and 22.923 are the same latitude""")
	Longitude = forms.CharField(required = False,
				    help_text = r"""Examples: 120W13'51" and -120 13.85' and -120.2308 are the same longitude""")

	Owner = forms.CharField(max_length = 255,
				widget = forms.Textarea(attrs=dict(rows=5,
								   cols=50)),
				label = 'Address',
				help_text = "Full Postal Address")

	def clean_Latitude(self):
		x = self.data['Latitude']

		if not x: return x

		try:
			return str(parse_latitude(x))
		except ValueError:
			raise forms.ValidationError('Invalid latitude')

	def clean_Longitude(self):
		x = self.data['Longitude']

		if not x: return x

		try:
			return str(parse_longitude(x))
		except ValueError:
			raise forms.ValidationError('Invalid longitude')

class WebForm(forms.Form):
	Site = forms.URLField(help_text = "URL to this appliance")
	HomePage = forms.URLField(help_text = "URL to your company home page")

	ScriptDomain = forms.CharField(required = False)
	CookieDomain = forms.CharField(required = False)

class EmailForm(forms.Form):
	FromName = forms.CharField(initial = 'MedCommons')

	FromEmail = forms.EmailField( \
		initial = 'cmo@medcommons.net',
		help_text = "Emails to users are from this email address")

	IncomingEmailEnabled = forms.ChoiceField(choices = [('0', 'Disabled'),
							    ('1', 'Enabled')],
					       help_text = "Enables incoming email of the form {mcid}@{domain name}")


class StyleForm(forms.Form):
	Logo = forms.CharField(help_text = "Image SRC for home page logo")
	Alt = forms.CharField(max_length=80, \
			      help_text = "Image ALT for home page logo")
	StyleSheet = forms.CharField(initial='',
				     help_text = "URL for home page CSS",
				     required = False)

class SettingsForm(forms.Form):
#	AccountStatus = forms.CharField()
#	PrivacyConfigurationFile = forms.CharField()
#	PrivacyPolicyFile = forms.CharField()
#	PatientBrochureFile = forms.CharField()
#	TemporaryAccountRetentionTime = forms.CharField()
	ApplianceMode = forms.ChoiceField(choices = [('0', 'Development'),
						     ('1', 'Test'),
						     ('2', 'Demo'),
						     ('3', 'Production')],
					  initial = '0')

	OnlineRegistration = forms.ChoiceField(choices = [('0', 'Disabled'),
							  ('1', 'Enabled')],
					       help_text = "Can new users register online")

	OpenIDMode = forms.ChoiceField(choices = [('0', 'Disabled'),
						  ('1', 'Whitelist'),
						  ('2', 'Open')],
				       initial = '0',
				       help_text = 'Which OpenID logins are accepted')

class BackupForm(forms.Form):
    S3Backup = forms.ChoiceField(choices = [('true', 'Enabled'),
                                            ('false', 'Disabled')],
                                 initial = 'false')

    S3Bucket = forms.CharField()

@login_required
def index_req(request):
	return show_properties(request, 'config/index.html')

@login_required
def wiz_publish_req(request):
	return show_properties(request, 'config/wiz_7publish.html')

def show_properties(request, template):
    props = initial_values()

    context = default_context(request)

    context['name_fields'] = field_list(NameForm, props)
    context['location_fields'] = field_list(LocationForm, props)
    context['web_fields'] = field_list(WebForm, props)
    context['email_fields'] = field_list(EmailForm, props)
    context['style_fields'] = field_list(StyleForm, props)
    context['settings_fields'] = field_list(SettingsForm, props)
    context['backup_fields'] = field_list(BackupForm, props)

    # hidden fields
    props.pop('S3Key_ID', None)
    props.pop('S3Secret', None)
    props.pop('GlobalsRoot', None)
    context['misc_fields'] = [dict(key=key, value=value) for key, value in props.items()]

    return render_to_response(template, context)

def field_list(form, properties):
	return [dict(key = field.label or key, value = properties.pop(key, '')) \
		for key, field in form.base_fields.items()]

@login_required
def paths_req(request):
	props = initial_values()
	props.update(default_context(request))

	return render_to_response('config/paths.html', props)

@login_required
def wiz_name_req(request):
	domain = getfqdn()

	return wizard(request, NameForm, 'config/wiz_1name.html',
		      next = 'wiz_location',
		      initial = initial_values(Domain = domain))

@login_required
def set_name_req(request):
	domain = getfqdn()

	return config(request, NameForm, 'config/set_name.html',
		      initial = initial_values(Domain = domain))

@login_required
def wiz_location_req(request):
	return wizard(request, LocationForm, 'config/wiz_2location.html',
		      next = 'wiz_web', prev = 'wiz_name')

@login_required
def set_location_req(request):
	return config(request, LocationForm, 'config/set_location.html')

@login_required
def wiz_web_req(request):
	try:
		prop = MCProperty.objects.get(property='acDomain')
		Domain = prop.value
	except MCProperty.DoesNotExist:
		Domain = getfqdn()

	Site = 'https://' + Domain

	return wizard(request, WebForm, 'config/wiz_3web.html',
		      next = 'wiz_email', prev='wiz_location',
		      initial = initial_values(Site = Site, HomePage = Site))

@login_required
def set_web_req(request):
	return config(request, WebForm, 'config/set_web.html')

@login_required
def wiz_email_req(request):
	return wizard(request, EmailForm, 'config/wiz_4email.html',
		      next = 'wiz_style', prev = 'wiz_web')

@login_required
def set_email_req(request):
	return config(request, EmailForm, 'config/set_email.html')

@login_required
def wiz_style_req(request):
	return wizard(request, StyleForm, 'config/wiz_5style.html',
		      next = 'wiz_settings', prev = 'wiz_email')

@login_required
def set_style_req(request):
	return config(request, StyleForm, 'config/set_style.html')

@login_required
def wiz_settings_req(request):
    return wizard(request, SettingsForm, 'config/wiz_6settings.html',
                  next = 'wiz_publish', prev = 'wiz_style')

@login_required
def set_settings_req(request):
    return config(request, SettingsForm, 'config/set_settings.html')

@login_required
def set_backup_req(request):
    return config(request, BackupForm, 'config/set_backup.html')

def wizard(request, form_type, template, next=None, prev=None, initial=None,
           context=None):
    context = context or dict()

    if request.POST:
        form = form_type(request.POST)

        if form.is_valid():
            save_form_properties(form)

            if next and 'next' in request.POST:
                return HttpResponseRedirect(next)

            elif prev and 'prev' in request.POST:
                return HttpResponseRedirect(prev)
    else:
        form = form_type(initial = initial or initial_values())

    context.update(default_context(request, form=form))

    return render_to_response(template, context)

def config(request, form_type, template, initial = None):
    if request.POST:
        form = form_type(request.POST)

        if form.is_valid():
            save_form_properties(form)

            return HttpResponseRedirect('.')
    else:
        form = form_type(initial = initial or initial_values())

    return render_to_response(template, default_context(request, form=form))

def save_form_properties(form):
    s3backup = None

    for key, value in form.clean_data.items():
        if key == 'ApplianceMode':
            if int(value) == 3:
                save_property('GlobalsRoot', 'http://globals.medcommons.net/')
            else:
                save_property('GlobalsRoot', 'http://globals.myhealthespace.com/')
        elif key == 'S3Key_ID' and value and s3backup is None:
            s3backup = 'true'

        elif key == 'S3Backup':
            s3backup = None

        save_property(key, value)

    if s3backup is not None:
        save_property('S3Backup', s3backup)

def save_property(key, value):
    c = MCProperty()
    c.property = 'ac' + key
    c.value = value
    c.save()

def initial_values(**values):
	for e in MCProperty.objects.all():
		if e.property.startswith('ac'):
			values[e.property[2:]] = parse_property(e.value)

	return values

def preview_req(request):
    return render_to_response('www/ipso.html', initial_values())

@login_required
def backup_req(request):
    """Backup the console configuration: the database tables that
    affect the console, and the customized templates.
    """

    objects = []
    objects.extend(Admin.objects.all())
    objects.extend(Certificate.objects.all())
    objects.extend(MCProperty.objects.all())
    objects.extend(IdentityProvider.objects.all())

    json = serializers.serialize('json', objects)
    td = mkdtemp()
    json_fn = join(td, 'configuration.json')

    bck_name = strftime('console-backup-%Y-%m-%d.tar.bz2')
    bck_path = join(td, bck_name)

    response = HttpResponse(mimetype='application/octet-stream')

    try:
        f = file(json_fn, 'w')
        try:
            f.write(json)
        finally:
            f.close()

        del json

        # Construct a .tar.bz2 file that contains the configuration.json
        # created above, and also contains the console's customize
        # directory, if it exists

        tar_cmd = ['tar', 'cvjf', bck_path]

        customize_dir = join(settings.INSTALL_DIR, 'customize')
        if exists(customize_dir):
            tar_cmd += ['-C', settings.INSTALL_DIR, 'customize']

        # The -C does a chdir, so the current directory changes, and
        # if settings.INSTALL_DIR is '.' it won't work
        #
        tar_cmd += ['-C', td, 'configuration.json']

	pid = spawn(tar_cmd)
	waitpid(pid, 0)

        response['Content-Disposition'] = 'attachment; filename=' + bck_name

        f = file(bck_path)
        try:
            data = f.read()
        finally:
            f.close()
        response.write(data)
        del data

        return response
    finally:
        remove(json_fn)
        remove(bck_path)
        rmdir(td)

@login_required
def restore_req(request):
    errors = ''

    if 'file' in request.FILES:
	p = request.FILES['file']

	td = mkdtemp()

	try:
	    fn = join(td, p.get('filename', 'console-backup.tar.bz2'))

	    f = file(fn, 'w')
	    try:

		try:
		    f.write(p['content'])
		finally:
		    f.close()

		p_err = pipe()
		pid = spawn([join(settings.INSTALL_DIR, 'bin', 'mc-restore-console'),
			     fn], stderr = p_err)
		r_err, w_err = p_err
		close(w_err)
		errors = fdopen(r_err).read()

		pid1, status = waitpid(pid, 0)

		if status == 0:
		    return HttpResponseRedirect('.')

	    finally:
		remove(fn)

	finally:
	    rmdir(td)

    return render_to_response('config/restore.html',
			      default_context(request, errors=errors))

@login_required
def delete_req(request):
    name = 'ac' + request.REQUEST['property']
    property = MCProperty.objects.get(property = name)

    if 'delete' in request.POST or 'delete.x' in request.POST:
	property.delete()

	return HttpResponseRedirect('.')

    return render_to_response('config/delete.html',
			      default_context(request, property=property))
