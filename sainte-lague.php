#!/usr/bin/php
<?php
include("functions.php");

#$votes = input_votes();
#$misc = input_misc();

$votes = array(
	"GRUENE" => 5762380,
	"CDU" => 11196374,
	"FDP" => 2148757,
	"SPD" =>  8149124,
	"AfD" => 10328780,
	"CSU" => 2964028,
	"Linke" => 4356532
);
$result = calculate_seats($votes, 630, 5, 1);

#$result = calculate_seats($votes, $misc['seats'], $misc['treshold'], $misc['majority']);

$seatcount = 0;
foreach($result as $party => $value){
	$seats = $value['seats'];
	$proportion = round($value['proportion'] * 100, 2);
	$proportion_new = round($value['proportion_new'] * 100, 2);
	$additional_text = "";
	
	if(isset($value['majority_seats'])){
		$additional_text = ", davon " . $value['majority_seats'] . " Sitze aufgrund der Mehrheitsregel.";
	} else if($proportion_new == 0) $additional_text = " - aufgrund der Prozenthürde rausgefallen";
	
	echo PHP_EOL . "$party hat $seats Sitze bekommen ($proportion % / $proportion_new % der Sitze)" . $additional_text . PHP_EOL;
	$seatcount += $value['seats'];
}

echo PHP_EOL . "Sitze gesamt: " . $seatcount . PHP_EOL;

?>
