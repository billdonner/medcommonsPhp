<?php
//
// Make a MedCommons Appliance Website
//
//  runs in console mode, expects first arg as name of config file
//
require_once 'settings.php';
function copyfile($infile,$outfile)
{
	file_put_contents($outfile,file_get_contents($infile));
	echo "copied $infile to $outfile\r\n";
}
function xform($site, $logo, $alt, $pageframe,
$infile,$outfile,$title)
{
	$src =file_get_contents(
	$infile);

	$contents = str_replace('$$$$MAINDIV$$$$',
	$src,
	$GLOBALS['page_frame']);

	$contents = str_replace('$$$$TITLE$$$$',
	$title,
	$contents);

	$contents = str_replace('$$$$SITE$$$$',
	$site,
	$contents);

	$contents = str_replace('$$$$LOGO$$$$',
	$logo,
	$contents);

	$contents = str_replace('$$$$ALT$$$$',
	$alt,
	$contents);

	file_put_contents($outfile,$contents);
	echo "transformed $infile to $outfile\r\n";
}

// start here, list every .htm page to be transformed into .html
echo "website generator v0.1\r\n";
$site = "https://".$acDomain;
$home = "/var/www/html";
//open the config file and perform substitution for console $acDomain variable
//$config = $_REQUEST['config'];
$config = $argv[1];
echo "transforming $config\r\n";

$contents =  file_get_contents($config);
$contents = str_replace('$$$$HOME$$$$',
$home,
$contents);
$xmldata = simplexml_load_string($contents);
// get the template
$page_frame = file_get_contents(trim($xmldata->frame));
// apply each of the requested file re-writes
$transforms = $xmldata->transforms;
foreach ($transforms->transform as $transform)
{
	xform($site, $acLogo, $acAlt, $page_frame,
	trim($transform->incoming),
	trim($transform->outgoing),
	trim($transform->title));
}
// do direct copies of any other file collateral
$copies = $xmldata->copies;
foreach ($copies->copy as $copy)
{
	copyfile(
	trim($copy->incoming),
	trim($copy->outgoing));
}
echo "new website is available at $site\r\n";
echo "DO NOT FORGET TO CLEAR THE BROWSER CACHE\r\n";
exit;
?>