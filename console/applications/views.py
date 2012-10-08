#!/usr/bin/env python

from django import newforms as forms 
from django.http import HttpResponseRedirect
from django.views.generic.list_detail import object_list
from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect

from applications.models import ExternalApplication
from applications.models import AuthenticationToken

from users.models import User, VALID_MCID, pretty_mcid, normalize_mcid, \
                         search_query_set, all_query_set

from utils import login_required, sql_execute, submit_redirect, default_context

import sha
import random

class AppRegistrationForm(forms.Form):
  name = forms.CharField(max_length=255)
  code = forms.CharField(max_length=30)
  email = forms.CharField(max_length=255)
  website_url = forms.CharField(max_length=255)

@login_required
def index_req(request):
    return object_list(request,
                       queryset = ExternalApplication.objects.order_by('ea_name'),
                       template_name = 'applications/index.html',
                       paginate_by = 20,
                       allow_empty = True,
                       extra_context = default_context(request))

@login_required
def register(request):
    return render_to_response('applications/register.html',
			      default_context(request,
					      form=AppRegistrationForm()))

@login_required
def register_application_req(request):

    form = AppRegistrationForm(request.POST)
    
    if not form.is_valid():
      return render_to_response('applications/register.html',
				default_context(request, form=form))

    ip = request.META['REMOTE_ADDR']
    code = request.POST['code']
    name = request.POST['name']
    email = request.POST['email']
    url = request.POST['website_url']

    if 'save' in request.POST and request.POST['save'] == 'Save':
      id = request.POST['id']
      print "Updating ea " + id
      ea = ExternalApplication.objects.get(ea_id=id)
      ea.ea_name = name
      ea.ea_code = code
      ea.ea_contact_email = email
      ea.ea_web_site_url = url
    else:
      # Compute a random key
      key = sha.new(str(random.randint(0,1000000))+ip+code+name).hexdigest()
      secret = sha.new(str(random.randint(0,1000000))+code+ip+name).hexdigest()
      ea = ExternalApplication(ea_name=name,ea_code=code,ea_key=key,ea_ip_address=ip,ea_active_status='Pending',ea_secret=secret, ea_contact_email=email, ea_web_site_url=url)

      # add authentication token for this application
      at = AuthenticationToken(at_account_id=None,at_token=key,at_secret=secret)
      at.save()

    ea.save()
    return HttpResponseRedirect('.')


@login_required
def delete_application_req(request):
    id = request.GET['id']
    ea = ExternalApplication.objects.get(ea_id=id)
    print "found ea " + ea.ea_name
    ea.delete()
    return HttpResponseRedirect('/applications/')

@login_required
def edit_application_req(request):
    id = request.GET['id']
    ea = ExternalApplication.objects.get(ea_id=id)
    form = AppRegistrationForm({'name':ea.ea_name, 'code':ea.ea_code, 'email':ea.ea_contact_email, 'website_url':ea.ea_web_site_url})
    return render_to_response('applications/register.html',
			      default_context(request, form=form, ea=ea,
					      existing=True))

