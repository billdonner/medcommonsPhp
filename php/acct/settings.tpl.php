<?
  require_once "mc.inc.php";

  global $acAmazonProducts;
  global $acOpenIDMode;
  global $p;
  $p = $page;

  // utility function to select correct page
  function page($x) {
    global $p;
    error_log("checking page $x == $p");
    if(isset($p) && ($x == $p))
      return " class='selected'";
    else
      return "";
  }
?>
<!-- Sam Skin CSS for TabView -->
<link rel="stylesheet" type="text/css" href="/zip/yui/2.6.0/tabview/assets/skins/sam/tabview.css">

<style type='text/css'>
.yui-skin-sam .yui-navset .yui-content {
  background-color: #f8f8f8 !important;
  padding: 10px;
}
.yui-skin-sam .yui-navset li a em {
  color: #333;
}
.yui-nav {
  border-color: #828076 !important;
}
.yui-skin-sam .yui-navset li.selected a em {
  color: white;
  font-weight: bold;
}
#wrapper .yui-navset a:hover
{
    text-decoration: none;
}

table.creditsTable {
  text-align: center;
  border-collapse: collapse;
  background-color: white;
  margin-top: 20px;
}
table.creditsTable tr th {
  border-bottom-style: solid;
  border-bottom-width: 1px;
  border-bottom-color: #333;
  color: #333;
}

table.creditsTable tr th, 
table.creditsTable tr td {
  text-align: center;
  padding: 5px 25px;
}
</style>


<div id='content'>
<h2>Personal Profile</h2>
<?/*
  This page uses a YUI tab control that is built from the existing markup.
  Each tab is represented by a child <div> of class 'yui-content'
 */?>
<div id='profileTabs' class='yui-navset' style='display:none;'>
  <ul class='yui-nav'>
      <li <?=page('personalDetails')?> ><a href="#personalDetails"><em>Personal&nbsp;Details</em></a></li>
      <li <?=page('password')?> ><a href="#password"><em>Password</em></a></li>
      <li <?=page('identities')?> ><a href="#identities"><em>Identities</em></a></li>
      <li <?=page('personalAccount')?> ><a href="#personalAccount"><em>My&nbsp;HealthURL</em></a></li>
<?if(!$user->enable_dod):?>
      <li <?=page('amazon')?> ><a href="#amazon"><em>Purchased&nbsp;Services</em></a></li>
