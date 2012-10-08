# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#     * Rearrange models' order
#     * Make sure each model has one field with primary_key=True
# Feel free to rename the models, but don't rename db_table values or field names.
#
# Also note: You'll have to insert the output of 'django-admin.py sqlcustom [appname]'
# into your database.

from django.db import models

class AccountLog(models.Model):
    datetime = models.DateTimeField()
    mcid = models.TextField() # This field type is a guess.
    username = models.CharField(blank=True, maxlength=192)
    provider_id = models.IntegerField(null=True, blank=True)
    operation = models.CharField(blank=True, maxlength=48)
    class Meta:
        db_table = 'account_log'

class AccountNotifications(models.Model):
    id = models.IntegerField(primary_key=True)
    mcid = models.TextField(blank=True) # This field type is a guess.
    recipient = models.CharField(blank=True, maxlength=180)
    status = models.CharField(blank=True, maxlength=90)
    class Meta:
        db_table = 'account_notifications'

class AccountRls(models.Model):
    ar_accid = models.CharField(primary_key=True, maxlength=96)
    ar_rls_url = models.TextField()
    class Meta:
        db_table = 'account_rls'

class Addresses(models.Model):
    mcid = models.TextField(primary_key=True) # This field type is a guess.
    comment = models.CharField(primary_key=True, maxlength=765)
    address1 = models.CharField(maxlength=765)
    address2 = models.CharField(blank=True, maxlength=765)
    city = models.CharField(maxlength=192)
    state = models.CharField(maxlength=24)
    postcode = models.CharField(maxlength=48)
    country = models.TextField()
    telephone = models.CharField(blank=True, maxlength=96)
    class Meta:
        db_table = 'addresses'

class Affiliates(models.Model):
    affiliatelogo = models.CharField(maxlength=765)
    affiliateid = models.IntegerField()
    affiliatename = models.CharField(maxlength=765)
    class Meta:
        db_table = 'affiliates'

class Appeventlog(models.Model):
    accid = models.CharField(maxlength=48)
    appserviceid = models.CharField(maxlength=96)
    eventname = models.CharField(maxlength=765)
    param1 = models.CharField(maxlength=765)
    time = models.IntegerField()
    chargeclass = models.CharField(maxlength=765)
    class Meta:
        db_table = 'appeventlog'

class Appservicechargeclasses(models.Model):
    appserviceid = models.CharField(primary_key=True, maxlength=96)
    chargeclass = models.CharField(primary_key=True, maxlength=765)
    permonth = models.IntegerField()
    perclick = models.IntegerField()
    perxmtgb = models.IntegerField()
    perrcvgb = models.IntegerField()
    setup = models.IntegerField()
    perstoredgb = models.IntegerField()
    class Meta:
        db_table = 'appservicechargeclasses'

class Appservicecontracts(models.Model):
    accid = models.CharField(primary_key=True, maxlength=48)
    appserviceid = models.CharField(primary_key=True, maxlength=96)
    time = models.TextField(blank=True) # This field type is a guess.
    class Meta:
        db_table = 'appservicecontracts'

class Appservicedependencies(models.Model):
    appserviceid = models.CharField(primary_key=True, maxlength=96)
    dependson = models.CharField(primary_key=True, maxlength=96)
    class Meta:
        db_table = 'appservicedependencies'

class Appservices(models.Model):
    name = models.CharField(maxlength=765)
    serviceurl = models.CharField(maxlength=765)
    publisher = models.CharField(maxlength=765)
    description = models.CharField(maxlength=765)
    appserviceid = models.CharField(primary_key=True, maxlength=96)
    createurl = models.CharField(maxlength=765)
    removeurl = models.CharField(maxlength=765)
    viewurl = models.CharField(maxlength=765)
    builtin = models.CharField(maxlength=765)
    class Meta:
        db_table = 'appservices'

class AuthGroup(models.Model):
    id = models.IntegerField(primary_key=True)
    name = models.CharField(unique=True, maxlength=240)
    class Meta:
        db_table = 'auth_group'

class AuthGroupPermissions(models.Model):
    id = models.IntegerField(primary_key=True)
    group_id = models.IntegerField(unique=True)
    permission_id = models.IntegerField(unique=True)
    class Meta:
        db_table = 'auth_group_permissions'

