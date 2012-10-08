#!/usr/bin/env python
# vim: tabstop=8 softtabstop=8 shiftwidth=8 expandtab

from django import newforms as forms # change to 'from django import forms'
from django.http import HttpResponseRedirect
from django.views.generic.list_detail import object_list
from django.shortcuts import render_to_response

from groups.models import Group, Practice, create_group, delete_group, \
			  Practiceccrevent

from users.models import User, VALID_MCID, pretty_mcid, \
			 normalize_mcid, search_query_set, all_query_set

from utils import login_required, sql_execute, submit_redirect, \
                          default_context

@login_required
def index_req(request):
    return object_list(request,
                       queryset = Group.objects.order_by('name'),
                       template_name = 'groups/index.html',
                       paginate_by = 20,
                       allow_empty = True,
                       extra_context = default_context(request))

@login_required
def search_req(request):
    q = request.GET['q']
    qs = Group.objects.filter(name__icontains = q)
    
    return object_list(request,
                       queryset = qs,
                       template_name = 'groups/search.html',
                       paginate_by = 20,
                       allow_empty = True,
                       extra_context = default_context(request, q=q))

@login_required
def group_req(request):
    try:
        id = int(request.GET['id'])
        group = Group.objects.get(groupinstanceid=id)

        where = ['groupmembers.memberaccid = users.mcid',
                 'groupmembers.groupinstanceid = groupinstances.groupinstanceid',
                 'groupinstances.groupinstanceid = %d' % id]

        admin_where = ['groupadmins.adminaccid = users.mcid',
                       'groupadmins.groupinstanceid = groupinstances.groupinstanceid',
                       'groupinstances.groupinstanceid = %d' % id]
	try:
	    practice = Practice.objects.get(providergroupid = id)
	    worklist = Practiceccrevent.objects.filter(practiceid = practice.practiceid, ViewStatus='VISIBLE')
	except Practice.DoesNotExist:
	    practice = None
	    worklist = []

        users = User.objects.extra(where=where,
                                   tables = ['groupmembers', 'groupinstances'])

        owners = User.objects.extra(where=admin_where,
                                   tables = ['groupadmins', 'groupinstances'])

        owner = None
        if(len(owners)>0):
                owner = owners[0]

        return render_to_response('groups/group.html',
                                  default_context(request, group=group,
			                          practice = practice,
			                          worklist = worklist,
                                                  users = users,
                                                  owner = owner))
    except KeyError:
        return render_to_response('groups/query.html',
                                  default_context(request))

@login_required
def delete_wl_entry_req(request):
    pid = int(request.REQUEST['pid'])

    practice = Practice.objects.get(practiceid = pid)

    if request.POST:
	guid = request.POST['guid']

	if 'delete' in request.POST or 'delete.x' in request.POST:
	    sql_execute("DELETE FROM practiceccrevents WHERE Guid=%s AND practiceid=%s",
			guid, pid)
	return HttpResponseRedirect('group?id=%d' % practice.providergroupid.groupinstanceid)

    guid = request.GET['guid']
    event = Practiceccrevent.objects.get(Guid=guid, practiceid=pid)

    return render_to_response('groups/delete_wl_entry.html',
                              default_context(request, entry=entry,
                                              practice=practice))

@login_required
def remove_from_group_req(request):
    mcid = request.REQUEST['mcid']
    group = request.REQUEST['group']

    if 'delete' in request.POST or 'delete.x' in request.POST:
	sql_execute("DELETE FROM groupmembers " + \
	            "WHERE groupinstanceid=%s AND memberaccid=%s;",
	            int(group), mcid)

    return HttpResponseRedirect("group?id=" + group)

class CreateForm(forms.Form):
    owner_mcid = forms.RegexField(VALID_MCID)
    id = forms.CharField(widget = forms.HiddenInput(),
                         required = False)

@login_required
def wiz_create_req(request):
    id = request.REQUEST.get('id', '')
    action = 'wiz_create?id=%s' % id

    if id:
        g = Group.objects.get(groupinstanceid = id)
    else:
        g = None

    queryset = all_query_set()

    if 'search' in request.POST or 'search.x' in request.POST:
        queryset = search_query_set(request.POST['q'])
        form = CreateForm(initial = request.POST)

    elif request.POST:
        form = CreateForm(request.POST)

        if form.is_valid():
            mcid = normalize_mcid(form.clean_data['owner_mcid'])

            if g:
                p = Practice.objects.get(practiceid = g.parentid)
                p.accid_id = g.accid_id = mcid

                p.save()
            else:
                g = create_group('New Group', mcid,
                                 'https://' + request.META['SERVER_NAME'])

            g.save()

    	    return HttpResponseRedirect('wiz_name?id=%d' % g.groupinstanceid)
    else:
        initial = dict(id = id)

        if 'mcid' in request.REQUEST:
            initial['owner_mcid'] = pretty_mcid(request.REQUEST['mcid'])
	elif g:
	    initial['owner_mcid'] = pretty_mcid(g.accid_id)

        form = CreateForm(initial = initial)

    return object_list(request, queryset = queryset,
                       template_name = 'groups/wiz_1create.html',
                       paginate_by = 20,
                       extra_context = default_context(request,
                                                       form=form,
                                                       action=action))

