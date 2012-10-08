<?php
/*

*
* layout inspired by Mariano Belinky's SVG sparklines:
* http://www.interactiva.com.ar/mariano/?pname=sparklines
*
*/
/*
function mcsparkline_bargraph($data,$label='',$x=200,$y=50,$linesize=3,$padding=3, $barwidth=10,$barheight=50,
		$barspacing=5,
			$midval=0,$poscolor='black',$negcolor='red',$backcolor='white',$highquality=false
			)
			{
	//////////////////////////////////////////////////////////////////////////////
	// build sparkline using standard flow:
	//   construct, set, render, output
	//
	require_once('../lib/Sparkline_Bar.php');

	$sparkline = new Sparkline_Bar();
//	$sparkline->SetDebugLevel(DEBUG_NONE);
	$sparkline->SetDebugLevel(DEBUG_ERROR | DEBUG_WARNING | DEBUG_STATS | DEBUG_CALLS, '../log.txt');

	$sparkline->SetBarWidth($barwidth);
	$sparkline->SetBarSpacing($barspacing);

	while (list($k, $vals) = each($data)) {
		
		$value = $vals[0]; 
		$highlight = $vals[1];
		//
		// black if positive, red if negative
		//
		$color = $poscolor;
		if ($value < $midval) {
			$color = $negcolor;
		}
//		echo " k=".$k." v=".$value." color=".$color." highlight=".$highlight;
		$sparkline->SetData($k, $value, $color, $highlight);
	}

	$sparkline->Render($barheight); // height only for Sparkline_Bar

	$sparkline->Output();

	return;
	
			}
}
 //mcsparkline ($data,$label='nolabel',$width=200,$height=100,$linesize=3,$padding=3, $barwidth=1,$barheight=16,$barspacing=2,
//$midval=0,$poscolor='black',$negcolor='red',$backcolor='white',$highquality=false
//
*/
function mcsparkline($data, $label, $width,$height,$linewidth,
						$showmin,$showmax,$showlast,$bgcolor,$renderquality,$logfile)	
{//echo "mcsparkline:$label $width X $height X $linesize bgcolor:$showcolor  padding=$padding";return;
	//////////////////////////////////////////////////////////////////////////////
	// build sparkline using standard flow:
	//   construct, set, render, output
	//
	require_once('../lib/Sparkline_Line.php');

	$sparkline = new Sparkline_Line();
//		$sparkline->SetDebugLevel(DEBUG_NONE);// -- ndont use debug_stats 
	$sparkline->SetDebugLevel(DEBUG_ERROR | DEBUG_WARNING |  DEBUG_STATS |DEBUG_CALLS, $logfile);

//	$sparkline->SetColorHtml('background', $backcolor);
	$sparkline->SetColorBackground($bgcolor);


	$i = 0;
	$min  = null;
	$max  = null;
	$last = null;



	while (list($k, $vals) = each($data)) {
		$value = $vals[0];
		$highlight = $vals[1];
		//
		// black if positive, red if negative
		//
		$color = $poscolor;
		if ($value < $midval) 
			$color = $negcolor;

			$sparkline->SetData($i, $value /*,$color,$highlight*/ );

			if (null == $max ||
			$value >= $max[1]) {
				$max = array($i, $value);
			}

			if (null == $min ||
			$value <= $min[1]) {
				$min = array($i, $value);
			}

			$last = array($i, $value);

			$i++;
		
	}

		// set y-bound, min and max extent lines
		//
		$sparkline->SetYMin($min[1]);
		$sparkline->SetYMax($max[1]);
		/*	if (($showmin!='')||($showmax!='')||!($showlast!=''))
			{$sparkline->SetPadding($padding); // setpadding is additive ** not set

			$sparkline->SetPadding(imagefontheight(FONT_2),
		imagefontwidth(FONT_2) * strlen(" $last[1]"),
		0, //imagefontheight(FONT_2),
		0);}
		*/
		if ($showmin!='')
		$sparkline->SetFeaturePoint($min[0],  $min[1],  $showmin,   5, $min[1],     TEXT_TOP,    FONT_2);
		if ($showmax!='')
		$sparkline->SetFeaturePoint($max[0],  $max[1],  $showmax, 5, $max[1],     TEXT_TOP,    FONT_2);
		if ($showlast!='')
		$sparkline->SetFeaturePoint($last[0], $last[1], $showlast,  5, " $last[1]", TEXT_RIGHT,  FONT_2);

		$labellen = imagefontwidth(FONT_2) * strlen($label);
		if ($renderquality != 'high') {
			$sparkline->Render($width, $height);
		} else {
			$sparkline->SetLineSize($linesize); // for renderresampled, linesize is on virtual image
			$sparkline->RenderResampled($width, $height);
		}

		// manually add a label
		//
		if ($label!='')
		$sparkline->DrawText($label,
		$sparkline->GetImageWidth() - $labellen,
		$sparkline->GetImageHeight() - imagefontheight(FONT_2),
		'black',
		FONT_2);

		$sparkline->Output();
		return;
	}
	