class AuthMessage(models.Model):
    id = models.IntegerField(primary_key=True)
    user_id = models.IntegerField()
    message = models.TextField()
    class Meta:
        db_table = 'auth_message'

class AuthPermission(models.Model):
    id = models.IntegerField(primary_key=True)
    name = models.CharField(maxlength=150)
    content_type_id = models.IntegerField()
    codename = models.CharField(unique=True, maxlength=300)
    class Meta:
        db_table = 'auth_permission'

class AuthUser(models.Model):
    id = models.IntegerField(primary_key=True)
    username = models.CharField(unique=True, maxlength=90)
    first_name = models.CharField(maxlength=90)
    last_name = models.CharField(maxlength=90)
    email = models.CharField(maxlength=225)
    password = models.CharField(maxlength=384)
    is_staff = models.IntegerField()
    is_active = models.IntegerField()
    is_superuser = models.IntegerField()
    last_login = models.DateTimeField()
    date_joined = models.DateTimeField()
    class Meta:
        db_table = 'auth_user'

class AuthUserGroups(models.Model):
    id = models.IntegerField(primary_key=True)
    user_id = models.IntegerField(unique=True)
    group_id = models.IntegerField(unique=True)
    class Meta:
        db_table = 'auth_user_groups'

class AuthUserUserPermissions(models.Model):
    id = models.IntegerField(primary_key=True)
    user_id = models.IntegerField(unique=True)
    permission_id = models.IntegerField(unique=True)
    class Meta:
        db_table = 'auth_user_user_permissions'

class AuthenticationToken(models.Model):
    at_id = models.IntegerField(primary_key=True)
    at_token = models.CharField(blank=True, maxlength=120)
    at_secret = models.CharField(blank=True, maxlength=40)
    at_account_id = models.CharField(blank=True, maxlength=96)
    at_create_date_time = models.DateTimeField()
    class Meta:
        db_table = 'authentication_token'

class Ccdata(models.Model):
    accid = models.CharField(maxlength=48)
    nikname = models.CharField(maxlength=48)
    name = models.CharField(maxlength=765)
    addr = models.CharField(maxlength=765)
    city = models.CharField(maxlength=765)
    state = models.CharField(maxlength=765)
    zip = models.CharField(maxlength=48)
    cardnum = models.CharField(maxlength=48)
    expdate = models.CharField(maxlength=48)
    class Meta:
        db_table = 'ccdata'

class Ccrevents(models.Model):
    PatientGivenName = models.CharField(maxlength=192)
    PatientFamilyName = models.CharField(maxlength=192)
    PatientIdentifier = models.CharField(maxlength=192)
    PatientIdentifierSource = models.CharField(maxlength=192)
    Guid = models.CharField(maxlength=192)
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
    class Meta:
        db_table = 'ccrevents'

class Ccrlog(models.Model):
    id = models.IntegerField(primary_key=True)
    accid = models.TextField() # This field type is a guess.
    idp = models.CharField(maxlength=765)
    guid = models.CharField(maxlength=192)
    status = models.CharField(maxlength=36)
    date = models.DateTimeField()
    src = models.CharField(maxlength=765)
    dest = models.CharField(maxlength=765)
    subject = models.CharField(maxlength=765)
    einfo = models.TextField(blank=True)
    tracking = models.CharField(blank=True, maxlength=36)
    merge_status = models.CharField(blank=True, maxlength=96)
    class Meta:
        db_table = 'ccrlog'

class Ccstatus(models.Model):
    time = models.DateTimeField()
    authcode = models.CharField(maxlength=765)
    avsdata = models.CharField(maxlength=765)
    hostcode = models.CharField(maxlength=765)
    pnref = models.CharField(maxlength=765)
    respmsg = models.CharField(maxlength=765)
    csmatch = models.CharField(maxlength=765)
    custid = models.CharField(maxlength=765)
    amount = models.CharField(maxlength=765)
    user1 = models.CharField(maxlength=765)
    user2 = models.CharField(maxlength=765)
    user3 = models.CharField(maxlength=765)
    user4 = models.CharField(maxlength=765)
    user5 = models.CharField(maxlength=765)
    user6 = models.CharField(maxlength=765)
    user7 = models.CharField(maxlength=765)
    user8 = models.CharField(maxlength=765)
    user9 = models.CharField(maxlength=765)
    type = models.CharField(maxlength=765)
    class Meta:
        db_table = 'ccstatus'

