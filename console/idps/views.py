#!/usr/bin/env python

from django import newforms as forms

from django.conf import settings
from django.core import serializers
from django.http import HttpResponse, HttpResponseRedirect
from django.shortcuts import render_to_response
from django.views.generic.list_detail import object_list

from config.models import get_property
from users.models import User, normalize_mcid
from idps.models import IdentityProvider
from utils import login_required, submit_redirect, sql_cursor, sql_execute, \
                  default_context

import Image

from urllib2 import urlopen
from csv import reader, writer
from cStringIO import StringIO
from os.path import join

@login_required
def index_req(request):
    context = default_context(request,
                              OpenIDMode = get_property('OpenIDMode'))

    return object_list(request,
                       queryset = IdentityProvider.objects.order_by('name'),
                       template_name = 'idps/index.html',
                       paginate_by = 20,
                       allow_empty = True,
                       extra_context = context)

@login_required
def details_req(request):
    id = request.GET['id'].strip()
    object = IdentityProvider.objects.get(id = id)

    where = ['external_users.mcid = users.mcid',
             'external_users.provider_id = %s' % id]

    qs = User.objects.extra(where = where,
                            select = {'username': 'external_users.username'},
                            tables = ['external_users'])

    return object_list(request,
                       queryset = qs,
                       template_name = 'idps/details.html',
                       paginate_by = 20,
                       allow_empty = True,
                       extra_context = default_context(request, object=object,
                                                       count=qs.count()))

    return render_to_response('idps/details.html',
                              default_context(request, object=object))

class IDPForm(forms.Form):
    source_id = forms.CharField(max_length = 40)
    name = forms.CharField(max_length=32)

    logo = forms.CharField(widget = forms.FileInput(),
                           required = False)

    domain = forms.RegexField(r'^[a-zA-Z0-9\.\-]*$',
                              max_length = 64,
                              required = False)

    logouturl = forms.URLField(max_length = 128,
                               required = False)

    website = forms.URLField(max_length = 64,
                             required = False)

    format = forms.CharField(help_text = 'Format of identity URL, use % as placeholder for username')

    def clean_format(self):
        format = self.data['format']
        if format.count('%') != 1:
            raise forms.ValidationError('Must have one percent (%) as username placeholder')
        return format

@login_required
def add_req(request):
    if request.POST:
        request.POST.update(request.FILES)

        form = IDPForm(request.POST)

        if form.is_valid():

            object = IdentityProvider()
            object.source_id = form.clean_data['source_id']
            object.name = form.clean_data['name']
            object.domain = form.clean_data['domain']
            object.logouturl = form.clean_data['logouturl']
            object.website = form.clean_data['website']
            object.format = form.clean_data['format']

            object.save()

            if 'logo' in request.FILES:
                update_logo(object.id, request.FILES['logo'])

            return submit_redirect(request, object)
    else:
        form = IDPForm()

    return render_to_response('idps/add.html',
                              default_context(request, form=form))

def update_logo(id, file):
    i = Image.open(StringIO(file['content']))
    i.thumbnail((16, 16), Image.ANTIALIAS)

    o = StringIO()
    i.save(o, format='PNG')

    sql_execute("UPDATE identity_providers SET png16x16 = %s WHERE id = %s",
                o.getvalue(), id)

@login_required
def edit_req(request):
    id = request.REQUEST['id'].strip()
    object = IdentityProvider.objects.get(id=id)
    
    if request.POST:
        form = IDPForm(request.POST)

        if form.is_valid():
            object.source_id = form.clean_data['source_id']
            object.name = form.clean_data['name']
            object.domain = form.clean_data['domain']
            object.logouturl = form.clean_data['logouturl']
            object.website = form.clean_data['website']
            object.format = form.clean_data['format']

            object.save()

            if 'logo' in request.FILES:
                update_logo(object.id, request.FILES['logo'])

            return submit_redirect(request, object)
    else:
        form = IDPForm(initial = object.__dict__)

    return render_to_response('idps/edit.html',
                              default_context(request, form=form,
                                              object=object))

@login_required
def logo_req(request):
    id = int(request.GET['id'].strip())

    cursor = sql_cursor()

    sql = "SELECT png16x16 FROM identity_providers WHERE id = %d" % id
    cursor.execute(sql)
    a = cursor.fetchone()

    if a and a[0]:
        s = a[0]
        if not isinstance(s, str):
            s = s.tostring()
        return HttpResponse(s, mimetype='image/png')
    else:
        return HttpResponseRedirect(settings.MEDIA_URL + 'img/openid-icon.png')

@login_required
def delete_req(request):
    id = request.REQUEST['id'].strip()
    object = IdentityProvider.objects.get(id = id)

    where = ['external_users.mcid = users.mcid',
             'external_users.provider_id = %s' % id]

    qs = User.objects.extra(where = where,
                            select = {'username': 'external_users.username'},
                            tables = ['external_users'])

    if request.POST:
        if 'delete' in request.POST:
            sql_execute("DELETE FROM external_users WHERE provider_id = %s", id)
            object.delete()
        return HttpResponseRedirect('.')

    else:
        return render_to_response('idps/delete.html',
                                  default_context(request, object = object,
                                                  count = qs.count()))

@login_required
def unlink_user_req(request):
    return unlink_user(request, "details?id=%(idp)s")

def unlink_user(request, redirect):
    mcid = normalize_mcid(request.POST['mcid'])
    idp = request.POST['idp']
    username = request.POST['username']

    sql_execute("DELETE FROM external_users " + \
                "WHERE provider_id = %s AND mcid = %s AND username = %s",
                int(idp), mcid, username)

    return HttpResponseRedirect(redirect % locals())

# note: login not required!
def download_req(request):
    from base64 import b64encode

    cursor = sql_cursor()
    cursor.execute("SELECT * FROM identity_providers")

    columns = [c[0].lower() for c in cursor.description]
    if 'png16x16' in columns:
        image_row = columns.index('png16x16')
    else:
        image_row = None

    response = HttpResponse(mimetype='text/csv')
    response['Content-Disposition'] = 'attachment; filename=idps.csv'

    w = writer(response)
    w.writerow(columns)

    for row in cursor.fetchall():
        row = list(row)
        if image_row is not None and row[image_row]:
            row[image_row] = b64encode(row[image_row])
        w.writerow(row)

    return response

@login_required
def upload_req(request):
    from base64 import b64decode

    if request.FILES and 'csv' in request.FILES:
        s = request.FILES['csv']['content']

    elif 'url' in request.REQUEST:
        s = urlopen(request.REQUEST['url']).read()

    else:
        return render_to_response('idps/upload.html',
                                  default_context(request))

    r = reader(StringIO(s))
    # okay to remove first column, the 'id' field
    columns = r.next()
    assert columns[0] == 'id'
    if 'png16x16' in columns:
        image_row = columns.index('png16x16')
    else:
        image_row = None

    columns = columns[1:]
    sql = "INSERT INTO identity_providers (" + ', '.join(columns) + \
          ") VALUES (" + ', '.join(['%s'] * len(columns)) + ')'

    for row in r:
        if image_row is not None and row[image_row]:
            row[image_row] = b64decode(row[image_row])
        sql_execute(sql, *row[1:]) 

    return HttpResponseRedirect('.')
 
