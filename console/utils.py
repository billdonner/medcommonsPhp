#!/usr/bin/env python

from django.http import HttpResponseRedirect
from django.conf import settings
from django.contrib.auth.decorators import user_passes_test
from django.db import connection, transaction

LOGIN_URL = settings.ROOT_URL + 'account/login'

login_required = user_passes_test(lambda u: u.is_authenticated(),
				  login_url = LOGIN_URL)

def submit_redirect(request, object,
                    create_redirect = 'create',
                    edit_redirect = 'edit?id=%(id)',
                    save_redirect = '.',
                    add_redirect = 'add'):
    if 'create' in request.POST:
        url = create_redirect % object.__dict__
    elif 'add' in request.POST:
	url = add_redirect % object.__dict__
    elif 'edit' in request.POST:
        url = edit_redirect % object.__dict__
    else:
        url = save_redirect % object.__dict__

    return HttpResponseRedirect(url)

def sql_cursor():
    return connection.cursor()

    #from MySQLdb import connect
    #from django.conf import settings
    #db = connect(host = settings.DATABASE_HOST,
    #             db = settings.DATABASE_NAME,
    #             user = settings.DATABASE_USER,
    #             passwd = settings.DATABASE_PASSWORD)
    #
    #cursor = db.cursor()

def sql_execute(sql, *vars):
    cursor = sql_cursor()

    cursor.execute(sql, vars)

    transaction.commit_unless_managed()

def default_context(request, **kwargs):
    kwargs['META'] = request.META
    kwargs['user'] = request.user
    kwargs['root'] = settings.ROOT_URL
    kwargs['media'] = settings.MEDIA_URL
    return kwargs

def _test():
    import doctest, utils
    return doctest.testmod(utils)

if __name__ == '__main__':
    _test()