class Clicktracks(models.Model):
    requesturi = models.CharField(maxlength=765)
    time = models.DateTimeField()
    id = models.IntegerField(primary_key=True)
    referer = models.CharField(maxlength=765)
    class Meta:
        db_table = 'clicktracks'

class Cover(models.Model):
    cover_id = models.IntegerField(primary_key=True)
    cover_account_id = models.CharField(maxlength=60)
    cover_notification = models.CharField(blank=True, maxlength=360)
    cover_encrypted_pin = models.CharField(blank=True, maxlength=192)
    cover_provider_code = models.CharField(blank=True, maxlength=90)
    class Meta:
        db_table = 'cover'

class Cxpproblems(models.Model):
    id = models.IntegerField(primary_key=True)
    timestamp = models.DateTimeField()
    sender = models.CharField(maxlength=765)
    version = models.CharField(maxlength=765)
    problemdata = models.TextField()
    trackingnumber = models.CharField(maxlength=765)
    pin = models.CharField(maxlength=765)
    description = models.CharField(maxlength=765)
    useragent = models.CharField(maxlength=765)
    email = models.CharField(maxlength=765)
    class Meta:
        db_table = 'cxpproblems'

class DjangoAdminLog(models.Model):
    id = models.IntegerField(primary_key=True)
    action_time = models.DateTimeField()
    user_id = models.IntegerField()
    content_type_id = models.IntegerField(null=True, blank=True)
    object_id = models.TextField(blank=True)
    object_repr = models.CharField(maxlength=600)
    action_flag = models.IntegerField()
    change_message = models.TextField()
    class Meta:
        db_table = 'django_admin_log'

class DjangoContentType(models.Model):
    id = models.IntegerField(primary_key=True)
    name = models.CharField(maxlength=300)
    app_label = models.CharField(unique=True, maxlength=300)
    model = models.CharField(unique=True, maxlength=300)
    class Meta:
        db_table = 'django_content_type'

class DjangoSession(models.Model):
    session_key = models.CharField(primary_key=True, maxlength=120)
    session_data = models.TextField()
    expire_date = models.DateTimeField()
    class Meta:
        db_table = 'django_session'

class DjangoSite(models.Model):
    id = models.IntegerField(primary_key=True)
    domain = models.CharField(maxlength=300)
    name = models.CharField(maxlength=150)
    class Meta:
        db_table = 'django_site'

class Document(models.Model):
    id = models.IntegerField(primary_key=True)
    guid = models.CharField(maxlength=192)
    creation_time = models.DateTimeField()
    rights_time = models.DateTimeField()
    encrypted_hash = models.CharField(blank=True, maxlength=192)
    attributions = models.CharField(blank=True, maxlength=765)
    storage_account_id = models.CharField(blank=True, maxlength=96)
    class Meta:
        db_table = 'document'

class DocumentLocation(models.Model):
    document_id = models.IntegerField()
    id = models.IntegerField(unique=True)
    node_node_id = models.IntegerField()
    integrity_check = models.DateTimeField()
    integrity_status = models.IntegerField(null=True, blank=True)
    encrypted_key = models.CharField(blank=True, maxlength=192)
    copy_number = models.IntegerField(null=True, blank=True)
    class Meta:
        db_table = 'document_location'

class DocumentType(models.Model):
    dt_id = models.IntegerField(primary_key=True)
    dt_account_id = models.CharField(maxlength=60)
    dt_type = models.CharField(maxlength=90)
    dt_tracking_number = models.CharField(maxlength=60)
    dt_privacy_level = models.CharField(maxlength=90)
    dt_guid = models.CharField(blank=True, maxlength=120)
    dt_create_date_time = models.DateTimeField()
    dt_comment = models.CharField(blank=True, maxlength=765)
    dt_notification_status = models.CharField(blank=True, maxlength=90)
    class Meta:
        db_table = 'document_type'

class Downloaders(models.Model):
    email = models.CharField(maxlength=765)
    time = models.DateTimeField()
    id = models.IntegerField(primary_key=True)
    remoteaddr = models.CharField(maxlength=765)
    class Meta:
        db_table = 'downloaders'

