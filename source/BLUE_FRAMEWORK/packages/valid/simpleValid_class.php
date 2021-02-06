<?php
/**
 * contains all methods to validate data
 *
 * @category    BlueFramework
 * @package     valid
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.8.0
 */
class simpleValid_class
{
    /**
     * array of regular expressions used to validate
     * @var array
     */
    static $regularExpressions = array(
        'string' =>             '#^[\p{L} ]*$#u',
        'letters' =>            '#^[\p{L} _ ,.-]*$#u',
        'letters_extend' =>     '#^[\p{L}_ ,\\.;:-]*$#u',
        'fullchars' =>          '#^[\p{L}\\d_ ,\\.;:/!@#$%^&*()+=|\\\{}\\]\\[<>?`~\'"-]*$#u',
        'integer' =>            '#^[\\d]*$#',
        'multinum' =>           '#^[\\d /-]*$#',
        'num_chars' =>          '#^[\p{L}\\d\\.,_ -]*$#u',
        'num_char_extends' =>   '#^[\p{L}\\d_ ,\\.;:-]*$#u',
        'numeric' =>            '#^(-)?[\\d]*$#',
        'float' =>              '#^(-)?[\\d]*((,|\\.)?[\\d]*)?$#',
        'mail' =>               '#^[\\w_\.-]*[\\w_]@[\\w_\.-]*\.[\\w_-]{2,3}$#e',
        'url' =>                '#^(http://)?[\\w\\._-]+(/)?$#',
        'url_extend' =>         '#^((http|https|ftp|ftps)://)?[\\w\\._-]+(/)?$#',
        'url_full' =>           '#^((http|https|ftp|ftps)://)?[\\w\\._/-]+(\\?[\\w&%=+-]*)?$#',
        'price' =>              '#^[\\d]*((,|\\.)[\\d]{0,2})?$#',
        'postcode' =>           '#^[\\d]{2}-[\\d]{3}$#',
        'phone' =>              '#^((\\+)[\\d]{2})?( ?\\( ?[\\d]+ ?\\) ?)?[\\d -]*$#',
        'date2' =>              '#^[\\d]{2}-[\\d]{2}-[\\d]{4}$#',
        'date' =>               '#^[\\d]{4}-[\\d]{2}-[\\d]{2}$#',
        'month' =>              '#^[\\d]{4}-[\\d]{2}$#',
        'datetime' =>           '#^[\\d]{4}-[\\d]{2}-[\\d]{2} [\\d]{2}:[\\d]{2}$#',
        'jdate' =>              '#^[\\d]{2}/[\\d]{2}/[\\d]{4}$#',                            //time from jquery datepicker
        'jdatetime' =>          '#^[\\d]{2}/[\\d]{2}/[\\d]{4} [\\d]{2}:[\\d]{2}$#',          //time from jquery datepicker
        'time' =>               '#^[\\d]{2}:[\\d]{2}(:[\\d]{2})?$#',
        'hex_color' =>          '/^#[\\da-f]{6}$/i',
        'hex' =>                '/^#[\\da-f]+$/i',
        'hex2' =>               '#^0x[\\da-f]+$#i',
        'octal' =>              '#^0[0-7]+$#',
        'binary' =>             '#^b[0-1]+$#i',
        'week' =>               '#^[\\d]{4}-[\\d]{2}$#'
    );

    /**
     * contains information from PESEL validation, that user is male or female
     * 0 -female, 1 -male
     * @var integer
     */
    static $peselSex = NULL;
    
