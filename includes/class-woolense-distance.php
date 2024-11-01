<?php

class Woolense_Distance {

    //Convert hex to rgb
	public function hex_to_rgb($hex)
	{
		$rgb = array();
		$hex = ltrim($hex, $hex[0]); 
		$split = str_split($hex, 2);
		$r = hexdec($split[0]);
		array_push($rgb, $r);
		$g = hexdec($split[1]);
		array_push($rgb, $g);
		$b = hexdec($split[2]);
		array_push($rgb, $b);
		return $rgb;
    }
    
    //Get distance
	public function getDistanceBetweenColors($col1, $col2)
	{
		$xyz1 = $this->rgb_to_xyz($col1);
		$xyz2 = $this->rgb_to_xyz($col2);
		$lab1 = $this->xyz_to_lab($xyz1);
		$lab2 = $this->xyz_to_lab($xyz2);
		return $this->ciede2000($lab1, $lab2);
	}

    //Xyz to lab
	public function xyz_to_lab($xyz)
	{
		$x = $xyz[0];
		$y = $xyz[1];
		$z = $xyz[2];
		$_x = $x / 95.047;
		$_y = $y / 100;
		$_z = $z / 108.883;
		if ($_x > 0.008856) {
			$_x = pow($_x, 1 / 3);
		} else {
			$_x = 7.787 * $_x + 16 / 116;
		}
		if ($_y > 0.008856) {
			$_y = pow($_y, 1 / 3);
		} else {
			$_y = (7.787 * $_y) + (16 / 116);
		}
		if ($_z > 0.008856) {
			$_z = pow($_z, 1 / 3);
		} else {
			$_z = 7.787 * $_z + 16 / 116;
		}
		$l = 116 * $_y - 16;
		$a = 500 * ($_x - $_y);
		$b = 200 * ($_y - $_z);

		return (array('l' => $l, 'a' => $a, 'b' => $b));
    }
    
    //Rgb to xyz
	public function rgb_to_xyz($rgb)
	{
		$red = $rgb[0];
		$green = $rgb[1];
		$blue = $rgb[2];
		$_red = $red / 255;
		$_green = $green / 255;
		$_blue = $blue / 255;
		if ($_red > 0.04045) {
			$_red = ($_red + 0.055) / 1.055;
			$_red = pow($_red, 2.4);
		} else {
			$_red = $_red / 12.92;
		}
		if ($_green > 0.04045) {
			$_green = ($_green + 0.055) / 1.055;
			$_green = pow($_green, 2.4);
		} else {
			$_green = $_green / 12.92;
		}
		if ($_blue > 0.04045) {
			$_blue = ($_blue + 0.055) / 1.055;
			$_blue = pow($_blue, 2.4);
		} else {
			$_blue = $_blue / 12.92;
		}
		$_red *= 100;
		$_green *= 100;
		$_blue *= 100;
		$x = $_red * 0.4124 + $_green * 0.3576 + $_blue * 0.1805;
		$y = $_red * 0.2126 + $_green * 0.7152 + $_blue * 0.0722;
		$z = $_red * 0.0193 + $_green * 0.1192 + $_blue * 0.9505;
		return (array($x, $y, $z));
    }
    
    //De_1994 algorithm
	public function de_1994($lab1, $lab2)
	{
		$c1 = sqrt($lab1[1] * $lab1[1] + $lab1[2] * $lab1[2]);
		$c2 = sqrt($lab2[1] * $lab2[1] + $lab2[2] * $lab2[2]);
		$dc = $c1 - $c2;
		$dl = $lab1[0] - $lab2[0];
		$da = $lab1[1] - $lab2[1];
		$db = $lab1[2] - $lab2[2];
		$dh = (($dh_sq = (($da * $da) + ($db * $db) - ($dc * $dc))) < 0) ? sqrt($dh_sq * -1) : sqrt($dh_sq);
		$first = $dl;
		$second = $dc / (1 + 0.045 * $c1);
		$third = $dh / (1 + 0.015 * $c1);
		return (sqrt($first * $first + $second * $second + $third * $third));
	}

