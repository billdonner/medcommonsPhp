# Create your views here.

import django.newforms as forms
from django.db import connection
from django.http import HttpResponseRedirect
from django.shortcuts import render_to_response
from django.views.generic.list_detail import object_list

from urllib import quote

from utils import login_required, submit_redirect, default_context, sql_execute
from appliances.models import Appliance, Log

class ApplianceForm(forms.Form):
    url = forms.URLField()
    email = forms.EmailField()

    appliance_type = forms.ChoiceField(choices = [('', 'Unknown'),
                                                  ('EC2', 'Amazon EC2'),
                                                  ('VMX', 'VMware'),
                                                  ('Devel', 'Development')])

@login_required
def index_req(request):
    query = './?'
    order = 'name'

    if 'order' in request.GET:
        order = request.GET['order']
        query += 'order=%s&amp;' % quote(order)

    return object_list(request,
                       queryset = Appliance.objects.order_by(*order.split(',')),
                       template_name='appliances/index.html',
                       paginate_by=20,
                       allow_empty=True,
                       extra_context=default_context(request,
                                                     order_query='./?',
                                                     search_query=query))
                       
    return render_to_response('appliances/index.html',
                              default_context(request, objects=objects))

def augment(o):
    l = Log.objects.filter(appliance = o).order_by('-datetime')
    if len(l) > 0:
        o.ipaddr = l[0].ipaddr
    else:
        o.ipaddr = None

    return o

@login_required
def edit_req(request):
    id = int(request.REQUEST['id'].strip())

    object = Appliance.objects.get(id = id)

    if request.POST:
        form = ApplianceForm(request.POST)

        if form.is_valid():
            # object.name = form.clean_data['name']
            object.url = form.clean_data['url']
            object.email = form.clean_data['email']
            object.appliance_type = form.clean_data['appliance_type']

            object.save()

            return submit_redirect(request, object)
    else:
        form = ApplianceForm(initial = object.__dict__)

    return render_to_response('appliances/edit.html',
                              default_context(request, form=form,
                                              object=object))

@login_required
def merge_req(request):
    id = int(request.REQUEST['id'].strip())

    object = Appliance.objects.get(id = id)
    augment(object)

    objects = Appliance.objects.all()

    same_objects = []
    diff_objects = []

    # divide out the appliances: those with the same IP address and those
    # with different IP addresses
    for o in objects:
        augment(o)

        if o.id == object.id:
            continue

        if o.ipaddr == object.ipaddr:
            same_objects.append(o)
        else:
            diff_objects.append(o)

    if request.POST:
        to_id = int(request.REQUEST['to'].strip())
        if to_id != id:
            sql_execute("UPDATE alloc_log SET appliance_id = %s WHERE appliance_id = %s",
                        to_id, id)
            object.delete()

        return HttpResponseRedirect('.')

    return render_to_response('appliances/merge.html',
                              default_context(request, object=object,
                                              same_objects=same_objects,
                                              diff_objects=diff_objects))

@login_required
def delete_req(request):
    id = int(request.REQUEST['id'])
    object = Appliance.objects.get(id=id)

    if request.POST:
        if 'delete' in request.POST or 'delete.x' in request.POST:
            object.delete()

        return HttpResponseRedirect('.')

    return render_to_response('appliances/merge.html',
                              default_context(request, object=object))