    /**
     * standard validate method, use validation from $regularExpressions variable
     * 
     * 'string' =>             '#^[\p{L} ]*$#u',
     * 'letters' =>            '#^[\p{L} _ ,.-]*$#u',
     * 'letters_extend' =>     '#^[\p{L}_ ,\\.;:-]*$#u',
     * 'fullchars' =>          '#^[\p{L}\\d_ ,\\.;:/!@#$%^&*()+=|\\\{}\\]\\[<>?`~\'"-]*$#u',
     * 'integer' =>            '#^[\\d]*$#',
     * 'multinum' =>           '#^[\\d /-]*$#',
     * 'num_chars' =>          '#^[\p{L}\\d\\.,_ -]*$#u',
     * 'num_char_extends' =>   '#^[\p{L}\\d_ ,\\.;:-]*$#u',
     * 'numeric' =>            '#^(-)?[\\d]*$#',
     * 'float' =>              '#^(-)?[\\d]*((,|\\.)?[\\d]*)?$#',
     * 'mail' =>               '#^[\\w_\.-]*[\\w_]@[\\w_\.-]*\.[\\w_-]{2,3}$#e',
     * 'url' =>                '#^(http://)?[\\w\\._-]+(/)?$#',
     * 'url_extend' =>         '#^((http|https|ftp|ftps)://)?[\\w\\._-]+(/)?$#',
     * 'url_full' =>           '#^((http|https|ftp|ftps)://)?[\\w\\._/-]+(\\?[\\w&%=+-]*)?$#',
     * 'price' =>              '#^[\\d]*((,|\\.)[\\d]{0,2})?$#',
     * 'postcode' =>           '#^[\\d]{2}-[\\d]{3}$#',
     * 'phone' =>              '#^((\\+)[\\d]{2})?( ?\\( ?[\\d]+ ?\\) ?)?[\\d -]*$#',
     * 'date2' =>              '#^[\\d]{2}-[\\d]{2}-[\\d]{4}$#',
     * 'date' =>               '#^[\\d]{4}-[\\d]{2}-[\\d]{2}$#',
     * 'month' =>              '#^[\\d]{4}-[\\d]{2}$#',
     * 'datetime' =>           '#^[\\d]{4}-[\\d]{2}-[\\d]{2} [\\d]{2}:[\\d]{2}$#',
     * 'jdate' =>              '#^[\\d]{2}/[\\d]{2}/[\\d]{4}$#',                            //time from jquery datepicker
     * 'jdatetime' =>          '#^[\\d]{2}/[\\d]{2}/[\\d]{4} [\\d]{2}:[\\d]{2}$#',          //time from jquery datepicker
     * 'time' =>               '#^[\\d]{2}:[\\d]{2}(:[\\d]{2})?$#',
     * 'hex_color' =>          '/^#[\\da-f]{6}$/i',
     * 'hex' =>                '/^#[\\da-f]+$/i',
     * 'hex2' =>               '#^0x[\\da-f]+$#i',
     * 'octal' =>              '#^0[0-7]+$#',
     * 'binary' =>             '#^b[0-1]+$#i',
     * 'week' =>               '#^[\\d]{4}-[\\d]{2}$#'
     * 
     * @param mixed $value value to check
     * @param string $type validation type
     * @return boolean if ok return TRUE, of not return FALSE, return NULL if validation type wasn't founded
     */
    static function valid($value, $type)
    {
        if (!isset(self::$regularExpressions[$type])) {
            return NULL;
        }

        $bool = preg_match(self::$regularExpressions[$type], $value);

        if (!$bool) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * check e-mail address format
     * 
     * @param string $address 
     * @return boolean
     */
    static function mail($address)
    {
        if (!preg_match (self::$regularExpressions['mail'], $address)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * check price format
     * 
     * @param integer $value
     * @return boolean
     */
    static function price($value)
    {
        $bool = preg_match(self::$regularExpressions['price'], $value);
        if (!$bool) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * check post code format
     * 
     * @param string $value
     * @return boolean
     */
    static function postcode($value)
    {
        $bool = preg_match(self::$regularExpressions['postcode'], $value);
        if (!$bool) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * check NIP number format
     * 
     * @param string $value
     * @return boolean
     */
    static function nip($value)
    {
        if (!empty($value)) {
            $weights    = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
            $nip        = preg_replace('#[\\s-]#', '', $value);

            if (strlen($nip) === 10 && is_numeric($nip)) {
                $sum = 0;

                for ($i = 0; $i < 9; $i++) {
                    $sum += $nip[$i] * $weights[$i];
                }

                return ($sum % 11) == $nip[9];
            }
        }
        return FALSE;
    }

    /**
     * check string length, possibility to set range
     * 
     * @param string $value
     * @param integer $min minimal string length, if NULL don't check
     * @param integer $max maximal string length, if NULL don't check
     * @return boolean
     * @example stringLength('asdasdasd', $min = NULL, $max = 23)
     * @example stringLength('asdasdasd', $min = 3, $max = 23)
     * @example stringLength('asdasdasd', $min = 3)
     */
    static function stringLength($value, $min = NULL, $max = NULL)
    {
        $length = mb_strlen($value);
        $bool   = self::range($length, $min, $max);
        return $bool;
    }

    /**
     * check range on numeric values
     * allows to check decimal, hex, octal an binary values
     * 
     * @param integer $value
     * @param integer $min minimal string length, if NULL don't check
     * @param integer $max maximal string length, if NULL don't check
     * @example range(23423, $min = NULL, $max = 23)
     * @example range(23423, $min = 3, $max = 23)
     * @example range(23423, $min = 3)
     * @example range(0xd3a743f2ab, $min = 3)
     * @example range('#aaffff', $min = 3)
     * @return boolean
     */
    static function range($value, $min = NULL, $max = NULL)
    {
        if (   preg_match(self::$regularExpressions['hex'], $min)
            || preg_match(self::$regularExpressions['hex2'], $min)
        ){
            $value = hexdec($value);
            $min = hexdec($min);
        }

        if (   preg_match(self::$regularExpressions['hex'], $max)
            || preg_match(self::$regularExpressions['hex2'], $max)
        ){
            $value = hexdec($value);
            $max = hexdec($max);
        }

        if (preg_match(self::$regularExpressions['octal'], $min)) {
            $value = octdec($value);
            $min = octdec($min);
        }

        if (preg_match(self::$regularExpressions['octal'], $max)) {
            $value = octdec($value);
            $max = octdec($max);
        }

        if (preg_match(self::$regularExpressions['binary'], $min)) {
            $value = bindec($value);
            $min = bindec($min);
        }

        if (preg_match(self::$regularExpressions['binary'], $max)) {
            $value = bindec($value);
            $max = bindec($max);
        }

        if ($min != NULL && $min > $value) {
            return FALSE;
        }

        if ($max != NULL && $max < $value) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * check that numeric value is less than 0
     * if less return TRUE
     * 
     * @param integer $value
     * @return boolean
     */
    static function underZero($value)
    {
        if ($value < 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * check PESEL number format
     * also set sex of person in $peselSex variable
     * 
     * @param mixed $value
     * @return boolean
     */
    static function pesel($value)
    {
        $value = preg_replace('#[\\s-]#', '', $value);
        if (!preg_match('#^[0-9]{11}$#',$value)) {
            return FALSE;
        }

        if (($value[9] % 2) == 0) {
            self::$peselSex = 0;
        } else {
            self::$peselSex = 1;
        }

        $arrSteps   = array(1, 3, 7, 9, 1, 3, 7, 9, 1, 3);
        $intSum     = 0;

        for ($i = 0; $i < 10; $i++) {
            $intSum += $arrSteps[$i] * $value[$i];
        }

        $int            = 10 - $intSum % 10;
        $intControlNr   = ($int === 10) ? 0 : $int;

        if ($intControlNr === $value[10]) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * check REGON number format
     * 
     * @param mixed $value
     * @return boolean
     */
    static function regon($value)
    {
        $value = preg_replace('#[\\s-]#', '', $value);
        if (strlen($value) != 9) {
            return FALSE;
        }

        $arrSteps   = array(8, 9, 2, 3, 4, 5, 6, 7);
        $intSum     = 0;

        for ($i = 0; $i < 8; $i++) {
            $intSum += $arrSteps[$i] * $value[$i];
        }

        $int            = $intSum % 11;
        $intControlNr   = ($int === 10) ? 0 : $int;

        if ($intControlNr == $value[8]) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * check account number format in NRB standard
     * 
     * @param mixed $value
     * @return boolean
     */
    static function nrb($value)
    {
        $iNRB = preg_replace('#[\\s-]#', '', $value);
        if (strlen($iNRB) !== 26) {
            return FALSE;
        }

        $iNRB        = $iNRB.'2521';
        $iNRB        = substr($iNRB, 2).substr($iNRB, 0, 2);
        $iNumSum     = 0;
        $aNumWeight  = array(1, 10, 3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15,
            53, 45, 62, 38, 89, 17, 73, 51, 25, 56, 75, 71, 31, 19, 93, 57)
        ;

        for ($i = 0; $i < 30; $i++) {
            $iNumSum += $iNRB[29-$i] * $aNumWeight[$i];
        }

        if($iNumSum % 97 === 1){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * check account number format in IBAN standard
     * 
     * @param mixed $value
     * @return boolean
     * @author Bartłomiej Zastawnik, "Rzast"
     */
    static function iban($value)
    {
        //(c) Bartłomiej Zastawnik, "Rzast".
        $puste = array(' ', '-', '_', '.', ',','/', '|');//znaki do usuniącia
        $temp = strtoupper(str_replace($puste, '', $value));//Zostają cyferki + duże litery
        if (($temp{0}<='9')&&($temp{1}<='9')){//Jeżeli na początku są cyfry, to dopisujemy PL, inne kraje muszć być jawnie wprowadzone
            $temp ='PL'.$temp;
        }
        $temp=substr($temp,4).substr($temp, 0, 4);//przesuwanie cyfr kontrolnych na koniec
        $znaki=array(
            '0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4',
            '5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9',
            'A'=>'10','B'=>'11','C'=>'12','D'=>'13','E'=>'14','F'=>'15',
            'G'=>'16','H'=>'17','I'=>'18','J'=>'19','K'=>'20',
            'L'=>'21','M'=>'22','N'=>'23','O'=>'24','P'=>'25',
            'Q'=>'26','R'=>'27','S'=>'28','T'=>'29','U'=>'30',
            'V'=>'31','W'=>'32','X'=>'33','Y'=>'34','Z'=>'35'
        );//Tablica zamienników, potrzebnych do wyliczenia sumy kontrolnej
        $ilosc=strlen($temp);//długość numeru
        $ciag='';
        for ($i=0;$i<$ilosc;$i++){
            $ciag.=$znaki[$temp{$i}];
        }
        $mod = 0;
        $ilosc=strlen($ciag);//nowa długość numeru
        for($i=0;$i<$ilosc;$i=$i+6) {
            //oblicznie modulo, $ciag jest zbyt wielkć liczbę na format integer, wiąc dzielć go na kawaśki
            $mod = (int)($mod.substr($ciag, $i, 6)) % 97;
        }
        $out=($mod==1)?TRUE:FALSE;
        return $out;
    }

    /**
     * check URL address
     * 
     * @param string $url
     * @param integer $type if 1 check protocols also, if 2 check with GET parameters
     * @return boolean
     */
    static function url($url, $type)
    {
        switch ($type) {
            case 1:
                $type = self::$regularExpressions['url_extend'];
                break;

            case 2:
                $type = self::$regularExpressions['url_full'];
                break;

            default:
                $type = self::$regularExpressions['url'];
                break;
        }

        $bool = preg_match($type, $url);
        if (!$bool) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * check phone number format
     * eg +48 ( 052 ) 131 231-2312
     * 
     * @param mixed $phone
     * @return boolean
     */
    static function phone($phone)
    {
        if (!preg_match (self::$regularExpressions['phone'], $phone)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * check step of value
     * 
     * @param integer|float $value
     * @param integer|float $step step to check
     * @param integer|float $default default value (0)
     * @return boolean
     * @example step(15, 5, 5) TRUE
     * @example step(12, 5) FALSE
     */
    static function step($value, $step, $default = 0)
    {
        if (   !self::valid($step, 'float')
            || !self::valid($default, 'float')
            || !self::valid($value, 'float')
        ){
            return FALSE;
        }

        $check = (abs($value)-abs($default))%$step;
        if ($check) {
            return FALSE;
        }

        return TRUE;
    }

    static function checkDate($data)
    {

    }
}