class Emailstatus(models.Model):
    status = models.CharField(maxlength=765)
    time = models.DateTimeField()
    requesturi = models.CharField(maxlength=765)
    sendermcid = models.CharField(maxlength=765)
    rcvremail = models.CharField(maxlength=765)
    template = models.CharField(maxlength=765)
    arga = models.CharField(primary_key=True, maxlength=765)
    argb = models.CharField(maxlength=765)
    argc = models.CharField(maxlength=765)
    argd = models.CharField(maxlength=765)
    arge = models.CharField(maxlength=765)
    argf = models.CharField(maxlength=765)
    argg = models.CharField(maxlength=765)
    message = models.CharField(maxlength=765)
    class Meta:
        db_table = 'emailstatus'

class ExternalUsers(models.Model):
    mcid = models.TextField() # This field type is a guess.
    provider_id = models.IntegerField(primary_key=True)
    username = models.CharField(primary_key=True, maxlength=192)
    class Meta:
        db_table = 'external_users'

class ExternalApplication(models.Model):
    ea_id = models.IntegerField(primary_key=True)
    ea_key = models.CharField(maxlength=255) 
    ea_secret = models.CharField(maxlength=40) 
    ea_code = models.CharField(maxlength=30) 
    ea_name = models.CharField(maxlength=255) 
    ea_active_status = models.CharField(maxlength=30) 
    ea_ip_address = models.CharField(maxlength=60) 
    ea_create_date_time = models.DateTimeField()
    class Meta:
        db_table = 'external_application'

class Faxstatus(models.Model):
    xmtTime = models.DateTimeField()
    xmtService = models.CharField(maxlength=765)
    xmtTransmissionID = models.CharField(maxlength=765)
    xmtDOCID = models.CharField(maxlength=765)
    xmtStatusCode = models.CharField(maxlength=765)
    xmtStatusDescription = models.CharField(maxlength=765)
    xmtErrorLevel = models.CharField(maxlength=765)
    xmtErrorMessage = models.CharField(maxlength=765)
    faxnum = models.CharField(maxlength=765)
    filespec = models.CharField(maxlength=765)
    filetype = models.CharField(maxlength=765)
    dispCompletionDate = models.CharField(maxlength=765)
    dispFaxStatus = models.CharField(maxlength=765)
    dispRecipientCSID = models.CharField(maxlength=765)
    dispDuration = models.CharField(maxlength=765)
    dispPagesSent = models.CharField(maxlength=765)
    dispNumberOfRetries = models.CharField(maxlength=765)
    class Meta:
        db_table = 'faxstatus'

class ForensicLog(models.Model):
    id = models.IntegerField(primary_key=True)
    creation_time = models.DateTimeField()
    event_type = models.CharField(blank=True, maxlength=96)
    event_description = models.CharField(blank=True, maxlength=192)
    event_status = models.IntegerField(null=True, blank=True)
    rights_id = models.IntegerField(null=True, blank=True)
    rights_table = models.CharField(blank=True, maxlength=48)
    class Meta:
        db_table = 'forensic_log'

class GroupNode(models.Model):
    node_node_id = models.IntegerField()
    groups_group_number = models.IntegerField()
    class Meta:
        db_table = 'group_node'

class Groupinstances(models.Model):
    groupinstanceid = models.IntegerField(primary_key=True)
    grouptypeid = models.IntegerField()
    name = models.CharField(maxlength=765)
    groupLogo = models.CharField(maxlength=765)
    adminUrl = models.CharField(maxlength=765)
    memberUrl = models.CharField(maxlength=765)
    parentid = models.IntegerField()
    accid = models.CharField(blank=True, maxlength=96)
    createdatetime = models.DateTimeField()
    class Meta:
        db_table = 'groupinstances'

class Groupmembers(models.Model):
    groupinstanceid = models.IntegerField(primary_key=True)
    memberaccid = models.TextField() # This field type is a guess.
    comment = models.CharField(maxlength=765)
    class Meta:
        db_table = 'groupmembers'

class Groupproperties(models.Model):
    groupinstanceid = models.IntegerField(primary_key=True)
    property = models.CharField(primary_key=True, maxlength=765)
    value = models.CharField(maxlength=765)
    comment = models.CharField(maxlength=765)
    class Meta:
        db_table = 'groupproperties'

