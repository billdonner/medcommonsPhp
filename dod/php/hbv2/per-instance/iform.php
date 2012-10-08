<?php
require_once "if.inc.php";

function mprint($x)
{
	$count = count ($x);
	for ($i=0; $i<$count; $i++)
	{           
		$region = $x[$i][0];
		$rest = $x[$i][1];
		$count2 = count($rest);
		if ($count2==0) echo "<br/>1: $region"; else
		for ($j=0; $j<$count2; $j++ )
		{ 
		$zone =  $rest[$j][0];
		$remains = $rest[$j][1];
		$count3 = count ($remains);
	
		if ($count3==0) echo"<br/>2: $region zone: $zone"; else
		for ($k=0;$k<$count3; $k++)
		{
			$cond = $remains[$k][0];
			$code = $remains [$k][1];
			echo "<br/>3: $region zone: $zone cond: $cond code: $code";
		}
	}
	}
}

                    
                    mprint($ggg);
?>