    //Cide2000 algorithm
	public function ciede2000($c1, $c2)
	{

		// Get L,a,b values for color 1
		$L1 = $c1['l'];
		$a1 = $c1['a'];
		$b1 = $c1['b'];
		// Get L,a,b values for color 2
		$L2 = $c2['l'];
		$a2 = $c2['a'];
		$b2 = $c2['b'];
		// Weight factors
		$kL = 1;
		$kC = 1;
		$kH = 1;
		/**
		 * Step 1: Calculate C1p, C2p, h1p, h2p
		 */
		$C1 = sqrt(pow($a1, 2) + pow($b1, 2)); //(2)
		$C2 = sqrt(pow($a2, 2) + pow($b2, 2)); //(2)
		$a_C1_C2 = ($C1 + $C2) / 2.0;             //(3)
		$G = 0.5 * (1 - sqrt(pow($a_C1_C2, 7.0) / (pow($a_C1_C2, 7.0) + pow(25.0, 7.0)))); //(4)
		$a1p = (1.0 + $G) * $a1; //(5)
		$a2p = (1.0 + $G) * $a2; //(5)
		$C1p = sqrt(pow($a1p, 2) + pow($b1, 2)); //(6)
		$C2p = sqrt(pow($a2p, 2) + pow($b2, 2)); //(6)

		$h1p = $this->level_initial($b1, $a1p); //(7)
		$h2p = $this->level_initial($b2, $a2p); //(7)
		/**
		 * Step 2: Calculate dLp, dCp, dHp
		 */
		$dLp = $L2 - $L1; //(8)
		$dCp = $C2p - $C1p; //(9)

		$dhp = $this->level_two($C1, $C2, $h1p, $h2p); //(10)
		$dHp = 2 * sqrt($C1p * $C2p) * sin($this->radians($dhp) / 2.0); //(11)
		/**
		 * Step 3: Calculate CIEDE2000 Color-Difference
		 */
		$a_L = ($L1 + $L2) / 2.0; //(12)
		$a_Cp = ($C1p + $C2p) / 2.0; //(13)


		$a_hp = $this->level_one($C1, $C2, $h1p, $h2p); //(14)
		$T = 1 - 0.17 * cos($this->radians($a_hp - 30)) + 0.24 * cos($this->radians(2 * $a_hp)) + 0.32 * cos($this->radians(3 * $a_hp + 6)) - 0.20 * cos($this->radians(4 * $a_hp - 63)); //(15)
		$d_ro = 30 * exp(-(pow(($a_hp - 275) / 25, 2))); //(16)
		$RC = sqrt((pow($a_Cp, 7.0)) / (pow($a_Cp, 7.0) + pow(25.0, 7.0))); //(17)
		$SL = 1 + ((0.015 * pow($a_L - 50, 2)) / sqrt(20 + pow($a_L - 50, 2.0))); //(18)
		$SC = 1 + 0.045 * $a_Cp; //(19)
		$SH = 1 + 0.015 * $a_Cp * $T; //(20)
		$RT = -2 * $RC * sin($this->radians(2 * $d_ro)); //(21)
		$dE = sqrt(pow($dLp / ($SL * $kL), 2) + pow($dCp / ($SC * $kC), 2) + pow($dHp / ($SH * $kH), 2) + $RT * ($dCp / ($SC * $kC)) * ($dHp / ($SH * $kH))); //(22)
		return $dE;
    }
    
    //Extra essential functions
	public function level_initial($x, $y) //(7)
	{
		if ($x == 0 && $y == 0) return 0;
		else {
			$tmphp = $this->degrees(atan2($x, $y));
			if ($tmphp >= 0) return $tmphp;
			else           return $tmphp + 360;
		}
	}
	public function level_two($C1, $C2, $h1p, $h2p) //(10)
	{
		if ($C1 * $C2 == 0)               return 0;
		else if (abs($h2p - $h1p) <= 180) return $h2p - $h1p;
		else if (($h2p - $h1p) > 180)     return ($h2p - $h1p) - 360;
		else if (($h2p - $h1p) < -180)    return ($h2p - $h1p) + 360;
		else                         throw (error);
	}
	public function level_one($C1, $C2, $h1p, $h2p)
	{ //(14)
		if ($C1 * $C2 == 0)                                      return $h1p + $h2p;
		else if (abs($h1p - $h2p) <= 180)                         return ($h1p + $h2p) / 2.0;
		else if ((abs($h1p - $h2p) > 180) && (($h1p + $h2p) < 360))  return ($h1p + $h2p + 360) / 2.0;
		else if ((abs($h1p - $h2p) > 180) && (($h1p + $h2p) >= 360)) return ($h1p + $h2p - 360) / 2.0;
		else                                                throw (new Exception('d'));
    }
    
    //Converting to degrees
	public function degrees($n)
	{
		return $n * (180 / pi());
    }
    
    //Converting to radians
	public function radians($n)
	{
		return $n * (pi() / 180);
	}

}

?>