class Groups(models.Model):
    group_number = models.IntegerField(primary_key=True)
    name = models.CharField(blank=True, maxlength=192)
    location = models.CharField(blank=True, maxlength=192)
    group_type = models.CharField(blank=True, maxlength=96)
    admin_id = models.CharField(blank=True, maxlength=96)
    point_of_contact_id = models.CharField(blank=True, maxlength=96)
    class Meta:
        db_table = 'groups'

class Grouptypes(models.Model):
    grouptypeid = models.IntegerField(primary_key=True)
    name = models.CharField(maxlength=96)
    infoUrl = models.CharField(maxlength=765)
    rulesUrl = models.CharField(maxlength=765)
    supportPageUrl = models.CharField(maxlength=765)
    internalgroup = models.IntegerField()
    class Meta:
        db_table = 'grouptypes'

class Hipaa(models.Model):
    tracking_number = models.CharField(primary_key=True, maxlength=36)
    creation_time = models.DateTimeField()
    hpin = models.CharField(blank=True, maxlength=765)
    a1 = models.CharField(blank=True, maxlength=96)
    a2 = models.CharField(blank=True, maxlength=96)
    a3 = models.CharField(blank=True, maxlength=96)
    s1 = models.CharField(blank=True, maxlength=765)
    s2 = models.CharField(blank=True, maxlength=765)
    s3 = models.CharField(blank=True, maxlength=765)
    s4 = models.CharField(blank=True, maxlength=765)
    class Meta:
        db_table = 'hipaa'

class HipaaTrace(models.Model):
    tracking_number = models.CharField(primary_key=True, maxlength=36)
    creation_time = models.DateTimeField()
    hpin = models.CharField(blank=True, maxlength=765)
    a1 = models.CharField(blank=True, maxlength=96)
    a2 = models.CharField(blank=True, maxlength=96)
    a3 = models.CharField(blank=True, maxlength=96)
    s1 = models.CharField(blank=True, maxlength=765)
    s2 = models.CharField(blank=True, maxlength=765)
    s3 = models.CharField(blank=True, maxlength=765)
    s4 = models.CharField(blank=True, maxlength=765)
    class Meta:
        db_table = 'hipaa_trace'

class IdentityProviders(models.Model):
    id = models.IntegerField(primary_key=True)
    source_id = models.CharField(maxlength=120)
    name = models.CharField(maxlength=240)
    logo = models.CharField(blank=True, maxlength=192)
    domain = models.CharField(blank=True, maxlength=192)
    logouturl = models.CharField(blank=True, maxlength=384)
    website = models.CharField(blank=True, maxlength=192)
    class Meta:
        db_table = 'identity_providers'

class Inbox(models.Model):
    inbox_id = models.IntegerField(primary_key=True)
    inbox_name = models.CharField(blank=True, maxlength=135)
    inbox_type = models.IntegerField(null=True, blank=True)
    inbox_location = models.CharField(blank=True, maxlength=600)
    class Meta:
        db_table = 'inbox'

class Inboxes(models.Model):
    groups_group_number = models.IntegerField()
    inbox_id = models.IntegerField()
    user_medcommons_user_id = models.CharField(maxlength=96)
    descriptor = models.CharField(blank=True, maxlength=384)
    descriptor_type = models.IntegerField(null=True, blank=True)
    authentication = models.IntegerField(null=True, blank=True)
    class Meta:
        db_table = 'inboxes'

class Log(models.Model):
    content = models.CharField(maxlength=765)
    time = models.TextField() # This field type is a guess.
    class Meta:
        db_table = 'log'

class LogEntries(models.Model):
    id = models.IntegerField(primary_key=True)
    datetime = models.DateTimeField()
    source_id = models.IntegerField()
    severity = models.CharField(maxlength=3)
    message = models.CharField(maxlength=768)
    class Meta:
        db_table = 'log_entries'

class LogSources(models.Model):
    id = models.IntegerField(primary_key=True)
    name = models.CharField(maxlength=48)
    path = models.CharField(maxlength=768)
    class Meta:
        db_table = 'log_sources'

