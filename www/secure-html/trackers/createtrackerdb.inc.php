<?php
// included by bodytrackers when we need to create the table
$dbname = make_tracker_db_name($accid);
echo "createTrackers: about to open $dbname<br>";
if ($db = sqlite_open($dbname, 0666, $sqliteerror))
{
	sqlite_query($db, 'CREATE TABLE trackers (tracker varchar(32), value varchar(10),
   							tracktime time)');
	echo "Created mytrackers.db with table trackers<br>";
	sqlite_query($db, "CREATE TABLE tracker_info (tracker varchar(32), units varchar(255),
							show_display int,allow_input int, edit_older int,
   							tracktime time,
							value varchar(255), 
							graphicurl varchar(255),
							ginputurl varchar(255),
							PRIMARY KEY  ('tracker'))");
	echo "Created table tracker_info<br>";
	sqlite_query($db, 'CREATE TABLE tracker_properties (sequence varchar(10), bgcolor varchar(10),
	height int, width int,
    linewidth int,	
	showmin varchar(10),
	showmax varchar(10),
	showlast varchar(10),
	renderquality varchar(10),
	tracktime time)');
	echo "Created table tracker_properties<br>";

	sqlite_query($db, 'CREATE TABLE tracker_dictionary(
		tracker varchar(32),
		units varchar(32),
		infourl varchar (255),
		publisher varchar (255),
		id varchar (64),
		graphicurl varchar (255),
		ginputurl varchar (255))');

	echo "Created table tracker_dictionary<br>";


	$time=time();

	$x= "INSERT INTO tracker_properties (bgcolor,height,width,linewidth,
	showmin,showmax,showlast,renderquality,tracktime,sequence)
	VALUES ('$bgcolor', '$height', '$width',
	         	'$linewidth',
				'$showmin', '$showmax', '$showlast', 
				 '$renderquality','$time',
		  		 '*****')";


	sqlite_query($db,$x)
	or
	die ("Can't update tracker_properties ".sqlite_error_string(sqlite_last_error($db)));

	//just jump without doing a redirect

	$___file="http://www.medcommons.net/apps/trackers/catalog-trackers-builtin.xml";

	require_once "loadTrackerCatalog.inc.php";


}
else {
	die("Cannot create database mytrackers ".$sqliteerror);

}

// we finally flow back here where we flow back into viewTrackers or gg Trackers

sqlite_close($db);

?> 
