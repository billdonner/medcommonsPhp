<?php
function fixup($s)
{
	$ret ='';
	$len = strlen($s);
	for ($j=0; $j<$len; $j++)
	{
		if (ord(substr($s,$j,1))<ord(' ')) $ret.=' '; else $ret.=substr($s,$j,1);
	}
	return $ret;
}
function dump ($prompt,$val)
{ echo $prompt."&nbsp; ".$val."<br/>";}





function parsefile($buffer)
{

	//
	// parse according to spec:
	//  http://www.dbf2002.com/dbf-file-format.html
	$buffersize = strlen($buffer);
	$fileType = ord(substr($buffer,0,1));
	$lastUpdateYY = ord(substr($buffer,1,1));
	$lastUpdateMM= ord(substr($buffer,2,1));
	$lastUpdateDD = ord(substr($buffer,3,1));
	$numRecs = ord(substr($buffer,4,1)) + 256* (ord(substr($buffer,5,1))+ 256* (ord(substr($buffer,6,1))+ 256* (ord(substr($buffer,7,1)))));
	$firstPos = ord(substr($buffer,8,1))+ 256* (ord(substr($buffer,9,1)));
	$recLen = ord(substr($buffer,10,1))+ 256* (ord(substr($buffer,11,1)));
	$tableFlags =ord(substr($buffer,28,1));
	$codePageMark = ord(substr($buffer,29,1));
	$reservedZeroes = ord(substr($buffer,30,1))+ 256* (ord(substr($buffer,31,1)));


	//dump ('Type',$fileType);
	dump ('lastUpdateYYMMDD',$lastUpdateYY.'/'.$lastUpdateMM.'/'.$lastUpdateDD);
	dump ('Number of Records',$numRecs);
	/*
	 dump ('First Data Position',$firstPos);
	 dump ('Size of Each Data Record',$recLen);
	 dump ('tableFlags',$tableFlags);
	 dump ('codePageMark',$codePageMark);
	 dump ('reservedZeroes',$reservedZeroes);

	 dump (' ',' ');
	 dump ('Each Field ',' ');
	 dump (' ',' ');
	 */
	$pos = 32;
	// now the subfields of each record
	$fieldName = $fieldType = $fieldDisplacement = $fieldLength = $fieldDecimals = $fieldFlags = array();

	while (($pos<$buffersize) && (ord(substr($buffer,$pos,1))>=ord('A'))) // should terminate with this
	{
		$fieldName[] = substr($buffer,$pos,10);
		$fieldType[] = substr($buffer,$pos+11,1);
		$fieldDisplacement[] = ord(substr($buffer,$pos+12,1)) + 256* (ord(substr($buffer,$pos+13,1))+ 256* (ord(substr($buffer,$pos+14,1))+
		256* (ord(substr($buffer,$pos+15,1)))));
		$fieldLength[] = ord(substr($buffer,$pos+16,1));
		$fieldDecimals[] = ord(substr($buffer,$pos+17,1));
		$fieldFlags[] = ord(substr($buffer,$pos+18,1));
		$pos +=32;
	}
	$datapos = $firstPos;
	for ($i=1; $i<=min(3,$numRecs); $i++)
	{
		
			if ($numRecs>1)echo "<br/>record $i: <br/>";
			
		for ($j=0; $j<count($fieldName); $j++)
		{
			$dataval = substr($buffer,$datapos+$fieldDisplacement[$j],$fieldLength[$j]);
			if (ord(substr($dataval,0,1))>ord(' '))
			echo
			fixup($fieldName[$j]). " ".//$fieldType[$j]." ".$fieldLength[$j].'.'.
			//$fieldDecimals[$j].//" at ".$fieldDisplacement[$j]." flags " .$fieldFlags[$j].
			fixup($dataval)."<br/>";
		}
		$datapos +=$recLen;
	}



	// now show the data
}
function process_files($folder,$sysfiles)
{
	foreach ($sysfiles as $file)
	{

		$buffer='';
		$handle = @fopen($folder.'/'.$file, "r");
		if ($handle===false) die ("Cant open file $file");
		if ($handle) {
			echo "<hr/><br/>processing ==> $folder/$file<br/>";
			while (!feof($handle)) {
				$buffer .= fgets($handle, 8096);
			}
			fclose($handle);
		}
		parsefile($buffer);
	}
}

// main
$folder = $_GET['folder'];




$ufiles = array('FACTRN99.DBF','INJURY99.DBF','PERSON99.DBF','PHYSIC99.DBF','PROGRS99.DBF','STATUS99.DBF','TRTMNT99.DBF','WEIGHT99.DBF');

process_files($folder,$ufiles);

$sysfiles = array('count.DBF','facility.DBF','loggedin.DBF','password.DBF','schedule.DBF','pulldown.DBF','company.DBF','screens.DBF','tables.DBF',
										'proglist.DBF','wghtdet.DBF','wghtyrl.DBF','prgrmdte.DBF','words1.DBF','modrep.DBF','injlist.DBF','injtreat.DBF',
										'injtreat.DBF','physlist.DBF','physref.DBF','repopt.DBF','schdlist.DBF','perslist.DBF','isspulld.DBF','smsync.DBF',
										'trtdet.DBF','faclist.DBF','factran.DBF','datacdx.DBF',	'FOXUSER.DBF','WORDS2.DBF','WORDS3.DBF','PROGRAM.DBF','ICARRIER.DBF','SRWMAIN.DBF');

process_files($folder,$sysfiles);
?>

