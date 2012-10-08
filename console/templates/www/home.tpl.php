{% extends "www/base.html" %}

{% block head %}

    <?if($enableCombinedFiles):?>
      <link rel="stylesheet" href="<?=$httpUrl?>/acct_all.css.php" type="text/css"/>
      <script type="text/javascript" src="<?=$httpUrl?>/acct_all.js.php">This page needs Javascript to work properly.</script>
    <?else:?>
      <link rel="stylesheet" href="<?=$httpUrl?>/acctstyle.css" type="text/css"/>
      <link rel="stylesheet" href="<?=$httpUrl?>/main.css" type="text/css"/>
      <link rel="stylesheet" href="<?=$httpUrl?>/featurebox.css" type="text/css"/>
      <script type="text/javascript" src="<?=$httpUrl?>/MochiKit.js">This page needs Javascript to work properly.</script>
      <script type="text/javascript" src="<?=$httpUrl?>/utils.js"></script>
      <script type="text/javascript" src="<?=$httpUrl?>/featurebox.js"></script>
      <script type="text/javascript" src="<?=$httpUrl?>/ajlib.js"></script>
      <script type="text/javascript" src="<?=$httpUrl?>/contextManager.js"></script>
      <script type="text/javascript" src="<?=$httpUrl?>/autoComplete.js"></script>
    <?endif;?>
    <script type="text/javascript">
        function calculateHeight() {
          if($('featureboxes')) { // prevents errors on load
            var h = $('featureboxes').scrollHeight + 12;
            return h;
          }
          else
            return 0;
        }
    </script>
    <!--[if lt IE 7]>
      <script type="text/javascript">
        function calculateHeight() {
          if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
            h = document.body.scrollHeight;
          } 
          else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari
            h = document.body.offsetHeight;
          }
          return h;
        }
      </script>
    <![endif]-->
    <script type="text/javascript">
      function init() {
      	www_init();

        <? // Set up auto-sync'ing of iframe heights ?>
        addHeightSync();

        <?// Initialize context manager.  Do it AFTER other stuff has a chance to load to help performance?>
        <?if($info->practice):?>
          window.setTimeout(function(){
            setAccountFocus('<?=$info->accid?>', '<?=$info->practice->accid?>',
		      '<?=$info->practice->practicename?>', '<?=$info->auth?>', 
              '<?=$gwUrlParts['host']?>','<?=$gwUrlParts['port']?>', 
              '<?=$gwUrlParts['scheme']?>','/gateway/services/CXP2');
          },1500);
        <?endif;?>
      }
    </script>

{% endblock head %}

{% block stamp %}
  <a href='../acct/home.php'><img alt='' border=0 id='stamp' src='../acct/stamp.php?hash=<?=sha1($_COOKIE['mc'].floor(time()/3600))?>'  /></a>
{% endblock stamp %}

{% block body%}onload="init();"{% endblock %}

{% block main %}
    <?=$content?>
{% endblock %}