<?endif;?>
      <li <?=page('addresses')?> ><a href="#addresses"><em>Address&nbsp;Book</em></a></li>
      <li <?=page('groups')?> ><a href="#groups"><em>Group</em></a></li>

  </ul>

  <div class='yui-content'>
    <div id='personalDetails'>
    <fieldset>
      <legend>Details</legend>
      <table width='100%'>
        <tbody>
          <tr>
            <th>Name:</th>
            <td>
              <?= $first_name ?> <?= $last_name ?><br />
              <a href='edituser.php'>change</a>
            </td>
          </tr>
          <tr>
            <th>Email:</th>
            <td>
              <?= $email ?><br />
              <a href='setemail.php'>change</a>
            </td>
          </tr>
        </tbody>
      </table>
    </fieldset>
    <form method='post' action='picture.php' enctype='multipart/form-data'
          id='picture'>
      <fieldset>
        <legend>Your Picture</legend>
        <a href="<?= $photoUrl ?>">
          <img src="<?= $photoUrl ?>" 
               align='left' style='border: 0; margin: 10px' alt='User Photo' />
        </a>
        <p id='p_picture'>
          <label>Upload Image File:<br />
            <input class='infield'  type='file' name='picture' id='picture' />
          </label>
        </p>
        <input  type='submit' value='Change Picture' />
      </fieldset>
    </form>
    </div>

  <div id='password'>
    <fieldset>
      <legend>Change Password</legend>
    <form class='p' method='post' action='settings.php' id='password'>
        <div class='f' id='p_pw0'>
          <label for='password'>Current Password</label>
          <span class='q'>
            <input class='infield'  type='password' name='pw0' id='pw0' />

    <?php
            if (isset($error)) {
    ?>
    <div class='r errorAlert'><?php echo $error; ?></div>
    <?php
            }
    ?>
          </span>
        </div>

        <div class='f' id='p_pw1'>
          <label for='pw1'>New password</label>
          <span class='q'>
            <input class='infield'  type='password' name='pw1' id='pw1' />

    <?php
            if (isset($pw1_error)) {
    ?>
    <div class='errorAlert r'><?php echo $pw1_error; ?></div>
    <?php
            }
    ?>
          </span>
        </div>

        <div class='f' id='p_pw2'>
          <label for='pw2'>New password (again)</label>
          <span class='q'>
            <input class='infield'  type='password' name='pw2' id='pw2' />

    <?php
            if (isset($pw2_error)) {
    ?>
    <div class='r errorAlert'><?php echo $pw2_error; ?></div>
    <?php
            }
    ?>
          </span>
        </div>

    <?php if (isset($next)) { ?>
        <input type='hidden' value="<?php echo $next; ?>" />
    <?php } ?>

        <div class='f'>
          <span class='n'>&nbsp;</span>
          <span class='q'>
            <input type='submit' value='Change Password' />
          </span>
        </div>
    </form>
    </fieldset>
  </div>

  <div id='identities'>
    <fieldset>
      <legend>Identities</legend>
    <?php if (count($external_users) > 0) { ?>
      <table>
        <thead>
          <tr>
            <th></th>
            <th><acronym title="Identity Provider">IdP</acronym></th>
            <th>External Username</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($external_users as $i) { ?>
    <tr>
      <td>
        <img src="/images/idps/<?= $i['source_id'] ?>.png" width='16' height='16'
             alt="<?= $i['name'] ?>" />
      </td>
      <td>
        <a href="<?= $i['website'] ?>">
          <?= $i['name'] ?>
        </a>
      </td>
      <td>
        <a href="<?= $i['openid_url'] ?>"><?= $i['username'] ?></a>
      </td>
      <td>
        <form method='post' action='unlink_user.php'>
          <input type='hidden' name='idp' value='<?= $i['id'] ?>' />
          <input type='hidden' name='username' value='<?= $i['openid_url'] ?>' />
          <input type='image' src='/images/unlink.png' width='24' height='24'
                 alt='Unlink External User' />
        </form>
      </td>
    </tr>
    <?php } ?>
        </tbody>
      </table>
    <?php } ?>
    <form class='p' method='post' action='link_user.php' id='login'>
      <input type='hidden' name='page' value='identities'/>
      <input type='hidden' name='next' value='/acct/settings.php' />
      <div class='f'>
        <label for='openid_url'>Link</label>
        <span class='q'>
          <input class='infield' type='text' name='openid_url' id='openid_url' size='30' />
          <div class='r'>
            <em>
              <div>http://user.openid.com</div>
            </em>
    <?php
            if (isset($idp_error)) {
    ?>
    <div class='errorAlert'><?= $idp_error ?></div>
    <?php
            }
    ?>
          </div>
        </span>
      </div>
      <div class='f'>
        <span class='n'>
        <?if($acOpenIDMode == 1):?> Allowed <?elseif($acOpenIDMode == 2):?> Recommended <?endif;?>
        OpenID Providers
        </span>
        <span class='q'>
          <?foreach($idps as $i):?>
          <input type='image' src='/images/idps/<?=$i->source_id?>.png'
          width='16' height='16' alt='<?=htmlentities($i->name)?> OpenID'
                     name='idp' value='<?= $i->source_id ?>' class='logo' />
              link <a href='<?= htmlentities($i->website) ?>'><?= htmlentities($i->name) ?></a> account<br />
          <?endforeach;?>
        </span>
      </div>
    </form>
    </fieldset>
  </div>

  <!-- p>
  <a href='/version.php'>Version Information</a> about this MedCommons Appliance
  </p -->
  <div id='personalAccount'>
    <form id='phrs'>
        <h3>Show on Dashboard</h3>
        <table border='0'> 
          <tr><td>
            <input type='radio' 
                   name='mode' 
                   value='patient' 
                   onclick='saveActiveGroup("null")'
                   <?if($dashboard_mode == 'patient'):?>checked='true'<?endif;?> /> My HealthURL</td>
            <td style='padding-left: 60px;'>Shows only the personal HealthURL of the signed-in user.</td>
          </tr>
          <?if(!$is_group_member):?>
            <tr style='color: gray;'>
              <td>
              <input type='radio' name='mode' value='group'
                     onclick='saveActiveGroup("group")' disabled='true'/> Patient List 
              </td>
              <td style='padding-left: 60px;'>Shows a list of family members or patients accessible to the signed-in user.</td> 
            </tr>
          <?else:?>
            <?foreach($practices as $p):?>
              <tr><td><input type='radio' name='mode' value='group' onclick='saveActiveGroup("<?=$p->accid?>")'
                        <?if($p->accid == $active_group_accid):?>checked='true'<?endif;?>  />
                        <?=htmlentities($p->practicename)?>
                        </td>
                        <td></td></tr>
            <?endforeach;?>
         <?endif;?>  
          </table>
      <br/>
    </form>

    <form method='post' action='gwredir.php?dest=PersonalBackup' id='documentsForm'>
      <h3>Your Documents</h3>

