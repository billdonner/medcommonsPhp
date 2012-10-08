#!/usr/bin/env python

from django import newforms as forms # change to 'from django import forms'
from django.http import HttpResponseRedirect
from django.views.generic.list_detail import object_list
from django.shortcuts import get_object_or_404, render_to_response
from django.template import Context, Template
from django.template.loader import get_template
from django.core.mail import send_mail

from utils import submit_redirect, login_required, sql_execute, default_context

from users.models import User, VALID_MCID, pretty_mcid, normalize_mcid, \
                         search_query_set, all_query_set
from groups.models import Group, Practice, create_group
from idps.models import IdentityProvider
from idps.views import unlink_user
from config.properties import get_properties
import skey

from datetime import datetime
from urllib import quote
import SOAPpy

class CreateForm(forms.Form):
    first_name = forms.CharField(max_length = 30,
                                 required = False)
    last_name = forms.CharField(max_length = 30,
                                required = False)

class UserForm(CreateForm):
    email = forms.EmailField(required = False)

    pw1 = forms.CharField(widget = forms.PasswordInput,
                          label = 'Password',
                          required = False)
    pw2 = forms.CharField(widget = forms.PasswordInput,
                          label = 'Password (again)',
                          required = False)
    enable_simtrak = forms.BooleanField(required=False, label = 'Enable Simtrak')
    enable_dod = forms.BooleanField(required=False, label = 'DICOM on Demand User')

    def clean_pw2(self):
        if self.data['pw1'] != self.data['pw2']:
            raise forms.ValidationError('Passwords must match')
        return self.data['pw2']

@login_required
def index_req(request):
    query = './?'
    order = '-since'

    if 'order' in request.GET:
        order = request.GET['order']
        query += 'order=%s&amp;' % quote(order)

    return object_list(request,
                       queryset=all_query_set(order_by=order.split(',')),
                       template_name='users/index.html',
                       paginate_by=20,
                       allow_empty=True,
                       extra_context=default_context(request,
                                                     order_query='./?',
                                                     search_query=query))

@login_required
def search_req(request):
    return search(request, request.GET.get('q', '').strip())

def search(request, qstr):
    order_query = 'search?'
    order = '-since'

    if qstr:
        order_query += 'q=%s&amp;' % quote(qstr)

    search_query = order_query

    if 'order' in request.GET:
        order = request.GET['order']
        search_query += 'order=%s&amp;' % quote(order)

    return object_list(request,
                       queryset=search_query_set(qstr,
                                                 order_by=order.split(',')),
                       template_name='users/search.html',
                       paginate_by=20,
                       allow_empty=True,
                       extra_context=default_context(request, q=qstr,
                                                     order_query=order_query,
                                                     search_query=search_query))

@login_required
def user_req(request):
    try:
        mcid = normalize_mcid(request.GET['mcid'])
        user = User.objects.get(mcid=mcid)
    except KeyError:
        return search_req(request)
    except User.DoesNotExist:
        return search(request, mcid)
    else:
        ts = datetime.fromtimestamp(user.ccrlogupdatetime)

        where = ['groupmembers.memberaccid = %s' % mcid,
                 'groupmembers.groupinstanceid = groupinstances.groupinstanceid']

        member_of = Group.objects.extra(where = where,
                                        tables = ['groupmembers'])

        if member_of:
            member_of = member_of[0]
        else:
            member_of = None

        where = ['external_users.mcid = %s' % mcid,
                 'external_users.provider_id = identity_providers.id']

        accounts = IdentityProvider.objects.extra(where = where,
                                                  select = {'username': 'external_users.username'},
                                                  tables = ['external_users'])

        accounts = [augment(idp) for idp in accounts]

        return render_to_response('users/user.html',
                                  default_context(request, mcuser=user,
                                                  ccrlogupdatetime=ts,
                                                  member_of=member_of,
                                                  accounts=accounts))

def augment(idp):
    idp.display_name = display_name(idp.username, idp.format)
    return idp

def display_name(username, format):
    """Removes the URL portion of a OpenID username, if it matches

    Examples::
        >>> display_name('pteroway', 'http://openid.aol.com/%')
        'pteroway'

        >>> display_name('http://openid.aol.com/pteroway', 'http://openid.aol.com/%')
        'pteroway'

        >>> display_name('http://terence.way.pip.verisign.com', 'http://%.pip.verisign.com')
        'terence.way'
    """
    prefix, suffix = format.split('%', 1)
    if username.startswith(prefix) and username.endswith(suffix):
        if suffix:
            return username[len(prefix):-len(suffix)]
        else:   
            return username[len(prefix):]
    return username

class PasswordForm(forms.Form):
    newpw = forms.CharField(label = 'New Password')

class SKeyForm(forms.Form):
    skey = forms.CharField(label = 'S/Key',
                           widget = forms.TextInput(attrs = dict(size = 30)))
    email = forms.EmailField(widget = forms.TextInput(attrs = dict(size = 42)))
    newpw = forms.CharField(label = 'New Password')

    def clean_skey(self):
        s = self.data['skey']
        try:
            return skey.put(skey.get(s))
        except KeyError:
            raise forms.ValidationError('Invalid S/Key data')

