#!/usr/bin/env python
# security/views.py
# Copyright(c) 2007, MedCommons, Inc.

"""Use OpenSSL commands to manage certificates.
"""

__author__ = "Terence Way"
__email__  = "tway@medcommons.net"
__version__ = "1.0: August 6, 2007"

from os import close, fdopen, pipe, read, remove, write
from tempfile import mkstemp
from datetime import datetime

from django import newforms as forms
from django.http import HttpResponse
from django.shortcuts import render_to_response
from django.views.generic.list_detail import object_list
from django.http import HttpResponseRedirect

from utils import login_required, default_context
from process import spawn
from security.models import Certificate

@login_required
def index_req(request):
    return object_list(request,
		       queryset = Certificate.objects.all(),
		       template_name = 'security/index.html',
		       paginate_by = 20,
		       allow_empty = True,
		       extra_context = default_context(request))

class ReqForm(forms.Form):
    CN = forms.CharField(label='Domain Name',
			 help_text='Common Name, must be the fully-qualified domain name for this web server')
    C = forms.RegexField('^[A-Z]{2}$',
			 label='Country',
			 max_length = 2,
			 help_text='Two-letter country code.  For example: US')
    ST = forms.CharField(label='State or Province',
			 help_text='For example: California')
    L = forms.CharField(label='City, Locality, or Town',
			help_text='For example: Los Angeles')
    O = forms.CharField(label='Organization')
    OU = forms.CharField(label='Organizational Unit', required=False)

    pw1 = forms.CharField(widget=forms.PasswordInput(),
			  label='Password',
			  help_text='You will be prompted for this password when installing the certificate')
    pw2 = forms.CharField(widget=forms.PasswordInput(),
			  label='Password (again)')

    def clean_pw2(self):
	if self.data['pw1'] != self.data['pw2']:
	    raise forms.ValidationError('Passwords must match')
	return self.data['pw2']

@login_required
def req_req(request):
    if request.POST:
	form = ReqForm(request.POST)

	if form.is_valid():
	    cert = Certificate()
	    cert.CN = form.clean_data['CN']
	    cert.C  = form.clean_data['C']
	    cert.ST = form.clean_data['ST']
	    cert.L  = form.clean_data['L']
	    cert.O  = form.clean_data['O']
	    cert.OU = form.clean_data['OU']

	    cert.key = genrsa(form.clean_data['pw1'])
	    cert.issued = datetime.now()

	    subject = form.clean_data.copy()
	    del subject['pw1']
	    del subject['pw2']

	    csr = req(key = cert.key,
		      subject = subject,
		      password = form.clean_data['pw1'])

	    cert.csr = csr
	    cert.save()

	    return HttpResponseRedirect('csr?id=%d' % cert.id)
    else:
	initial = dict(CN = request.META['SERVER_NAME'])
	last_cert = get_last_certificate()
	if last_cert:
	    initial.update(last_cert.__dict__)

	form = ReqForm(initial = initial)

    return render_to_response('security/req.html',
                              default_context(request, form=form))

@login_required
def csr_req(request):
    id = request.GET['id']
    cert = Certificate.objects.get(id=id)

    csr = cert.csr.split('\n')

    cols = 0
    for line in csr:
	cols = max(cols, len(line))

    return render_to_response('security/csr.html',
                              default_context(request, cert=cert, rows=len(csr),
                                              cols = cols))

@login_required
def download_csr_req(request):
    id = request.GET['id']
    cert = Certificate.objects.get(id=id)

    response = HttpResponse(mimetype='text/plain')
    response['Content-Disposition'] = 'attachment; filename=certificate.csr'

    response.write(cert.csr)

    return response

class CertForm(forms.Form):
    text = forms.CharField(widget = forms.Textarea(attrs = dict(rows=14,
								cols=72)),
			   required = False)
    file = forms.CharField(widget = forms.FileInput(),
			   required = False)

    def clean_text(self):
	text = self.clean_data.get('text')
	file = self.data.get('file')

	if not text and not file:
	    raise forms.ValidationError('One of "text" or "file" must be filled out')

	if text and not is_valid_cert(text):
	    raise forms.ValidationError('A valid certificate is required')

	return text

    def clean_file(self):
	text = self.data.get('text')
	file = self.data.get('file')

	if text and file:
	    raise forms.ValidationError('Only one of "text" or "file" must be filled out')

	if file and not is_valid_cert(file['content']):
	    raise forms.ValidationError('A valid certificate is required')

	return file

@login_required
def cert_req(request):
    id = request.REQUEST['id']
    cert = Certificate.objects.get(id = id)

    if request.POST:
	request.POST.update(request.FILES)

	form = CertForm(request.POST)

	if form.is_valid():
	    text = form.clean_data['text']
	    file = form.clean_data['file']
	    if file: file = file.get('content')

	    crt = text or file

	    cert.crt = crt
	    cert.save()

	    return HttpResponseRedirect('.')

    else:
	form = CertForm(initial = dict(id = cert.id))

    return render_to_response('security/cert.html',
                              default_context(request, form=form, cert=cert))

def is_valid_cert(cert):
    if not isinstance(cert, (str, unicode)):
	return False

    cert = cert.upper()
    return '-BEGIN CERTIFICATE-' in cert and '-END CERTIFICATE-' in cert

def get_last_certificate():
    c = Certificate.objects.order_by('-issued')[:1]

    if c:
	return c[0]
    else:
	return None

def genrsa(password):
    p_in = pipe()
    p_out = pipe()

    cmd = ['openssl', 'genrsa']

    if password:
	cmd += ['-des3', '-passout', 'stdin']

    cmd += ['1024']

    spawn(cmd, stdin = p_in, stdout = p_out)

    # r_in, w_in file descriptors to new process's standard input
    # likewise r_out, w_out for new process's standard output
    r_in, w_in = p_in
    r_out, w_out = p_out

    close(r_in)
    close(w_out)

    if password:
	write(w_in, password)
    close(w_in)

    f_out = fdopen(r_out, 'r')
    key = f_out.readlines()
    f_out.close()

    return ''.join(key)

def req(key, subject, password):
    p_in = pipe()
    p_out = pipe()

    w_tmp, w_fn = mkstemp()

    write(w_tmp, key)
    close(w_tmp)

    subject = ''.join(['/%s=%s' % (k, v) for k, v in subject.items() if v])

    spawn(['openssl', 'req', '-new', '-key', w_fn, '-passin', 'stdin',
	   '-subj', subject], stdin = p_in, stdout = p_out)

    r_in, w_in = p_in
    r_out, w_out = p_out

    close(r_in)
    close(w_out)

    write(w_in, password)
    close(w_in)

    f_out = fdopen(r_out, 'r')
    csr = f_out.readlines()
    f_out.close()

    remove(w_fn)

    return ''.join(csr)

if __name__ == '__main__':
    main()
    
