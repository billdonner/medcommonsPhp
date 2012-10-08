#!/usr/bin/env python

from urlparse import urljoin
from cgi import parse_qs

from django.conf import settings
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.decorators import user_passes_test
from django.http import HttpResponseRedirect
from django.shortcuts import render_to_response
from django.template import Context
from django.template.loader import get_template

from django.contrib.auth.models import User

from session import urlsafe_b64encode, urandom, \
	add_encrypted_query_string, sign_query_string, \
	get_encrypted_query_string, is_signed_query_string_valid

DEFAULT_REDIRECT = settings.ROOT_URL

FROM_EMAIL = 'cmo@medcommons.net'

from utils import login_required, default_context

def login_req(request):
    next = request.REQUEST.get('next',
			       request.META.get('HTTP_REFERER',
						DEFAULT_REDIRECT))
    args = dict(next=next, META=request.META, root=settings.ROOT_URL,
                media=settings.MEDIA_URL)

    try:
	username = request.POST['username']
	password = request.POST['password']
    except KeyError:
	pass
    else:
	args['username'] = username
	user = authenticate(username = username, password = password)

	if user is not None and user.is_active:
	    login(request, user)

	    return HttpResponseRedirect(next)

	args['error'] = True

    return render_to_response('registration/login.html', args)

@login_required
def password_req(request):
    """Allows the user to change his/her password.

    If the existing password matches, and both new password
    fields match, then the password is changed.

    A ?next=/... query parameter can be added, so after the
    password is changed, the user is redirected back to the
    original referring page.
    """
    next = request.POST.get('next',
			    request.META.get('HTTP_REFERER',
					     DEFAULT_REDIRECT))
    args = default_context(request, username=request.user.username, next=next)

    try:
	password = request.POST['password']

	pw1 = request.POST['pw1']
	pw2 = request.POST['pw2']
    except KeyError:
	pass
    else:
	if pw1 != pw2:
	    args['mismatch'] = True
	elif not request.user.check_password(password):
	    args['error'] = True
	else:
	    request.user.set_password(pw1)
	    request.user.save()
	    return HttpResponseRedirect(next)

    return render_to_response('registration/password.html', args)

def forgot_req(request):
    """Handles the 'forgotten password' form.  The user can ask
    for a specific username to be reset.

    The user can also specify an email address instead of a
    username.

    An email containing encrypted and signed links is sent to
    the particular email address.

    There is no difference in the output if 0, 1, or many users
    are found matching the particular username/email.  This prevents
    people from guessing valid usernames or emails.

    The username can be specified in the query string, like so::

        /account/forgot?username=terry
    """
    server = request.META['SERVER_NAME']
    recover_url = urljoin(full_url(request), 'recover')

    if request.POST and not request.user.is_authenticated():
	try:
	    username_or_email = request.POST['username']
	except KeyError:
	    pass
	else:
	    if '@' in username_or_email:
		qs = User.objects.filter(email = username_or_email)
	    else:
		qs = User.objects.filter(username = username_or_email)

	    users = []
	    user = None

	    for user in qs:
		query = 'salt=%s&user=%s' % (urlsafe_b64encode(urandom(8)),\
					     user.username)
		url = add_encrypted_query_string(recover_url, query,
						 settings.SECRET_KEY)

		url = sign_query_string(settings.SECRET_KEY + user.password,
					url)

		users.append(dict(username = user.username, url = url))

	    template = get_template('registration/recover-password.txt')
	    context = Context(dict(users = users, ApplianceName = server))

	    if len(users) == 1:
		plural = ''
	    else:
		plural = 's'

	    if user:
		user.email_user(subject = "Your %s console account%s" % (server, plural),
				from_email = FROM_EMAIL,
				message = template.render(context))

	    return HttpResponseRedirect('sent')

    return render_to_response('registration/forgotten.html',
			      dict(username=request.GET.get('username', ''),
                                   META=request.META, root=settings.ROOT_URL,
                                   media=settings.MEDIA_URL))

def full_url(request):
    meta = request.META

    if request.is_secure():
	url = 'https://'
	defaultPort = 443
    else:
	url = 'http://'
	defaultPort = 80

    url += meta['SERVER_NAME']

    port = int(meta['SERVER_PORT'])
    if port != defaultPort:
       url += ':%d' % port

    return url + request.path

def sent_req(request):
    return render_to_response('registration/sent.html',
                              dict(META = request.META,
                                   root = settings.ROOT_URL,
                                   media = settings.MEDIA_URL))

def recover_req(request):
    """Allows the user to change his/her password.
    """
    query_string = request.META['QUERY_STRING']
    query_args = parse_qs(get_encrypted_query_string(query_string,
						     settings.SECRET_KEY))

    template_map = dict(META = request.META,
			enc = request.GET['enc'],
			hmac = request.GET['hmac'],
                        root = settings.ROOT_URL,
                        media = settings.MEDIA_URL)

    if request.POST and 'user' in query_args:
	username = query_args['user'][0]
	user = User.objects.get(username = username)
	pw1 = request.POST['pw1']
	pw2 = request.POST['pw2']

	if pw1 != pw2:
	    template_map['mismatch'] = True
	elif not is_signed_query_string_valid(settings.SECRET_KEY + user.password,
					      query_string):
	    template_map['error'] = True
	else:
	    user.set_password(pw1)
	    user.save()
	    user = authenticate(username = username, password = pw1)
	    login(request, user)
	    return HttpResponseRedirect('..')

    return render_to_response('registration/recover.html', template_map)

def logout_req(request):
    """Cancels a user session.

    Puts up the login form.
    """
    if request.user.is_authenticated():
	logout(request)
    return render_to_response('registration/login.html',
			      dict(next = DEFAULT_REDIRECT,
                                   META = request.META,
                                   root = settings.ROOT_URL,
                                   media = settings.MEDIA_URL))
