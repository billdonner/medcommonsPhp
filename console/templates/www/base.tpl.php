{% extends "www/base.html" %}

{% block title %}{{ CommonName }} <?if(isset($title)):?> - <?=$title?><?endif;?>{% endblock title %}

{% block head %}
<?$site = "{{ Site }}";?>

<?if(isset($head)):?>
<?=$head?>
<?endif;?>
<link rel='openid.server' href='http://{{ Domain }}/openid/server.php' />
{% endblock head %}

{% block stamp %}
  <a href='../acct/home.php'><img alt='' border=0 id='stamp' src='../acct/stamp.php?hash=<?=sha1($_COOKIE['mc'].floor(time()/3600))?>'  /></a>
{% endblock stamp %}

{% block main %}
<div id = 'content'>
<?=$content?>
</div>
{% endblock main %}