class Node(models.Model):
    node_id = models.IntegerField(primary_key=True)
    admin_id = models.CharField(blank=True, maxlength=96)
    e_key = models.IntegerField(null=True, blank=True)
    m_key = models.IntegerField(null=True, blank=True)
    display_name = models.CharField(blank=True, maxlength=192)
    hostname = models.CharField(blank=True, maxlength=192)
    fixed_ip = models.CharField(blank=True, maxlength=90)
    node_type = models.IntegerField(null=True, blank=True)
    creation_time = models.DateTimeField()
    logging_server = models.CharField(blank=True, maxlength=384)
    class Meta:
        db_table = 'node'

class NodeRight(models.Model):
    node_node_id = models.IntegerField()
    groups_group_number = models.IntegerField()
    rights = models.CharField(blank=True, maxlength=96)
    class Meta:
        db_table = 'node_right'

class Personas(models.Model):
    accid = models.TextField(primary_key=True) # This field type is a guess.
    persona = models.CharField(primary_key=True, maxlength=96)
    personanum = models.IntegerField()
    personagif = models.CharField(maxlength=765)
    isactive = models.IntegerField()
    phone = models.CharField(maxlength=765)
    exposephone = models.IntegerField()
    inheritphone = models.IntegerField()
    myid = models.CharField(maxlength=765)
    exposemyid = models.IntegerField()
    inheritmyid = models.IntegerField()
    email = models.CharField(maxlength=765)
    exposeemail = models.IntegerField()
    inheritemail = models.IntegerField()
    name = models.CharField(maxlength=765)
    exposename = models.IntegerField()
    inheritname = models.IntegerField()
    address = models.CharField(maxlength=765)
    exposeaddress = models.IntegerField()
    inheritaddress = models.IntegerField()
    dob = models.CharField(maxlength=765)
    exposedob = models.IntegerField()
    inheritdob = models.IntegerField()
    sex = models.CharField(maxlength=765)
    exposesex = models.IntegerField()
    inheritsex = models.IntegerField()
    ccrsectionconsents = models.CharField(maxlength=765)
    qualitativeandmultichoice = models.CharField(maxlength=765)
    distancecalcmin = models.CharField(maxlength=765)
    nooldccrs = models.IntegerField()
    excluderefs = models.IntegerField()
    requiresms = models.IntegerField()
    promptmissing = models.IntegerField()
    mergeccr = models.IntegerField()
    class Meta:
        db_table = 'personas'

class Practiceccrevents(models.Model):
    practiceid = models.IntegerField(null=True, blank=True)
    PatientGivenName = models.CharField(maxlength=192)
    PatientFamilyName = models.CharField(maxlength=192)
    PatientIdentifier = models.CharField(maxlength=192)
    PatientIdentifierSource = models.CharField(maxlength=192)
    Guid = models.CharField(maxlength=192)
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

class Rights(models.Model):
    rights_id = models.IntegerField(primary_key=True)
    groups_group_number = models.IntegerField()
    account_id = models.CharField(blank=True, maxlength=96)
    document_id = models.IntegerField(null=True, blank=True)
    rights = models.CharField(maxlength=96)
    creation_time = models.DateTimeField()
    expiration_time = models.DateTimeField()
    rights_time = models.DateTimeField()
    accepted_status = models.CharField(blank=True, maxlength=90)
    storage_account_id = models.CharField(blank=True, maxlength=96)
    active_status = models.CharField(maxlength=96)
    class Meta:
        db_table = 'rights'

class Rssheadlines(models.Model):
    id = models.IntegerField(primary_key=True)
    sourceid = models.IntegerField()
    title = models.CharField(maxlength=765)
    link = models.CharField(maxlength=765)
    description = models.TextField()
    pubDate = models.CharField(maxlength=765)
    time = models.TextField() # This field type is a guess.
    class Meta:
        db_table = 'rssheadlines'

class Rsssources(models.Model):
    id = models.IntegerField(primary_key=True)
    title = models.CharField(maxlength=765)
    link = models.CharField(maxlength=765)
    copyright = models.CharField(maxlength=765)
    language = models.CharField(maxlength=765)
    description = models.CharField(maxlength=765)
    webMaster = models.CharField(maxlength=765)
    managingEditor = models.CharField(maxlength=765)
    rssversion = models.CharField(maxlength=765)
    class Meta:
        db_table = 'rsssources'

class Servers(models.Model):
    id = models.IntegerField(primary_key=True)
    url = models.CharField(maxlength=384)
    class Meta:
        db_table = 'servers'