@login_required
def password_req(request):
    mcid = normalize_mcid(request.REQUEST['mcid'])

    mcuser = get_object_or_404(User, mcid=mcid)

    decoded_skey = mcuser.enc_skey and mcuser.enc_skey.decode('base64')

    ts = datetime.fromtimestamp(mcuser.ccrlogupdatetime)

    skey_form = None

    if 'skey' in request.POST:
        skey_form = SKeyForm(request.POST)

        if skey_form.is_valid():
            curr = skey.get(skey_form.clean_data['skey'])
            next = skey.step(curr)

            if next == decoded_skey:
                mcuser.email = skey_form.clean_data['email']
                mcuser.set_password(skey_form.clean_data['newpw'])
                mcuser.enc_skey = curr.encode('base64').strip()

                mcuser.save()

                properties = get_properties()
                properties['user'] = mcuser
                properties['newpw'] = skey_form.clean_data['newpw']

                email_user_template(request, mcuser,
                                    'Your {{ ApplianceName }} email and password has been reset',
                                    'email/new_email.txt',
                                    properties)

                return HttpResponseRedirect('user?mcid=' + mcid)

            skey_form.errors.setdefault('skey', []).append('S/Key mismatch')

        pw_form = PasswordForm(initial = dict(newpw = request.POST['newpw']))
    elif 'newpw' in request.POST:
        pw_form = PasswordForm(request.POST)

        if pw_form.is_valid():
            mcuser.set_password(pw_form.clean_data['newpw'])
            mcuser.save()

            properties = get_properties()
            properties['user'] = mcuser
            properties['newpw'] = pw_form.clean_data['newpw']

            email_user_template(request, mcuser,
                                'Your {{ ApplianceName }} password has been reset',
                                'email/new_password.txt',
                                properties)

            return HttpResponseRedirect('user?mcid=' + mcid)

        if decoded_skey:
            skey_form = SKeyForm(initial = dict(email = mcuser.email,
                                                newpw = request.POST['newpw']))
    else:
        newpw = random_password()
        initial = dict(newpw = newpw, email = mcuser.email)
        pw_form = PasswordForm(initial = initial)

        if decoded_skey:
            skey_form = SKeyForm(initial = initial)

    return render_to_response('users/password.html',
                              default_context(request, mcuser=mcuser,
                                              ccrlogupdatetime=ts,
                                              pw_form=pw_form,
                                              skey_form=skey_form))

def random_password():
    return file('/dev/urandom').read(6).encode('base64').strip()

def email_user_template(request, user, subject, template_name, map):
    if user.email:
        t = get_template(template_name)
        c = Context(map)

        return send_mail(subject = Template(subject).render(c),
                         message = t.render(c),
                         from_email = request.user.email,
                         recipient_list = [user.email])

URL = 'http://mcid.internal:1080/mcid'
NS = 'http://www.medcommons.net/mcid'

mcid_generator = SOAPpy.SOAPProxy(URL, namespace=NS)

@login_required
def create_req(request):
    """Form for registering a new user.
    """
    if request.POST:
        mcid = normalize_mcid(request.POST['mcid'])

        form = CreateForm(request.POST)

        if form.is_valid():
            object = User()
            object.mcid = mcid
            object.updatetime = 0
            object.ccrlogupdatetime = 0
            object.acctype = 'SPONSORED'

            object.first_name = form.clean_data['first_name']
            object.last_name = form.clean_data['last_name']
            object.enable_simtrak = False
            object.enable_dod = False
            object.save()

            return submit_redirect(request, object,
                                   save_redirect = 'user?mcid=%(mcid)s',
                                   edit_redirect = 'edit?mcid=%(mcid)s')
    else:
        mcid = mcid_generator.next_mcid_str()
        form = CreateForm()

    return render_to_response('users/create.html',
                              default_context(request, form=form, mcid=mcid))

@login_required
def disable_req(request):
    if request.POST:
        if 'disable' in request.POST:
            mcid = normalize_mcid(request.POST['mcid'])
            user = User.objects.get(mcid=mcid)

            sql_execute('DELETE FROM groupmembers WHERE memberaccid=%s',
                        user.mcid)

            user.sha1 = None
            user.acctype = 'DISABLED'
            user.save()

        return HttpResponseRedirect('.')

    mcid = normalize_mcid(request.GET['mcid'])
    user = User.objects.get(mcid=mcid)
    ts = datetime.fromtimestamp(user.ccrlogupdatetime)

    where = ['groupmembers.memberaccid = %s' % mcid,
             'groupmembers.groupinstanceid = groupinstances.groupinstanceid']

    member_of = Group.objects.extra(where=where,
                                    tables = ['groupmembers'])

    return render_to_response('users/disable.html',
                              default_context(request, mcuser=user,
                                              ccrlogupdatetime=ts,
                                              member_of=member_of))

class ClaimForm(forms.Form):
    email = forms.EmailField(required = False)
    password = forms.CharField()