class CreateNameForm(forms.Form):
    name = forms.CharField()
    id = forms.CharField(widget = forms.HiddenInput())
    
@login_required
def wiz_name_req(request):
    id = request.REQUEST['id']
    g = Group.objects.get(groupinstanceid = id)

    if request.POST:
        form = CreateNameForm(request.POST)

        if form.is_valid():
            p = Practice.objects.get(practiceid = g.parentid)
            g.name = p.practicename = form.clean_data['name']

            g.save()
            p.save()

            if 'prev' in request.POST:
                url = 'wiz_create?id=%d&mcid=%s' % (g.groupinstanceid, g.accid.mcid)
            else:
                url = 'wiz_users?id=%d' % g.groupinstanceid

            return HttpResponseRedirect(url)

    else:
        form = CreateNameForm(dict(name = g.name,
                                id = g.groupinstanceid))

    return render_to_response('groups/wiz_2name.html',
                              default_context(request, form=form, id=id))

@login_required
def wiz_users_req(request):
    id = request.REQUEST['id']

    return add_users(request,
		     this_action = 'wiz_users?id=%s' % id,
		     next_action = '.',
		     template = 'groups/wiz_3users.html')

@login_required
def add_user_req(request):
    id = request.REQUEST['id']
    action = 'add_user?id=%s' % id

    return add_users(request,
		     this_action = 'add_user?id=%s' % id,
		     next_action = 'group?id=%s' % id,
		     template='groups/adduser.html')

def add_users(request, this_action, next_action, template):
    id = request.REQUEST['id'].strip()
    group = Group.objects.get(groupinstanceid=int(id))

    context = default_context(request, action=this_action, group=group, id=id)

    if 'q' in request.REQUEST:
	q = request.REQUEST['q']
	qs = search_query_set(q)
	context['q'] = q
    else:
	qs = all_query_set()

    if 'mcid' in request.POST:
        mcid = normalize_mcid(request.POST['mcid'])

        if 'remove.x' in request.POST:
            sql_execute("DELETE FROM groupmembers " + \
                        "WHERE groupinstanceid = %s AND memberaccid = %s",
                        int(id), mcid)
        elif 'add.x' in request.POST:
	    # Only one group per user
	    sql_execute("DELETE FROM groupmembers " + \
			"wHERE memberaccid = %s", mcid)

            sql_execute("INSERT INTO groupmembers " + \
                        "(groupinstanceid, memberaccid) " + \
                        "VALUES (%s, %s)", int(id), mcid)

        return HttpResponseRedirect(this_action)

    if 'prev' in request.POST:
        return HttpResponseRedirect('wiz_name?id=%s' % id)

    if 'finish' in request.POST:
        return HttpResponseRedirect(next_action)

    where = ['groupmembers.memberaccid = users.mcid',
             'groupmembers.groupinstanceid = %s' % id]
    context['members'] = User.objects.extra(where = where,
                                            tables = ['groupmembers'])

    where = ['mcid NOT IN (SELECT memberaccid FROM groupmembers WHERE groupinstanceid = %s)' % id]
    select = {'groupname': 'SELECT DISTINCT(name) FROM groupinstances, groupmembers WHERE groupmembers.groupinstanceid = groupinstances.groupinstanceid AND groupmembers.memberaccid = users.mcid'}

    return object_list(request,
                       queryset = qs.extra(where = where,
					   select = select),
                       template_name = template,
                       paginate_by = 20,
                       extra_context = context)

@login_required
def delete_req(request):
    if request.POST:
	if 'delete' in request.POST or 'delete.x' in request.POST:
	    id = request.POST['id']
	    group = Group.objects.get(groupinstanceid = id)
	    delete_group(group)

	return HttpResponseRedirect('.')

    id = request.GET['id']
    group = Group.objects.get(groupinstanceid=id)

    where = ['groupmembers.memberaccid = users.mcid',
             'groupmembers.groupinstanceid = groupinstances.groupinstanceid',
             'groupinstances.groupinstanceid = %s' % id]

    users = User.objects.extra(where=where,
                               tables = ['groupmembers', 'groupinstances'])

    return render_to_response('groups/delete.html',
                              default_context(request, group = group,
                                              users = users))

class EditForm(forms.Form):
    name = forms.CharField()
    adminUrl = forms.URLField(required = False)
    memberUrl = forms.URLField(required = False)
    worklist_limit = forms.IntegerField(required = False)

@login_required
def edit_req(request):
    id = int(request.REQUEST['id'].strip())

    group = Group.objects.get(groupinstanceid = id)

    if request.POST:
	form = EditForm(request.POST)

	if form.is_valid():
	    group.name = form.clean_data['name']
	    group.adminUrl = form.clean_data['adminUrl']
	    group.memberUrl = form.clean_data['memberUrl']
	    group.worklist_limit = form.clean_data['worklist_limit']
	    group.save()

	    return submit_redirect(request, group,
				   create_redirect='wiz_create',
				   edit_redirect='edit?id=%(groupinstanceid)d')
    else:
	form = EditForm(initial = group.__dict__)

    return render_to_response('groups/edit.html',
			      default_context(request,
				              group=group,
				              form=form))
