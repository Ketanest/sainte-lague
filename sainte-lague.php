#!/usr/bin/php
<?php
include("functions.php");

#$votes = input_votes();
#$misc = input_misc();

$votes = array("GRUEN" => 960478, "CSU" => 622986, "FDP" => 133257, "SPD" => 189327, "Linke" => 17634);
$result = calculate_seats($votes, 70, 5, 1);

#$result = calculate_seats($votes, $misc['seats'], $misc['treshold'], $misc['majority']);

print_r($result);

$seatcount = 0;
foreach($result as $party => $value){
	$seats = $value['seats'];
	$proportion = round($value['proportion'] * 100, 2);
	$proportion_new = round($value['proportion_new'] * 100, 2);
	if(isset($value['majority_seats'])){
		$majority = ", davon " . $value['majority_seats'] . " Sitze aufgrund der Mehrheitsregel.";
	}else $majority = "";
	echo PHP_EOL . "$party hat $seats Sitze bekommen ($proportion % / $proportion_new % der Sitze)" . $majority . PHP_EOL;
	$seatcount += $value['seats'];
}

echo PHP_EOL . "Sitze gesamt: " . $seatcount . PHP_EOL;

?>