<?/*
      <a href="accountDocuments.php">View Documents</a>
      <br/>
 */?>
      You can <a href='gwredir.php?dest=<?=urlencode("PersonalBackup?storageId=$accid&auth=$auth")?>'>download all your documents</a> as a ZIP archive to save them for future reference.
    </form>

    <h3 style='margin-top: 1em;'>Delete Account</h3>
    <p>You can delete all data from your HealthURL.  <a href='javascript:deleteAccount();'>Delete My HealthURL Now</a>.</p>
  </div>
<?if(!$user->enable_dod):?>
  <div id='amazon'>
    <h3>Services and Vouchers</h3>
    <form method='post' action='enable_mod.php'>
      <p><input type='checkbox' name='enable_mod' value="true"
        <?if($enable_vouchers == 1):?>checked="true"<?endif;?>
            onclick='form.submit();'> Enable MedCommons on Demand Vouchers and Services.  
        <?if($enable_vouchers == 1):?>
          &nbsp;
          <a href='/mod/voucherlist.php'>Administer Issued Vouchers</a>.
        <?endif;?>
        </p>
        <?if(($enable_vouchers==0) && !$is_group_member):?>
        <input type='hidden' name='next' value='create_group.php?next=<?=urlencode("settings.php?page=amazon")?>'/>
        <?endif;?>
    </form>
    <br/>
    <h3>Your Account Storage</h3>
    <?if($amazon_user_token != null):?>
    <p>Storage <?if(isset($amazon_count) && ($amazon_count>1)):?>for <?=$amazon_count?> HealthURLs <?else:?>for this HealthURL<?endif;?> 
      is paid via a MedCommons Personal account at 
      Amazon<?if(isset($amazon_first_user_email) && $amazon_first_user_email):?> 
                created by <?if($amazon_first_user_email == $email):?>you<?else:?>
                              <a href='mailto:<?=htmlentities($amazon_first_user_email)?>'><?=htmlentities($amazon_first_user_email)?></a><?endif;?><?endif;?>.
      Pricing and account services are on the 
       <a href='http://www.amazon.com/dp-applications' target="_new">Amazon Application Billing</a> page.
       </p>
    <?else:?>
      <p>Storage for your account is paid for by the operator of this appliance.</p>
    <?endif;?>
    <br/>

    <h3>Your Credit</h3>
    <p>The table shows current credit available for purchased services on MedCommons accounts. 
       Review your purchases on the 
      <a href='https://payments.amazon.com/' target='_new'>Amazon Payments Account Management</a> page.
    </p>
    <p style='text-align: center;'>
    <table class='creditsTable' border='0'>
      <tr><th>DICOM</th><th>Inbound Fax (Pages)</th><th>Unpriced Vouchers</th></tr>
      <tr><td><?=$counters->dicom?></td><td><?=$counters->faxin?></td><td><?=$counters->acc?></td></tr>
      <tr><td>
            <?=AmzPayNowButton("DICOM10", $billingId,gpath('Secure_Url')."/acct/settings.php?page=amazon")?>
          </td>
          <td>
            <?=AmzPayNowButton("FAX20", $billingId, gpath('Secure_Url')."/acct/settings.php?page=amazon")?>
          </td>
                    <td>
            <?=AmzPayNowButton("UNPRICED10", $billingId, gpath('Secure_Url')."/acct/settings.php?page=amazon")?>
          </td>
        </tr>
        <tr>
          <?foreach(array("DICOM10","FAX20","UNPRICED10") as $p):?>
            <td>Purchase <?=htmlentities($acAmazonProducts[$p]["description"])?> for <?=htmlentities($acAmazonProducts[$p]["price"])?></td>
          <?endforeach;?>
        </tr>
    </table>
    </p>
  </div>
