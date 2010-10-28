<?php

class mod_time extends mod
{
	function mod_time()
	{
	}


	/**
	 * Formats a timestamp according to a format string, using german language.
	 * Following formatting rules can be used in the format string:
	 *
	 *	#d2 => mo di mi ...
	 *	#D2 => Mo Di Mi ...
	 *	#d+ => montag dienstag mittwoch ...
	 *	#D+ => Montag Dienstag Mittwoch ...
	 *
	 *	#m3 => jan feb mar apr ...
	 *	#M3 => Jan Feb Mar Apr ...
	 *	#m~ => jan. feb. märz apr. mai ...
	 *	#M~ => Jan. Feb. März Apr. Mai ...
	 *	#m+ => januar februar märz ...
	 *	#M+ => Januar Februar März ...
	 *
	 *	#D => day of the month, one or two digits (without leading zeros)
	 *	#DD => day of the month, two digits (with leading zeros)
	 *	#M => month, one or two digits (without leading zeros)
	 *	#MM => month, two digits (with leading zeros)
	 *	#YY => year, two digits (e.g. 08)
	 *	#YYYY => year, four digits (e.g. 2008)
	 *
	 *	#h => hours, one or two digits (without leading zeros)
	 *	#hh => hours, two digits (with leading zeros)
	 *	#m => minutes, one or two digits (without leading zeros)
	 *	#mm => minutes, two digits (with leading zeros)
	 *	#s => seconds, one or two digits (without leading zeros)
	 *	#ss => seconds, two digits (with leading zeros)
	 *
	 *	#z => difference to Greenwich time (GMT) in hours (e.g. +0200)
	 *	#Z => timezone identifier (e.g. MEZ)
	 *
	 *	#W => week number of year
	 *
	 *	## => #
	 *
	 * @param string $formatstring the formatting rules
	 * @param string $time optional - the timestamp to be used (defaults to the
	 * current time)
	 *
	 * @return string the formatted time
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_format($formatstring, $time = false)
	{
		$bits = explode("\n", date("w\nj\nd\nn\nm\ny\nY\nG\nH\ni\ns\nO\nT\nW", ($time) ? $time : time()));

		$d2 = array('so', 'mo', 'di', 'mi', 'do', 'fr', 'sa');
		$D2 = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
		$dplus = array('sonntag', 'montag', 'dienstag', 'mittwoch', 'donnerstag', 'freitag', 'samstag');
		$Dplus = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');

		$m3 = array('jan', 'feb', 'mar', 'apr', 'mai', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dez');
		$M3 = array('Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');
		$mtilde = array('jan.', 'feb.', 'märz', 'apr.', 'mai', 'juni', 'juli', 'aug.', 'sep.', 'okt.', 'nov.', 'dez.');
		$Mtilde = array('Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Juni', 'Juli', 'Aug.', 'Sep.', 'Okt.', 'Nov.', 'Dez.');
		$mplus = array('januar', 'februar', 'märz', 'april', 'mai', 'juni', 'juli', 'august', 'september', 'oktober', 'november', 'dezember');
		$Mplus = array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

		if ($bits[12] == 'CET') $bits[12] = 'MEZ';
		if ($bits[12] == 'CEST') $bits[12] = 'MESZ';

		$rules = array(
			  '/#d2/' => $d2[$bits[0]],
			  '/#D2/' => $D2[$bits[0]],
			 '/#d\+/' => $dplus[$bits[0]],
			 '/#D\+/' => $Dplus[$bits[0]],

			  '/#m3/' => $m3[$bits[3] - 1],
			  '/#M3/' => $M3[$bits[3] - 1],
			  '/#m~/' => $mtilde[$bits[3] - 1],
			  '/#M~/' => $Mtilde[$bits[3] - 1],
			 '/#m\+/' => $mplus[$bits[3] - 1],
			 '/#M\+/' => $Mplus[$bits[3] - 1],

			  '/#DD/' => $bits[2],
			   '/#D/' => $bits[1],
			  '/#MM/' => $bits[4],
			   '/#M/' => $bits[3],
			'/#YYYY/' => $bits[6],
			  '/#YY/' => $bits[5],

			  '/#hh/' => $bits[8],
			   '/#h/' => $bits[7],
			  '/#mm/' => $bits[9],
			   '/#m/' => ($bits[9][0] == '0') ? substr($bits[9], 1) : $bits[9],
			  '/#ss/' => $bits[10],
			   '/#s/' => ($bits[10][0] == '0') ? substr($bits[10], 1) : $bits[10],

			   '/#z/' => $bits[11],
			   '/#Z/' => $bits[12],

			   '/#W/' => $bits[13]
		);

		$formatstring = explode('##', $formatstring);
		$formatstring = preg_replace(array_keys($rules), array_values($rules), $formatstring);
		return implode('#', $formatstring);
	}


	function met_parse_TTMMJJJJ($string)
	{
		// check validity
		if (!preg_match('/^(\d?\d)\.(\d?\d)\.(\d{4}|\d{2})$/', $string, $matches)) return false;

//		if ($matches[3] < 100 && $matches[3] >= 50) $matches[3] += 1900;
//		if ($matches[3] < 50) $matches[3] += 2000;

		$time = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);
		return $time;
	}
}

?>
