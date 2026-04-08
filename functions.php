<?php
/*
fetches party and vote count (providing input check)

returns array with partyname as key and votecount as value or NULL if array is empty

break with empy party
*/
function input_votes(){
	$input = array();

	repeat_empty:
	while(true){
		$party = readline("Partei: (Enter fuer Beenden) ");
		if($party == "") break;

		repeat_votes:
		$votes = (int) readline("Stimmenanzahl: ");
		if($votes < 1){
			echo "Eingabe ungültig (muss eine Zahl größer 0 sein), wiederholen!" . PHP_EOL;
			goto repeat_votes;
		} else {
			$input[$party] = (int) $votes;
		}
	}

	if(count($input) > 0) return $input; else goto repeat_empty;
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

	$input['majority'] = (bool) readline("Berücksichtigung der Mehrheitsklausel? (ja / nein [standard]) ");

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
treshold (optional, default: false): removes party not reaching the threshold
majority (optional, default: false): more than 50 % of votes results mandatory in more than 50 % of the seats (increases seats if necessary)

returns 2-dimensional array with
	key1 = party (string)
		key2 =	'seats' (int) => -1 if threshold party 
				'proportion' (decimal) => initial proportion (with treshold parties)
				'proportion_new' (decimal) => new proportion (treshold parties removed), 0 if treshold party
				'majority_seats' (int) => how many majority seats (if chosen), unset for other parties
*/
function calculate_seats($votes, $seats, $treshold = 0, $majority = 0){
	//calculate proportions and add to array
	$seatcalc = array();
	foreach($votes as $party => $votecount){
		$votesum = array_sum($votes);
		$proportion = $votecount / $votesum;
		$seatcalc[$party]['proportion'] = $proportion;
		//if treshold > 0 and party below treshold remove party from $votes and set seats to 0
		if($treshold > 0 && $proportion < ($treshold / 100)){
			unset($votes[$party]);
			$seatcalc[$party]['seats'] = 0;
			$seatcalc[$party]['proportion_new'] = 0;
		}
	}
	
	/*
	calculate seats
	*/
	$votesum = array_sum($votes);
	echo "Start-Divisor: " . $divisor = $votesum / $seats;
	$majorityparty = false;
	$calcround = 1;
	while(true){
		//assign seats with actual divisor
		foreach($votes as $party => $votecount){
			$seatcalc[$party]['seats'] = (int) round($votecount / $divisor);
			if($calcround == 1){
				//set proportion_new and majorityparty (if desired) only in the first run
				$seatcalc[$party]['proportion_new'] = $votecount / $votesum;
				if($seatcalc[$party]['proportion_new'] > 0.5 && $majority) $majorityparty = $party;
			}
		}
		//check if assigned seats differs from available seat number and increase/decrease divisor if necessary
		$assigned_seats = array_sum(array_column($seatcalc, 'seats'));
		if($assigned_seats == $seats){
			//increase seats if majority is set and seats not majority
			if($majorityparty != false){
				while(true){
					if($seatcalc[$majorityparty]['seats'] < ceil(($seats / 2) + 0.1)){
						$seats += 1;
						$seatcalc[$majorityparty]['seats'] += 1;
						$seatcalc[$majorityparty]['majority_seats'] = ($seatcalc[$majorityparty]['majority_seats'] ?? 0) + 1;
					}else break 2;
				}
			}else break;
		}else if($assigned_seats > $seats){
			echo "Divisor: " . $divisor += $divisor / 1000;
		}else if($assigned_seats < $seats){
			echo "Divisor: " . $divisor -= $divisor / 1000;
		}
		$calcround += 1;
	}

	return $seatcalc;
}
?>
