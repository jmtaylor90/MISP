<?php
class FinancialTool {
	public $ibanLengths = array(
			'AD' => '24',
			'AE' => '23',
			'AL' => '28',
			'AO' => '25',
			'AT' => '20',
			'AZ' => '28',
			'BA' => '20',
			'BE' => '16',
			'BF' => '27',
			'BG' => '22',
			'BH' => '22',
			'BI' => '16',
			'BJ' => '28',
			'BR' => '29',
			'CG' => '27',
			'CH' => '21',
			'CI' => '28',
			'CM' => '27',
			'CR' => '21',
			'CV' => '25',
			'CY' => '28',
			'CZ' => '24',
			'DE' => '22',
			'DK' => '18',
			'DO' => '28',
			'DZ' => '24',
			'EE' => '20',
			'EG' => '27',
			'ES' => '24',
			'FI' => '18',
			'FO' => '18',
			'FR' => '27',
			'GA' => '27',
			'GB' => '22',
			'GE' => '22',
			'GI' => '23',
			'GL' => '18',
			'GR' => '27',
			'GT' => '28',
			'HR' => '21',
			'HU' => '28',
			'IE' => '22',
			'IL' => '23',
			'IR' => '26',
			'IS' => '26',
			'IT' => '27',
			'JO' => '30',
			'KW' => '30',
			'KZ' => '20',
			'LB' => '28',
			'LC' => '32',
			'LI' => '21',
			'LT' => '20',
			'LU' => '20',
			'LV' => '21',
			'MC' => '27',
			'MD' => '24',
			'ME' => '22',
			'MG' => '27',
			'MK' => '19',
			'ML' => '28',
			'MR' => '27',
			'MT' => '31',
			'MU' => '30',
			'MZ' => '25',
			'NL' => '18',
			'NO' => '15',
			'PK' => '24',
			'PL' => '28',
			'PS' => '29',
			'PT' => '25',
			'QA' => '29',
			'RO' => '24',
			'RS' => '22',
			'SA' => '24',
			'SE' => '24',
			'SI' => '19',
			'SK' => '24',
			'SM' => '27',
			'SN' => '28',
			'TN' => '24',
			'TR' => '26',
			'UA' => '29',
			'VG' => '24',
			'XK' => '20'
	);

	public function validateRouter($type, $value) {
		$validationRoutes = array(
			'cc-number' => 'CC',
			'bin' => 'BIN',
			'bic' => 'BIC',
			'iban' => 'IBAN',
			'btc' => 'BTC',
		);
		if (in_array($type, array_keys($validationRoutes))) return $this->{'validate' . strtoupper($validationRoutes[$type])}($value);
		return true;
	}

	// validating using method described on wikipedia @ https://en.wikipedia.org/wiki/International_Bank_Account_Number#Algorithms
	public function validateIBAN($iban) {
		if (strlen($iban) < 15 || strlen($iban) > 32) return false;
		$temp = substr($iban, 4) . substr($iban, 0, 4);
		$temp2 = '';
		for ($i = 0; $i < strlen($temp); $i++) {
			if (is_numeric($temp[$i])) $temp2 .= $temp[$i];
			else $temp2 .= ord(strtolower($temp[$i])) - 87;
		}
		$temp = bcmod($temp2, 97);
		return intval($temp)===1 ? true : false;
	}

	public function validateBIC($bic) {
		if (preg_match('/^([A-Z]{4})([A-Z]){2}([0-9A-Z]){2}([0-9A-Z]{3})?$/i', $bic)) return true;
		return false;
	}

	public function validateBIN($bin) {
		if (is_numeric($bin) && strlen($bin) == 6) return true;
		return false;
	}

	// based on the explanation at www.freeformatter.com/credit-card-number-generator-validator.html#validate
	public function validateCC($cc) {
		if (is_numeric($cc) && strlen($cc) > 12 && strlen($cc) < 20) {
			$numberArray = str_split($cc);
			$lastDigit = $numberArray[count($numberArray) - 1];
			unset($numberArray[count($numberArray) - 1]);
			$numberArray = array_reverse($numberArray);
			$sum = 0;
			foreach ($numberArray as $k => $number) {
				$number = intval($number);
				if ($k%2 == 0) $number *= 2;
				if ($number > 9) $number -=9;
				$sum += $number;
			}
			$sum += $lastDigit;
			if ($sum%10 == 0) return true;
			return false;
		}
		return false;
	}

	// based on the php implementation of the BTC address validation example from
	// http://rosettacode.org/wiki/Bitcoin/address_validation
	public function validateBTC($address) {
		if (strlen($address) < 26 || strlen($address) > 35) return false;
		$decoded = $this->__decodeBase58($address);
		if ($decoded === false) return false;

		$d1 = hash("sha256", substr($decoded,0,21), true);
		$d2 = hash("sha256", $d1, true);

		if (substr_compare($decoded, $d2, 21, 4)) {
			return false;
		}
		return true;
	}

	private function __decodeBase58($input) {
		$alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";

		$out = array_fill(0, 25, 0);
		for ($i=0;$i<strlen($input);$i++) {
			if (($p=strpos($alphabet, $input[$i]))===false) {
				return false;
			}
			$c = $p;
			for ($j = 25; $j--; ) {
				$c += (int)(58 * $out[$j]);
				$out[$j] = (int)($c % 256);
				$c /= 256;
				$c = (int)$c;
			}
			if ($c != 0) {
				return false;
			}
		}

		$result = "";
		foreach ($out as $val) {
			$result .= chr($val);
		}

		return $result;
	}
}
