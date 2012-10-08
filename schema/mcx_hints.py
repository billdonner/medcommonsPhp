{'document.encrypted_key': DROP,
 'rights.accepted_time': DROP,
 'ccrlog.samplidp': DROP,
 'rights.user_medcommons_user_id': 'rights.account_id',
 'rights.document_ID': 'rights.document_id',
 'todir.time': DROP,
 'todir.contact': DROP,
 'todir.ctx': DROP,

 'users.trackerdb': DROP,
 'group_account_access': DROP,
 'account_group_users': DROP,
 'account_group': DROP,

 'groupccrevents':  'practiceccrevents',
 'practiceccrevents.groupinstanceid': 'practiceid',

 'identity_providers.logo': DROP,
 'practice.patientgroupid': DROP,
 'users.amazon_pid': 'amazon_product_token',

 # 104_drop_unused_columns_mcx.sql
 'document.attributions': DROP,
 'rights.accepted_status': DROP,
 'rights.groups_group_number': DROP,
}
