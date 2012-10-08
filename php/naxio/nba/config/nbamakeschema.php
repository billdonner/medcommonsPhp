<?php
require_once "injurylib.inc.php";
require_once "../is.inc.php";

$GLOBALS['form_field_counter']=0;
$GLOBALS['blank_form_stream']='';
$GLOBALS['filled_form_stream']='';
$GLOBALS['schema_stream']='';
$GLOBALS['fields_stream']='';
$GLOBALS['vars_stream']='';
$GLOBALS['loadvars_stream']='';

$GLOBALS['loadobjs_stream']='';
// running thru the injury form include generates three separae code streams, finish them up and store them

require_once "_injuryform.inc.php";

//
$blank_form ='<?php $injury_form="'. "<form action=injuryhandler.php?new method='POST'>\r\n
<input type=hidden name=player value='\$player'>
<input type=hidden name=team value='\$team'>
".$GLOBALS['blank_form_stream'].
"<input type=submit value='submit injury report'>&nbsp;&nbsp;<input type=reset value='reset'>&nbsp;&nbsp;<input type=submit value='cancel'></form>".'"; ?>';
file_put_contents("gen_blank_form.txt",$blank_form); // store it somewhere


$filled_form ='<?php $injury_form="'. "<form action=injuryhandler.php?filled method='POST'>\r\n
<input type=hidden name=player value='\$player'>
<input type=hidden name=team value='\$team'>
".$GLOBALS['filled_form_stream'].
"<input type=submit value='submit injury report'>&nbsp;&nbsp;<input type=reset value='reset'>&nbsp;&nbsp;<input type=submit value='cancel'></form>".'"; ?>';
file_put_contents("gen_filled_form.txt",$filled_form); // store it somewhere


$schema = "
CREATE TABLE `injuries` (
  `injuryid` int(11) NOT NULL auto_increment,
  `player` varchar(255) NOT NULL,
  `team` varchar(255) NOT NULL,
  `alert` varchar(255) NOT NULL,
  `priority` tinyint(4) NOT NULL,
   \r\n".$GLOBALS ['schema_stream']." \r\n
`time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`injuryid`),
  KEY `team` (`team`),
  KEY `player` (`player`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

\r\n";
file_put_contents("gen_injury_schema.txt",$schema); // store it somewhere


$insert ='<?php $insert_query ="'. "INSERT INTO `injuries` (`player`, `team`, `alert`, `priority`,".
$GLOBALS['fields_stream'].
" `time`) VALUES ('\$player','\$team','\$alert','\$priority',".
$GLOBALS['vars_stream'].
" NOW())".'"; ?>';

file_put_contents("gen_injury_insert.txt",$insert); // store it somewher

$loadvars =  '<?php $player=$_REQUEST["player"]; $team = $_REQUEST["team"]; $alert="new report"; $priority=1;'.$GLOBALS['loadvars_stream']."; ?>";

file_put_contents("gen_loadvars.txt",$loadvars); // store it somewhere


$loadobjs =  '<?php $player=$r->player; $team = $r->team;'.$GLOBALS['loadobjs_stream']."; ?>";

file_put_contents("gen_loadobjs.txt",$loadobjs); // store it somewhere

echo "Generated <br/>
<a target='_new' href=gen_blank_form.txt>blankform</a> 
<a target='_new' href=gen_filled_form.txt>filledform</a> 

<a target='_new' href=gen_injury_schema.txt>create table</a>
 <a target='_new' href=gen_injury_insert.txt>insert</a>
<a target='_new' href=gen_loadvars.txt>loadvars</a>
<a target='_new' href=gen_loadobjs.txt>loadobjs</a><br/><br/>";

?>