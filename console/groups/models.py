# vim: tabstop=4 softtabstop=4 shiftwidth=4 expandtab

from django.db import models

from users.models import User
from utils import sql_execute
import SOAPpy

class Group(models.Model):
    groupinstanceid = models.AutoField('groupinstanceid', primary_key=True)
    
    models.IntegerField(primary_key=True)
    grouptypeid = models.IntegerField()
    name = models.CharField(maxlength=765)
    groupLogo = models.CharField(maxlength=765)
    adminUrl = models.CharField(maxlength=765)
    memberUrl = models.CharField(maxlength=765)
    parentid = models.IntegerField(default=0)
    accid = models.ForeignKey(User, db_column='accid')

    createdatetime = models.DateTimeField(auto_now_add = True)

    worklist_limit = models.IntegerField(null=True)

    class Meta:
        db_table = 'groupinstances'

    class Admin:
        pass

class Practice(models.Model):
    practiceid = models.AutoField('practiceid', primary_key=True)

    practicename = models.CharField(maxlength=96)
    providergroupid = models.ForeignKey(Group, db_column='providergroupid', null=True)
    practiceRlsUrl = models.CharField(maxlength=765)
    practiceLogoUrl = models.CharField(maxlength=765)
    accid = models.ForeignKey(User, db_column='accid')
    class Meta:
        db_table = 'practice'


# ss: I am not quite clever enough to know how
# to put these in a sensible common place
# so that users/models.py and this file can
# both reference it.
#URL = 'http://yowie:9080/identity/ws/AccountCreationServiceImpl';
URL = 'http://mcid.internal:1080/mcid'
NS = 'http://www.medcommons.net/mcid'
mcid_generator = SOAPpy.SOAPProxy(URL, namespace=NS)

def create_group(name, mcid, url_root):
    """Create a group and a related practice.

    name is the name of the group and practice
    mcid is the account id of the owner
    url_root is the base URL of the for 'https://host'
    """
   
    group_mcid = mcid_generator.next_mcid()

    g = Group()
    g.name = name
    g.grouptypeid = 0
    g.accid_id = group_mcid
    g.save()

    p = Practice()
    p.providergroupid = g
    p.practicename = name
    p.accid_id = group_mcid
    p.save()

    p.practiceRlsUrl = url_root + '/acct/ws/R.php?pid=%d' % p.practiceid
    p.save()

    g.parentid = p.practiceid

    sql_execute("INSERT INTO groupmembers (groupinstanceid, memberaccid) " + \
                "VALUES (%s, %s)", int(g.groupinstanceid), mcid)

    sql_execute("INSERT INTO groupadmins (groupinstanceid, adminaccid, comment) " + \
                "VALUES (%s, %s, %s)", int(g.groupinstanceid), mcid, "")
    return g

def delete_group(group):
    i = group.groupinstanceid
    sql_execute("DELETE FROM groupmembers WHERE groupinstanceid = %s", i)
    sql_execute("DELETE FROM practice WHERE providergroupid = %s", i)
    group.delete()

class Practiceccrevent(models.Model):
    practiceid = models.ForeignKey(Practice, db_column='practiceid')
    PatientGivenName = models.CharField(maxlength=192)
    PatientFamilyName = models.CharField(maxlength=192)
    PatientIdentifier = models.CharField(maxlength=192)
    PatientIdentifierSource = models.CharField(maxlength=192)
    Guid = models.CharField(maxlength=192, primary_key=True)
    Purpose = models.CharField(maxlength=192)
    SenderProviderId = models.CharField(maxlength=192)
    ReceiverProviderId = models.CharField(maxlength=192)
    DOB = models.CharField(maxlength=192)
    CXPServerURL = models.CharField(maxlength=765)
    CXPServerVendor = models.CharField(maxlength=765)
    ViewerURL = models.CharField(maxlength=765)
    Comment = models.CharField(maxlength=765)
    CreationDateTime = models.IntegerField()
    ConfirmationCode = models.CharField(maxlength=192)
    RegistrySecret = models.CharField(maxlength=192)
    PatientSex = models.CharField(blank=True, maxlength=192)
    PatientAge = models.CharField(blank=True, maxlength=192)
    Status = models.CharField(blank=True, maxlength=90)
    ViewStatus = models.CharField(blank=True, maxlength=60)
    class Meta:
        db_table = 'practiceccrevents'



class Groupadmin(models.Model):
    groupinstanceid = models.IntegerField(primary_key=True)
    adminaccid = models.ForeignKey(User, db_column = 'adminaccid')
                #models.TextField() # This field type is a guess.
    comment = models.CharField(maxlength=765)
    class Meta:
        db_table = 'groupadmins'