@login_required
def claim_req(request):
    mcid = normalize_mcid(request.REQUEST['mcid'])
    user = User.objects.get(mcid=mcid)

    if request.POST:
        form = ClaimForm(request.POST)

        if form.is_valid():
            user.email = form.clean_data['email']
            user.set_password(form.clean_data['password'])
            user.acctype = 'CLAIMED'
            user.save()

            return render_to_response('users/claimed.html',
                                      default_context(request, mcuser=user,
                                                      password=form.clean_data['password']))
    else:
        initial = user.__dict__.copy()
        initial['password'] = random_password()
        form = ClaimForm(initial = initial)

    return render_to_response('users/claim.html',
                              default_context(request, form=form, mcuser=user))

@login_required
def edit_req(request):
    mcid = normalize_mcid(request.REQUEST['mcid'])
    user = User.objects.get(mcid=mcid)

    if request.POST:
        form = UserForm(request.POST)

        if form.is_valid() and save_user(user, form):
            return submit_redirect(request, user,
                                   edit_redirect = 'edit?mcid=%(mcid)s')
    else:
        form = UserForm(initial = user.__dict__)

    return render_to_response('users/edit.html',
                              default_context(request, form=form, mcuser=user))

def save_user(user, form):
    """Fill out the model object _user_ based on form object
    _form_.  If a password is set, then set the _sha1_ field
    to the SHA1 hash of 'medcommons.net' + mcid + password.

    If there's a database error, sets the form.errors['email']
    for rendering the error.

    Returns True if user correctly saved, false otherwise.
    """
    user.email = form.clean_data['email']

    pw = form.clean_data['pw1']
    if pw:
        user.set_password(form.clean_data['pw1'])

    user.enable_simtrak = form.clean_data['enable_simtrak']
    user.enable_dod = form.clean_data['enable_dod']

    try:
        user.save()
        return True
    except Exception, e:
        msg = ' '.join([str(x) for x in e.args])
        form.errors.setdefault('email', []).append(msg)
        return False

@login_required
def groups_req(request):
    """search and/or list all groups, so this user can be added
    to specific groups
    """
    mcid = normalize_mcid(request.REQUEST['mcid'])
    user = User.objects.get(mcid=mcid)

    where = ['groupmembers.memberaccid = %s' % mcid,
             'groupmembers.groupinstanceid = groupinstances.groupinstanceid']

    member_of = Group.objects.extra(where=where,
                                    tables = ['groupmembers'])

    q = request.REQUEST.get('q', '')

    if q:
        groups = Group.objects.filter(name__icontains=q)
    else:
        groups = Group.objects.order_by('name')

    return object_list(request,
                       queryset = groups,
                       template_name = 'users/groups.html',
                       paginate_by = 10,
                       allow_empty = True,
                       extra_context = default_context(request, mcuser=user,
                                                       member_of=member_of,
                                                       q=q))

class GroupForm(forms.Form):
    accid = forms.RegexField(VALID_MCID,
                             label = 'Group Owner')

    name = forms.CharField(max_length = 765,
                           label = 'Group Name')

@login_required
def addgroup_req(request):
    mcid = normalize_mcid(request.REQUEST['mcid'])
    user = User.objects.get(mcid = mcid)

    if request.POST:
        form = GroupForm(request.POST)

        if form.is_valid():
            g = create_group(form.clean_data['name'],
                             normalize_mcid(form.clean_data['accid']),
                             'https://' + request.META['SERVER_NAME'])

            g.save()

            return submit_redirect(request, g,
                                   create_redirect='addgroup?mcid=%s' % mcid,
                                   edit_redirect='../groups/edit?id=%(groupinstanceid)s',
                                   save_redirect='groups?mcid=%s' % mcid)

    else:
        form = GroupForm(initial = dict(accid = pretty_mcid(mcid)))

    return render_to_response('users/addgroup.html',
                              default_context(request, mcuser=user, form=form))

@login_required
def add_to_group_req(request):
    mcid = normalize_mcid(request.GET['mcid'])
    group = request.GET['group']

    sql_execute("INSERT INTO groupmembers (groupinstanceid, memberaccid) " + \
                "VALUES (%s, %s);", int(group), mcid)

    return HttpResponseRedirect("user?mcid=" + mcid)

@login_required
def remove_from_group_req(request):
    mcid = normalize_mcid(request.GET['mcid'])
    group = request.GET['group']

    sql_execute("DELETE FROM groupmembers " + \
                "WHERE groupinstanceid=%s AND memberaccid=%s;",
                int(group), mcid)

    return HttpResponseRedirect("user?mcid=" + mcid)

@login_required
def unlink_idp_req(request):
    return unlink_user(request, "user?mcid=%(mcid)s")

@login_required
def login_as_req(request):
    mcid = normalize_mcid(request.GET['mcid'])
    user = User.objects.get(mcid = mcid)

    return render_to_response('users/login_as.html',
                              default_context(request, mcid=pretty_mcid(mcid),
                                              mcuser=user))
