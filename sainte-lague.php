#!/usr/bin/php
<?php
include("functions.php");

#$votes = input_votes();
#$misc = input_misc();

$votes = array("GRUEN" => 991478, "CSU" => 622986, "FDP" => 133257, "SPD" => 189327, "Linke" => 17634);
$result = calculate_seats($votes, 69, 5, 1);

#$result = calculate_seats($votes, $misc['seats'], $misc['treshold'], $misc['majority']);

$seatcount = 0;
foreach($result as $party => $value){
	echo PHP_EOL . $party . " hat " . $value['seats'] . " Sitze bekommen (" . round($value['proportion'] * 100, 2) . " % / " . round($value['proportion_new'] * 100, 2) . " % der Sitze)" . PHP_EOL;
	$seatcount += $value['seats'];
}

echo PHP_EOL . "Sitze gesamt: " . $seatcount . PHP_EOL;

?>
