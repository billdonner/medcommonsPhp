#!/usr/bin/env python

from time import time
from os.path import join

from django.conf import settings
from django.contrib.auth.models import User as Admin
from django.db import connection
from django.http import HttpResponse
from django.shortcuts import render_to_response

from users.models import User
from idps.models import IdentityProvider
from groups.models import Group
from utils import login_required, sql_cursor, default_context

SQL = """
SELECT (SELECT COUNT(*) FROM mcgateway.backup_queue
        WHERE status='PERMERR') AS permerrs, 
       (SELECT COUNT(*) FROM mcgateway.backup_queue
        WHERE status='TEMPERR') AS temperrs,
       (SELECT COUNT(*) FROM mcgateway.backup_queue
        WHERE endtime IS NULL) AS pending,
       (SELECT COUNT(*) FROM mcgateway.backup_queue) AS total
"""

@login_required
def index_req(request):
    cursor = sql_cursor()

    cursor.execute(SQL)
    a = cursor.fetchone()

    vars = default_context(request, admins_count = Admin.objects.count(),
                users_count = User.objects.filter(sha1__isnull=False).count(),
                groups_count = Group.objects.count(),
                idps_count = IdentityProvider.objects.count())

    if a:
        vars['backups'] = True
        n = float(a[3])
        if n == 0.0:
            n = 1.0

        vars['backup_permerrs'] = int(100.0 * a[0] / n)
        vars['backup_temperrs'] = int(100.0 * a[1] / n)
        vars['backup_finished'] = int(100.0 * (a[3] - a[2]) / n)
    else:
        vars['backups'] = False

    return render_to_response('index.html', vars)

media_img_path = join(settings.MEDIA_ROOT, 'img')

OK_LED = file(join(media_img_path, 'greenball.png')).read()
WARNING_LED = file(join(media_img_path, 'yellowball.png')).read()
ERROR_LED = file(join(media_img_path, 'redball.png')).read()

def console_led_req(request):
    return HttpResponse(OK_LED, mimetype='image/png')

def db_led_req(request):
    return show_led(db_status)

def db_status():
    cursor = connection.cursor()
    cursor.execute("SELECT COUNT(*) FROM users")
    x = cursor.fetchone()[0]
    cursor.close()
    return True

def mclocals_led_req(request):
    return show_led(mclocals_status)

def mclocals_status():
    from urllib2 import urlopen
    from cgi import parse_qs

    m = parse_qs(urlopen('http://mcid.internal:1080/status').read())
    return m['status'][0] == 'OK'

def gateway_led_req(request):
    return show_led(gateway_status)

def gateway_status():
    from urllib2 import urlopen
    from xml.dom.minidom import parseString
    d = parseString(urlopen('http://localhost/router/status.do?fmt=xml').read())
    return d

def show_led(func, *args, **kwargs):
    """Execute a function _func_.  If it raises an exception, show
    a red LED ball (/console/media/img/redball.png).  If it takes
    longer than some time (0.5 seconds) show a yellow LED ball
    (/console/media/img/yellowball.png).  Otherwise show a
    green LED ball (/console/media/img/greenball.png)
    """
    try:
	start_time = time()

	if func(*args, **kwargs):
	    led = OK_LED
	else:
	    led = WARNING_LED

	delta_time = time() - start_time

	if delta_time > 0.5:
	    led = WARNING_LED
    except:
	led = ERROR_LED

    return HttpResponse(led, mimetype='image/png')
