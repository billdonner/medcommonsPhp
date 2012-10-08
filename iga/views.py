#!/usr/bin/env python

from django.conf import settings
from django.contrib.auth.models import User as Admin
from django.db import connection
from django.http import HttpResponse
from django.shortcuts import render_to_response

from time import time
from os.path import join

from utils import login_required, default_context
from appliances.models import Appliance

media_img_path = join(settings.MEDIA_ROOT, 'img')

OK_LED = file(join(media_img_path, 'greenball.png')).read()
WARNING_LED = file(join(media_img_path, 'yellowball.png')).read()
ERROR_LED = file(join(media_img_path, 'redball.png')).read()

@login_required
def index_req(request):
    vars = default_context(request, admins_count = Admin.objects.count(),
                appliances_count = Appliance.objects.count())

    return render_to_response('index.html', vars)

def status_req(request):
    from dns_axfr import axfr
    from csv import reader

    machines = {}

    try:
        r = reader(file('/var/www/iga/machines.csv'))
        columns = r.next()
        extra = [None for c in columns]
        for row in r:
            if row:
                machine = dict(zip(columns, row + extra))
                if len(row) > len(columns):
                    machine['extra'] = row[len(columns):]
                machines.setdefault(row[0], {}).update(machine)
    except:
        pass

    appliances = Appliance.objects.all()

    names, cnames, addrs = axfr()
    for a in appliances:
        if a.name in addrs:
            addrs.pop(a.name)
        if a.name in machines:
            m = machines.pop(a.name)
            for k, v in m.items():
                setattr(a, k, v)
            a.appliance_type = m.get('type', getattr(a, 'appliance_type', ''))

    machines = machines.values()
    machines.sort()
    for a in machines:
        if a['name'] in addrs:
            addrs.pop(a['name'])

    other = addrs.keys()
    other.sort()

    return render_to_response('status.html',
                              default_context(request,
                                              appliances=appliances,
                                              machines=machines,
                                              other=other))

def iga_led_req(request):
    return HttpResponse(OK_LED, mimetype='image/png')

def db_led_req(request):
    return show_led(db_status)

def db_status():
    cursor = connection.cursor()
    cursor.execute("SELECT COUNT(*) FROM appliances")
    x = cursor.fetchone()[0]
    cursor.close()
    return True

def mcglobals_led_req(request):
    return show_led(mcglobals_status)

def mcglobals_status():
    from urllib2 import urlopen
    from cgi import parse_qs

    m = parse_qs(urlopen('http://mcid.internal:1081/status').read())
    return m['status'][0] == 'OK'

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
 
