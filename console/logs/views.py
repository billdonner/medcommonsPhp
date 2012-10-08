#!/usr/bin/env python

from django.shortcuts import render_to_response
from django.views.generic.list_detail import object_list

from utils import default_context, login_required
from logs.models import Entry

@login_required
def index_req(request):
    return object_list(request,
                       queryset = Entry.objects.order_by('datetime', 'id'),
                       template_name = 'logs/index.html',
                       paginate_by = 20,
                       allow_empty = True,
                       extra_context = default_context(request))

@login_required
def router_req(request):
    return render_to_response('logs/router.html', default_context(request))