class Todir(models.Model):
    id = models.IntegerField(primary_key=True)
    groupid = models.IntegerField()
    xid = models.CharField(maxlength=765)
    alias = models.CharField(maxlength=765)
    contactlist = models.CharField(maxlength=765)
    sharedgroup = models.IntegerField()
    pinstate = models.IntegerField()
    accid = models.CharField(maxlength=48)
    class Meta:
        db_table = 'todir'

class TrackingNumber(models.Model):
    tracking_number = models.CharField(primary_key=True, maxlength=192)
    rights_id = models.IntegerField()
    encrypted_pin = models.CharField(blank=True, maxlength=192)
    class Meta:
        db_table = 'tracking_number'

class User(models.Model):
    medcommons_user_id = models.CharField(primary_key=True, maxlength=96)
    telephone_number = models.CharField(blank=True, maxlength=192)
    email_address = models.CharField(blank=True, maxlength=192)
    credential = models.TextField(blank=True)
    creation_time = models.DateTimeField()
    last_access_time = models.DateTimeField()
    ui_role = models.IntegerField(null=True, blank=True)
    public_key = models.CharField(blank=True, maxlength=765)
    serial = models.CharField(blank=True, maxlength=96)
    hpass = models.CharField(blank=True, maxlength=765)
    gateway1 = models.CharField(blank=True, maxlength=765)
    gateway2 = models.CharField(blank=True, maxlength=765)
    identity_provider = models.CharField(blank=True, maxlength=765)
    cert_url = models.CharField(blank=True, maxlength=765)
    status = models.CharField(blank=True, maxlength=765)
    name = models.CharField(blank=True, maxlength=765)
    cert_checked = models.DateTimeField()
    wired_ipaddress = models.CharField(blank=True, maxlength=765)
    wired_useragent = models.CharField(blank=True, maxlength=765)
    class Meta:
        db_table = 'user'

class UserGroup(models.Model):
    user_medcommons_user_id = models.CharField(maxlength=96)
    groups_group_number = models.IntegerField()
    user_role_with_group = models.CharField(maxlength=96)
    added_by_id = models.CharField(blank=True, maxlength=96)
    class Meta:
        db_table = 'user_group'

class Users(models.Model):
    mcid = models.TextField(primary_key=True) # This field type is a guess.
    email = models.CharField(blank=True, maxlength=192)
    sha1 = models.CharField(blank=True, maxlength=120)
    server_id = models.IntegerField()
    since = models.DateTimeField()
    first_name = models.CharField(blank=True, maxlength=96)
    last_name = models.CharField(blank=True, maxlength=96)
    mobile = models.CharField(blank=True, maxlength=192)
    smslogin = models.IntegerField(null=True, blank=True)
    updatetime = models.IntegerField()
    ccrlogupdatetime = models.IntegerField()
    chargeclass = models.CharField(blank=True, maxlength=765)
    rolehack = models.CharField(blank=True, maxlength=765)
    affiliationgroupid = models.IntegerField(null=True, blank=True)
    startparams = models.CharField(blank=True, maxlength=765)
    stylesheetUrl = models.CharField(blank=True, maxlength=765)
    picslayout = models.CharField(blank=True, maxlength=765)
    photoUrl = models.CharField(blank=True, maxlength=765)
    acctype = models.CharField(blank=True, maxlength=765)
    persona = models.CharField(blank=True, maxlength=765)
    validparams = models.CharField(blank=True, maxlength=765)
    interests = models.CharField(blank=True, maxlength=765)
    email_verified = models.DateTimeField(null=True, blank=True)
    mobile_verified = models.DateTimeField(null=True, blank=True)
    skey = models.TextField(blank=True)
    class Meta:
        db_table = 'users'

class WorklistQueue(models.Model):
    worklist_id = models.IntegerField(primary_key=True)
    groups_group_number = models.IntegerField()
    user_medcommons_user_id = models.CharField(maxlength=96)
    description = models.CharField(blank=True, maxlength=96)
    class Meta:
        db_table = 'worklist_queue'

class WorklistQueueItem(models.Model):
    rights_id = models.IntegerField()
    worklist_queue_worklist_id = models.IntegerField()
    placed_in_queue = models.DateTimeField()
    order_number = models.IntegerField(null=True, blank=True)
    priority = models.IntegerField(null=True, blank=True)
    class Meta:
        db_table = 'worklist_queue_item'

