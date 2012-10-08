#!/usr/bin/env python

from django import newforms as forms
from django.http import HttpResponse, HttpResponseRedirect
from django.shortcuts import render_to_response
from django.conf import settings

from os.path import expanduser

import s3

from utils import login_required, sql_cursor, default_context
from config.models import MCProperty
from config.properties import get_properties
from config.views import wizard

@login_required
def index_req(request):
    cursor = sql_cursor()
    sql = "SELECT (SELECT COUNT(*) FROM mcgateway.backup_queue WHERE status='PERMERR') AS permerrs, " + \
          "       (SELECT COUNT(*) FROM mcgateway.backup_queue WHERE status='TEMPERR') AS temperrs, " + \
          "       (SELECT COUNT(*) FROM mcgateway.backup_queue WHERE endtime IS NULL) AS pending, " + \
          "       (SELECT COUNT(*) FROM mcgateway.backup_queue) AS total"

    cursor.execute(sql)
    a = cursor.fetchone()

    vars = default_context(request)

    if a:
        vars['backups'] = True
        n = float(a[3])
        if n == 0.0:
            n = 1.0
            
        vars['backup_permerrs'] = int(100.0 * a[0] / n)
        vars['backup_temperrs'] = int(100.0 * a[1] / n)
        vars['backup_pending'] = int(100.0 * a[2] / n)
        vars['backup_finished'] = int(100.0 * (a[3] - a[2]) / n)
        vars['permerrs'] = a[0]
        vars['temperrs'] = a[1]
        vars['pending'] = a[2]
        vars['finished'] = a[3] - a[2]
        vars['total'] = a[3]
    else:
        vars['backups'] = False

    cursor.execute("SHOW MASTER STATUS")
    columns = [c[0].lower() for c in cursor.description]
    vars['master'] = [dict(zip(columns, row)) for row in cursor.fetchall()]

    cursor.execute("SHOW SLAVE STATUS")
    columns = [c[0].lower() for c in cursor.description]
    vars['slave'] = [dict(zip(columns, row)) for row in cursor.fetchall()]

    return render_to_response('backups/index.html', vars)

class KeyForm(forms.Form):
    S3Key_ID = forms.RegexField('^[a-zA-Z0-9+/]{20}$',
                                widget = forms.TextInput(dict(size = 25)))

    S3Secret = forms.RegexField('^[a-zA-Z0-9+/]{40}$',
                                widget = forms.TextInput(dict(size = 50)))

    def clean_S3Secret(self):
        try:
            a = s3.list_all_my_buckets(key_id = self.data['S3Key_ID'],
                                       secret = self.data['S3Secret'])
        except:
            pass

        else:
            if a.Buckets:
                return self.data['S3Secret']

        raise forms.ValidationError('Invalid S3 Credentials')


@login_required
def keys_req(request):
    return wizard(request, KeyForm, 'backups/wiz_1keys.html',
                  next='buckets')

class BucketForm(forms.Form):
    S3Bucket = forms.CharField()

def s3_keys():
    a = get_properties()
    return dict(key_id = a['S3Key_ID'], secret = a['S3Secret'])

@login_required
def buckets_req(request):
    a = get_properties()

    if 'bucket' in request.GET and request.GET['bucket']:
        a['S3Bucket'] = request.GET['bucket']

    result = s3.list_all_my_buckets(key_id = a['S3Key_ID'],
                                    secret = a['S3Secret'])

    buckets = result.Buckets.Bucket
    if not isinstance(buckets, list):
        buckets = [buckets]

    return wizard(request, BucketForm, 'backups/wiz_2buckets.html',
                  initial = a, prev='keys', next='encryption',
                  context = dict(buckets = buckets, owner = result.Owner))

class CreateBucketForm(forms.Form):
    bucket_name = forms.RegexField('^[a-zA-Z0-9_\.\-]*$')

@login_required
def createbucket_req(request):
    if request.POST:
        form = CreateBucketForm(request.POST)

        if form.is_valid():
            a = get_properties()
            s3.create_bucket(form.clean_data['bucket_name'],
                             key_id = a['S3Key_ID'],
                             secret = a['S3Secret'])

            if 'add' in request.POST:
                url = 'createbucket'
            else:
                url = 'buckets'

            return HttpResponseRedirect(url)
    else:
        form = CreateBucketForm()

    return render_to_response('backups/createbucket.html',
                              default_context(request, form=form))

class EncryptionForm(forms.Form):
    pw1 = forms.CharField(widget = forms.PasswordInput,
                          label = 'AES/256 key',
                          required = False)

    pw2 = forms.CharField(widget = forms.PasswordInput,
                          label = 'AES/256 key (again)',
                          required = False)

    def clean_pw2(self):
        if self.data['pw1'] != self.data['pw2']:
            raise forms.ValidationError('Keys must match')
        return self.data['pw2']

@login_required
def encryption_req(request):
    backup_dir = expanduser(settings.HOME_DIR)

    if request.POST:
        form = EncryptionForm(request.POST)

        if form.is_valid():
            if form.clean_data['pw1']:
                print >>file(backup_dir + '/.aes-key', 'w'), form.clean_data['pw1']

            if 'prev' in request.POST:
                url = 'buckets'
            else:
                url = 'publish'

            return HttpResponseRedirect(url)
    else:
        form = EncryptionForm()

    return render_to_response('backups/wiz_3encryption.html',
                              default_context(request, form=form))

@login_required
def publish_req(request):
    if request.POST:
        return HttpResponseRedirect('encryption')

    return render_to_response('backups/wiz_4publish.html',
                              default_context(request))

@login_required
def configuration_req(request):
    """Backup the console configuration: the database tables that
    affect the console, and the customized templates.
    """
    pass

def master_req(request):
    cursor = sql_cursor()

    cursor.execute("SHOW SLAVE STATUS")
    columns = [c[0].lower() for c in cursor.description]
    slave = [dict(zip(columns, row)) for row in cursor.fetchall()]


    if len(slave) == 1:
        return HttpResponseRedirect('https://%s/console/backups/' % slave[0]['master_host'])
    else:
        return HttpResponseRedirect('.')

def status_led_req(request):
    import Image, ImageDraw, ImageFont

    cursor = sql_cursor()

    cursor.execute("SHOW MASTER STATUS")
    columns = [c[0].lower() for c in cursor.description]
    master = [dict(zip(columns, row)) for row in cursor.fetchall()]

    img = Image.new('RGB', (200, 11), 0xFFFFFF)
    draw = ImageDraw.Draw(img)
    font = ImageFont.load_default()

    if master:
        m0 = master[0]

        draw.ellipse((0, 0, 10, 10), fill=0x00FF00, outline=0x00cc00)
        draw.text((3, -1), 'M', font=font, fill=0x000000)

    else:
        cursor.execute("SHOW SLAVE STATUS")
        columns = [c[0].lower() for c in cursor.description]
        slave = [dict(zip(columns, row)) for row in cursor.fetchall()]

        if slave:
            s0 = slave[0]

            draw.ellipse((0, 0, 10, 10), fill=0xFFFF33, outline=0xCCCC33)
            draw.text((3, -1), 'S', font=font, fill=0x000000)
            draw.text((15, -1), s0['master_host'], font=font, fill=0x000000)

    del draw

    response = HttpResponse(mimetype='image/png')
    img.save(response, 'PNG')

    return response
    
