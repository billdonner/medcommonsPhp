#!/usr/bin/env python

from django import newforms as forms  # change to 'from django import forms'
from django.db.models import Q
from django.views.generic.list_detail import object_list
from django.contrib.auth.models import User
from django.shortcuts import render_to_response

from utils import login_required, submit_redirect, default_context

@login_required
def index_req(request):
    return object_list(request,
                       queryset = User.objects.order_by('username'),
                       template_name = 'admins/index.html',
                       paginate_by = 20,
                       allow_empty = True,
                       extra_context = default_context(request))

class CreateForm(forms.Form):
    username = forms.RegexField('^[a-zA-Z0-9_]*$', max_length=30)
    first_name = forms.CharField(max_length = 30,
                                 required = False)
    last_name = forms.CharField(max_length = 30,
                                required = False)

    email = forms.EmailField()

    pw1 = forms.CharField(widget = forms.PasswordInput,
                          label = 'Password')
    pw2 = forms.CharField(widget = forms.PasswordInput,
                          label = 'Password (again)',
                          required = False)

class EditForm(forms.Form):
    username = forms.RegexField('^[a-zA-Z0-9_]*$', max_length=30)
    is_active = forms.BooleanField(required = False)
    first_name = forms.CharField(max_length = 30,
                                 required = False)
    last_name = forms.CharField(max_length = 30,
                                required = False)

    email = forms.EmailField()

    pw1 = forms.CharField(widget = forms.PasswordInput,
                          label = 'Password',
                          required = False)
    pw2 = forms.CharField(widget = forms.PasswordInput,
                          label = 'Password (again)',
                          required = False)
    
@login_required
def create_req(request):
    """Form for registering a new user.
    """
    if request.POST:
        form = CreateForm(request.POST)
    else:
        form = CreateForm()

    if form.is_valid():
        if form.clean_data['pw1'] != form.clean_data['pw2']:
            form.errors['pw2'] = ["Must match password above"]
        else:
            try:
                user = User.objects.create_user(form.clean_data['username'],
                                                form.clean_data['email'],
                                                form.clean_data['pw1'])
            except:
                form.errors['username'] = ["Username already exists"]
            else:
                user.first_name = form.clean_data['first_name']
                user.last_name = form.clean_data['last_name']
                user.is_staff = True
                user.save()

                return submit_redirect(request, user,
                                       edit_redirect = 'edit?id=%(id)s')

    return render_to_response('admins/create.html',
			      default_context(request, form=form))

@login_required
def edit_req(request):
    id = request.REQUEST['id']
    object = User.objects.get(id = id)

    if request.POST:
        form = EditForm(request.POST)

        if not form.is_valid():
            pass
        elif form.clean_data['pw1'] != form.clean_data['pw2']:
            form.errors['pw2'] = ['Must match password above']
        else:

            object.username = form.clean_data['username']
            object.is_staff = True
            object.is_active = form.clean_data['is_active']
            object.first_name = form.clean_data['first_name']
            object.last_name = form.clean_data['last_name']
            object.email = form.clean_data['email']
            
            if form.clean_data['pw1']:
                object.set_password(form.clean_data['pw1'])

            try:
                object.save()
            except:
                form.errors['username'] = ['Username already exists']
            else:
                return submit_redirect(request, object,
                                       edit_redirect = 'edit?id=%(id)s')

    else:
        form = EditForm(initial = object.__dict__)

    return render_to_response('admins/edit.html',
			      default_context(request, form=form,
					      object=object))

@login_required
def search_req(request):
    try:
        qstr = request.GET['q']
        if '@' in qstr:
            qs = User.objects.filter(email=qstr)
        else:
            q = qstr.split()
            x = Q(first_name=q[0]) | Q(last_name=q[0]) | Q(username=q[0])
            for y in q[1:]:
                x = x | Q(first_name=y) | Q(last_name=y) | Q(username=y)
            qs = User.objects.filter(x)

        return object_list(request, queryset = qs,
                           template_name = 'admins/search.html',
                           paginate_by = 20,
                           allow_empty = True,
                           extra_context = default_context(request, q=qstr))
    except KeyError:
        return render_to_response('admins/search.html',
				  default_context(request))
