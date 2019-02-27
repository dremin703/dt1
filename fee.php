<?php

$weeks = [];

function from_euro($sum, string $currency, $reverse=FALSE) {
				$result = $sum;
				if ($currency == "JPY") {
								$result = $reverse ? $sum/119.51 : $sum*119.51;
				};
				if ($currency == "USD") {
								$result = $reverse ? $sum/1.1297 : $sum*1.1297;
				};
				return $result;
}

function format($sum, $currency) {
				switch ($currency) {
				case 'EUR':
				case 'USD':
								return sprintf("%.2f",ceil($sum*100)/100);
				case "JPY":
								return ceil($sum);
				};
				return "???";
}

function calculate_fee($input) {
				global $weeks;
	$iDate = $input[0];
	$iClid = $input[1];
	$iPerson = $input[2];
	$iOper = $input[3];
	$iAmount = $input[4];
	$iCurrency = $input[5];
	$commission = 0.00;
	switch ($iOper) {
					case "cash_in":
									$tmp = from_euro(0.50, $iCurrency);
									$commission = ($tmp<$iAmount*0.003 ? $tmp : $iAmount*0.003);
									break;
					case "cash_out":
									switch ($iPerson) {
									case "legal":
													$tmp = from_euro(5, $iCurrency);
													$commission = ($tmp > $iAmount*0.03 ? $tmp : $iAmount*0.03);
													break;
									case "natural":
													$clidweek = $iClid*1000000+date("oW", strtotime($iDate));
													if (!array_key_exists($clidweek,$weeks)) {
														$weeks[$clidweek] = [
																		"remain_transactions" => 0,
																		"remain_sum" => 1000
														];
													};
													$thisweek = $weeks[$clidweek];
													$commission = 0.00;
													if ($thisweek["remain_transactions"]++ > 3) {
																	$commission = $iAmount*0.003;
													} else {
																	$remainSum = from_euro($thisweek["remain_sum"], $iCurrency);
																	if ($remainSum >= $iAmount) {
																					$commission = 0;
																					$thisweek["remain_sum"] -= from_euro($iAmount, $iCurrency, TRUE);
																	} else {
																					$commission = ($iAmount - $remainSum) * 0.003;
																					$thisweek["remain_sum"] = 0;
																	};
													};
													$weeks[$clidweek] = $thisweek;
													# return $commission;
									};
	};
	return format($commission,$iCurrency);
}

$file = fopen("input.csv","r");
while ( $line = fgets($file) ) {
	$arr = explode(',', trim($line));
	echo calculate_fee($arr) . "\n";
};
fclose($file);

?>

