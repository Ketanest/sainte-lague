<?php
/*
fetches party and vote count (providing basic input check)

returns array with partyname as key and votecount as value or NULL if array is empty

break with empy party
*/
function input_votes(){
	$input = array();
	
	while(true){
		$party = readline("Partei: (Enter fuer Beenden) ");
		if($party == "") break;

		repeat_votes:
		$votes = (int) readline("Stimmenanzahl: ");
		if($votes == ""){
			echo "Keine Eingabe, wiederholen!" . PHP_EOL;
			goto repeat_votes;
		} else if($votes < 1){
			echo "Eingabe ungültig (muss eine Zahl größer 0 sein), wiederholen!" . PHP_EOL;
			goto repeat_votes;
		} else {
			$input[$party] = (int) $votes;
		}
	}

	if(count($input) > 0) return $input; else return NULL;
}

/*
fetches miscellaneous options (treshold, seats, majority)
treshold (percent): integer between 0 and 100 (0 = off, 1-100 = percentage)
seats (mandatory): integer seats in the parliament
majority: bool if majority rule is applied

returns array with treshold, seats and majority as key and corresponding values
*/
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

/*
calculates seats proportionally
treshold (optional, default: false): removes party not meeting the threshold
majority (optional, default: false): more than 50 % of votes results mandatory in more than 50 % of the seats (increases seats if necessary)

returns 2-dimensional array with [key1 = party][key2 = 'seats' and 'proportion']
*/
function calculate_seats($votes, $seats, $treshold = 0, $majority = 0){
	//if treshold > 0 walk through array, remove any party below treshold and add removed party to temporary array
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

	/*
	calculate seats
	*/
	$votesum = array_sum($votes);
	echo "Start-Divisor: " . $divisor = $votesum / $seats;
	$majorityparty = false;
	$seatcalc = array();
	$calcround = 1;
	while(true){
		//assign seats with actual divisor
		foreach($votes as $party => $votecount){
			$seatcalc[$party]['seats'] = (int) round($votecount / $divisor);
			if($calcround == 1){
				//set proportion and majorityparty (if desired) only in the first run
				$seatcalc[$party]['proportion'] = $votecount / $votesum;
				if($seatcalc[$party]['proportion'] > 0.5 && $majority) $majorityparty = $party;
			}
		}
		//check if assigned seats differs from available seat number and increase/decrease divisor if necessary
		$assigned_seats = array_sum(array_column($seatcalc, 'seats'));
		if($assigned_seats == $seats){
			//increase seats if majority (if desired) and seats not majority
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

	//add seats and proportion from treshold parties (temporaray array)
	foreach($treshold_parties as $party => $proportion){
		$seatcalc[$party]['seats'] = 0;
		$seatcalc[$party]['proportion'] = $proportion;
	}

	return $seatcalc;
}
?>