<?endif;?>
  <div id='addresses'>
    <?= $template->fetch('addresses.tpl.php') ?>
  </div>

  <div id='groups'>
    <?if($is_group_member && $active_group_accid):?>
      <?= $template->fetch('groups.tpl.php') ?>
    <?else:?>
      <?= $template->fetch('no_group.tpl.php') ?>
    <?endif;?>
  </div>


</div>
    <div id='deleteAccountRequest' style='display: none;'>
      <p>Deleting your account permanently removes all existing data from your account.</p>
      <br/>
      <p><b>This operation cannot be undone.</b></p>
      <br/>
      <p>Your account will not be deleted immediately.  You will receive an email when 
         the account contents have been deleted.</p>
    </div>

    <script type="text/javascript" src="/zip/yui/2.6.0/utilities/utilities.js,yui/2.6.0/tabview/tabview-min.js,yui/2.6.0/datasource/datasource-min.js,yui/2.6.0/datatable/datatable-min.js,acct/utils.js,acct/sha1.js,acct/settings.js"></script>

    <script type='text/javascript'>

      function tabFromLabel(label) {
          var allTabs = tabs.get('tabs');
          for(var i = 0; i<allTabs.length; ++i) {
	    	  if(allTabs[i].get('label').indexOf(label)>=0)
		    	  return allTabs[i];
          }
      }

      YAHOO.util.Event.onDOMReady(function() { 
	      document.body.className='yui-skin-sam';
	      window.tabs = new YAHOO.widget.TabView("profileTabs");
	      var addressesIndex = tabs.get('tabs')
	      tabFromLabel('Address').addListener('click',function(e) {
	        initAddresses();
	      });
	      tabFromLabel('Group').addListener('click',function(e) {
	        initGroups();
	      });
	      if(www_init)
	        www_init();
	      <?if($acctype == 'VOUCHER'):?>
	        var allTabs = tabs.get('tabs');
	        for(var i=0; i<allTabs.length; ++i) {
	          if(allTabs[i].get('label') != 'Password') {
	            tabs.removeTab(allTabs[i]);
	            i--;
	          }
	        }
	      <?endif;?>
	      document.getElementById('profileTabs').style.display='block';
	      // Get full yui loading in background
	      // window.setTimeout(yuiLoader(),1500);
    });
    </script>


    <script type="text/javascript">
      function saveActiveGroup(value) {
        YAHOO.util.Connect.asyncRequest('POST', 'set_dashboard_mode.php', {
          success: function(req) { 
            window.location.href='?page=personalAccount';
          },
          failure: function(req) { alert('failed: '+req.responseText);  window.location.reload();}
        }, 'accid='+value+'&enc='+hex_sha1(getCookie('mc')));
      }
      function sendDeleteRequest() {
        window.location = 'delete_account_request.php';
      }
      function deleteAccount() {
        yuiLoader().insert(function() {
          var dlg = new YAHOO.widget.SimpleDialog('deleteWarnDlg', { 
              width: '500px',
              fixedcenter:true,
              modal:true,
              visible:true,
              draggable:true,
              buttons: [ {text: 'OK - Send Delete Request', handler: sendDeleteRequest}, { text: 'Cancel', handler: function(){dlg.destroy();}} ]
          });
          dlg.setHeader('Delete Account Request - Confirmation');
          dlg.setBody(document.getElementById('deleteAccountRequest').innerHTML);
          dlg.render(document.body);
        });
      }
<?if($page == "groups"):?>
      initGroups();
<?elseif($page == "addresses"):?>
      initAddresses();
<?endif;?>
    </script>
    <link rel="stylesheet" type="text/css" href="/zip/yui/2.6.0/datatable/assets/skins/sam/datatable.css">

