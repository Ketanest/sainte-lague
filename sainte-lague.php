#!/usr/bin/php
<?php

function input_votes(){
	$input = array();
	while(true){
		$party = readline("Partei: (Enter fuer Beenden) ");
		if($party == "") break;

		repeat_votes:
		$votes = readline("Stimmenanzahl: ");
		if($votes == ""){
			echo "Keine Eingabe, wiederholen!" . PHP_EOL;
			goto repeat_votes;
		} else if(!is_numeric($votes)){
			echo "Eingabe keine Zahl, wiederholen." . PHP_EOL;
			goto repeat_votes;
		} else {
			$input[$party] = (int) $votes;
		}
	}

	if(count($input) > 0) return $input; else return NULL;
}

function input_misc(){
	repeat_treshold:
	$input['treshold'] = (int) readline("Berücksichtigung einer Prozenthürde? (1-100, 0 für ohne [standard]) ");
	if($input['treshold'] < 0 || $input['treshold'] > 100){
		echo "Wert muss zwischen 0 und 100 liegen!" . PHP_EOL;
		goto repeat_treshold;
	}

	$input['majority'] = (bool) readline("Berücksichtigung der Mehrheitsklausel? (ja / NEIN) ");

	repeat_seats:
	$input['seats'] = (int) readline("Wie viele Sitze sind zu vergeben (Ganze Zahl) ");
	if(!($input['seats'] > 0)){
		echo "Ungültige Eingabe, wiederholen!" . PHP_EOL;
		goto repeat_seats;
	}

	return $input;
}

function calculate_seats($votes, $seats, $treshold = 0, $majority = 0){
	$treshold_parties = array();
	if($treshold > 0){
		$sum = array_sum($votes);
		foreach($votes as $party => $votecount){
			$proportion = $votecount / $sum;
			if($proportion < $treshold / 100){
				$treshold_parties[$party] = $proportion;
				 unset($votes[$party]);
			}
		}
	}

	$votesum = array_sum($votes);
	echo "Start-Divisor: " . $divisor = $votesum / $seats;
	$majorityparty = false;
	$seatcalc = array();
	$calcround = 1;
	while(true){
		foreach($votes as $party => $votecount){
			$seatcalc[$party]['seats'] = (int) round($votecount / $divisor);
			if($calcround == 1){
				$seatcalc[$party]['proportion'] = $votecount / $votesum;
				if($seatcalc[$party]['proportion'] > 0.5 && $majority) $majorityparty = $party;
			}
		}
		$assigned_seats = array_sum(array_column($seatcalc, 'seats'));
		if($assigned_seats == $seats){
			if($majorityparty != false && $seatcalc[$majorityparty]['seats'] < ($seats / 2) + 1){
				$seats += 1;
			}else break;
		}else if($assigned_seats > $seats){
			echo "Divisor: " . $divisor += $divisor / 1000;
		}else if($assigned_seats < $seats){
			echo "Divisor: " . $divisor -= $divisor / 1000;
		}else echo "irgenwo ist murks";
		$calcround += 1;
	}

	foreach($treshold_parties as $party => $proportion){
		$seatcalc[$party]['seats'] = 0;
		$seatcalc[$party]['proportion'] = $proportion;
	}

	return $seatcalc;
}

#$votes = input_votes();
#$misc = input_misc();

$votes = array("GRUEN" => 991578, "CSU" => 622986, "FDP" => 133257, "SPD" => 189327, "Linke" => 17634);

$result = calculate_seats($votes, 69, 5, 0);

#$result = calculate_seats($votes, $misc['seats'], $misc['treshold'], $misc['majority']);

$seatcount = 0;
foreach($result as $party => $value){
	echo PHP_EOL . $party . " hat " . $value['seats'] . " Sitze bekommen (" . round($value['proportion'] * 100, 2) . " %)" . PHP_EOL;
	$seatcount += $value['seats'];
}

echo PHP_EOL . "Sitze gesamt: " . $seatcount . PHP_EOL;

?>
