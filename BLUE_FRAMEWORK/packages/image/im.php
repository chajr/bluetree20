<?PHP
/**
 * obiekt do przeksztalcen na pojedynczym obrazie
 * @author chajr <chajr@bluetree.pl>
 * @version 1.5
 * @access private
 * @copyright chajr/bluetree
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
*/
class image_class{
	/**
	 * jakosc wynikowego pliku jpg 0-100 (domyslnie 100)
	 * @var integer
	 */
	public $jpg_quality = 100;
	/**
	 * kompresja wynikowego pliku png 0-9
	 * @var integer 
	 */
	public $png_quality = 9;
	/**
	 * lokalizacja folderu tymaczowego
	 * @var string
	 */
	public $tmp_location = 'BLUE_FRAMEWORK/TMP';
	/**
	 * filtry dla png
	 * PNG_ALL_FILTERS 
	 * PNG_NO_FILTER 
	 * @var string 
	 */
	public $png_filter = '';
	/**
	 * przechowuje kod bledu
	 * @var string
	 */
	public $error = NULL;
	/**
	 * typ obrazu w postaci mime
	 * @var string
	 */
	private $mime = NULL;
	/**
	 * typ obrazu, przetrzymuje jako rozszerzenie
	 * @var string 
	 */
	private $image_type = NULL;
	/**
	 * tablica informacji o pliku z konstruktora
	 * @var array
	 */
	private $file_reference;
	/**
	 * uchwyt otwartego pliku do wykozystania
	 * @var resource
	 */
	protected $img;
	/**
	 * szerokosc zaladowanego obrazu
	 * @var integer
	 */
	protected $width;
	/**
	 * wysokosc zaladowanego obrazu
	 * @var integer 
	 */
	protected $height;
	/**
	 * unixowy znacznik czasu, wykozystywany przy tworzeniu tymczasowego pliku
	 * @var type 
	 */
	private $time;
	/**
	 * pobiera informacje o pliku i tworzy z niego uchwyt, lub tworzy zupelnie nowy plik o wymiarach i odpowiednim tle
	 * @param array $file tablica informacji o pliku (nazwa, rozszerzenie, scierzka dostepu lub wymiary oraz nazwa pliku do zapisania)
	 * @param boolean $chk jesli na true sprawdza czy plik uploudowany byl z bledem
	 * @example new obraz_class(array('name' => 'tmp/plik_tmp'));
	 * @example new obraz_class(array('name' => 'elements/images/obraz.jpg'));
	 * @example new obraz_class(array('binary' => TRUE 'name' => 'binary 3b5y64w7w46b7347ertgytry')); tworzy z binarnej reprezentacji (bez base64_decode)
	 * @example new obraz_class(array('x' => 100, 'y' => 100, 'color' => '#000000', 'alpha' => 5)); alpha 0-127
	 * @return FALSE jesli wystapil blad w uploadzie
	 * @uses obraz_class::$file_reference
	 * @uses obraz_class::$img
	 * @uses obraz_class::$width
	 * @uses obraz_class::$height
	 * @uses obraz_class::$time
	 * @uses obraz_class::create_image()
	 * @uses obraz_class::chk_tmp()
	 */
	public function __construct($file, $chk = FALSE){
		$bool = $this->chk_tmp();
		if(!$bool){
			return FALSE;
		}
		$this->file_reference = $file;
		$this->time = time();
		if(isset($file['x']) && isset($file['y'])){
			$this->img = imagecreatetruecolor($file['x'], $file['y']);
			$this->height = $file['y'];
			$this->width = $file['x'];
			$colors = self::html2rgb($this->file_reference['color']);
			if(isset($this->file_reference['alpha'])){
				imagealphablending($this->img, 0);
				$color = imagecolorallocatealpha($this->img, $colors[0], $colors[1], $colors[2], $this->file_reference['alpha']);
			}else{
				$color = imagecolorallocate($this->img, $colors[0], $colors[1], $colors[2]);
			}
			@imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $color);
		}else{
			if($chk && $this->file_reference['error'] != UPLOAD_ERR_OK){
				$this->error = 'upload_error_'.$this->file_reference['error'];
				return FALSE;
			}
			if(!isset($this->file_reference['binary'])){
				$this->get_type();
			}
			$this->create_image();
		}
	}
	/**
	 * zmienia rozmiary obrazu z lub bez zachowania aspect ratio
	 * @param integer $x docelowa szerokosc obrazu
	 * @param integer $y docelowa wysokosc obrazu
	 * @param boolean $ratio jesli true, obraz ma zachowac aspect ratio
	 * @param boolena $width czy ma wymuszac po wysokosci , jesli ustawnone na 0 wymusza po wysokosci, inaczej po szerokosci
	 * @return boolean FALSE jesli nie udalo sie zmienic wymiarow obazu
	 * @example resize(100, 200, 1)
	 * @example resize(100, 200)
	 * @example resize(600, 400, 1, 0)
	 * @uses obraz_class::$height
	 * @uses obraz_class::$width
	 * @uses obraz_class::$img
	 * @uses obraz_class::$error
	 */
	public function resize($x, $y, $ratio = FALSE, $width = TRUE){
		if($ratio){
			if(($this->height < $this->width) && $width){
				$nowy = imagecreatetruecolor($x,($x/$this->width) * $this->height);
			}else{
				$nowy = imagecreatetruecolor(($y/$this->height) * $this->width, $y);
			}
		}else{
			$nowy = imagecreatetruecolor($x, $y);
		}
		imagealphablending($nowy, 0);
		$nowy_x = imagesx($nowy);
		$nowy_y = imagesy($nowy);
		$bool = @imagecopyresampled($nowy, $this->img, 0, 0, 0, 0, $nowy_x, $nowy_y, $this->width, $this->height);
		if(!$bool){
			$this->error = 'resample_error';
			return FALSE;
		}
		$this->height = $nowy_y;
		$this->width = $nowy_x;
		$this->img = $nowy;
		return TRUE;
	}
	/**
	 * zapisuje plik w podanej lokalizacji i odpowiedim formacie
	 * @param string $location docelowa lokalizacja pliku, jesli NULL zwraca obraz do wyswietlenia
	 * @param string $type typ pliku (jpg/jpeg, gif, png, bmp)
	 * @example save('folder/podfolder/nowa_nazwa.jpg', 'jpg')
	 * @return boolean FALSE jesli wystapil blad przy zapisie pliku
	 * @uses obraz_class::$img
	 * @uses obraz_class::$jpg_quality
	 * @uses obraz_class::$png_filter
	 * @uses obraz_class::$png_quality
	 * @uses obraz_class::$error
	 */
	public function save($location, $type){
		if($location){
			$location = $location;//.'.'.$type
		}else{
			$location = NULL;
		}
		switch($type){
			case "jpg":case "jpeg":
				$bool = imagejpeg($this->img, $location, $this->jpg_quality);
				break;
			case "gif":
				$bool = imagegif($this->img, $location);
				break;
			case "png":
				if($this->png_filter){
					$bool = imagepng($this->img, $location, $this->png_quality, $this->png_filter);
				}else{
					$bool = imagepng($this->img, $location, $this->png_quality);
				}
				break;
			case "bmp":
				$bool = imagewbmp($this->img, $location);
				break;
			default:
				$this->error = 'unknow_file_type';
				return FALSE;
				break;
		}
		if(!$bool){
			$this->error = 'save_error';
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * zwraca rozmiar pliku w bajtach i jego reprezentacje binarna
	 * z mozliwoscia dodanie sekwencji ucieczki np. dla bazy danych
	 * @param string $type typ pliku do zapisu i sprawdzenia
	 * @param boolean $slashes jesli na true dodaje sekwencje ucieczki
	 * @return array() tablica rozmiaru pliku (size) i reprezentacji binarnej (bin), lub FALSE jesli wystapil jakis blad
	 * @uses obraz_class::chk_tmp()
	 * @uses obraz_class::save()
	 * @uses obraz_class::$tmp_location
	 * @uses obraz_class::$time
	 */
	public function binary($type, $slashes = FALSE){
		$bool = $this->chk_tmp();
		if(!$bool){
			return FALSE;
		}
		$arr = array();
		$this->save($this->tmp_location, $type, 'temp_image'.$this->time);
		$f = @fopen($this->tmp_location.'/temp_image'.$this->time, 'r');
		if(!$f){
			throw new packageException('tmp_file_open_error', $this->tmp_location.'/temp_image'.$this->time);
		}
		$arr['size'] = @filesize($this->tmp_location.'/temp_image'.$this->time);
		$arr['bin'] = fread($f, $arr['size']);
		if($slashes){
			$arr['bin'] = addslashes($arr['bin']);
		}
		fclose($f);
		return $arr;
	}
	/**
	 * tworzy uchwyt pliku do przetwarzania
	 * umozliwia kilka operacji na tym samym pliku (np otrzymac kilka plikow z jednego o roznych wymiarach)
	 * @uses obraz_class::$file_reference
	 * @uses obraz_class::$img
	 * @uses obraz_class::$width
	 * @uses obraz_class::$height
	 * @uses obraz_class::$image_type
	 * @uses_obraz_class::$time
	 * @uses obraz_class::$tmp_location
	 * @uses obraz_class::chk_tmp()
	 * @uses obraz_class::save()
	 * @throws packageException invalid_extension, create_error
	 * @todo sprawdzanie typu obrazka z jego reprezentacji binarnej
	 */
	public function create_image(){
		if($this->img){
			@imagedestroy($this->img);
		}
		if(!$this->image_type){
			$type = 'binary';
		}else{
			$type = $this->image_type;
		}
		switch($type){
			case ".jpg":case ".jpeg":
				$this->img = @imagecreatefromjpeg($this->file_reference['name']);
				break;
			case ".gif":
				$this->img = @imagecreatefromgif($this->file_reference['name']);
				break;
			case ".png":
				$this->img = @imagecreatefrompng($this->file_reference['name']);
				break;
			case ".bmp":
				$this->img = @imagecreatefromwbmp($this->file_reference['name']);
				break;
			case "binary":
				$bin_data = base64_decode($this->file_reference['name']);
				$this->img = @imagecreatefromstring($bin_data);
				if($this->img){
					$bool = $this->chk_tmp();
					if(!$bool){
						return FALSE;
					}
					$f = @fopen($this->tmp_location.'/temp_image'.$this->time, 'r');
					if(!$f){
						throw new packageException('tmp_file_open_error', $this->tmp_location.'/temp_image'.$this->time);
					}
					@fclose($f);
					@file_put_contents($this->tmp_location.'/temp_image'.$this->time, $bin_data);
					$this->image_type = image_type_to_extension(
						exif_imagetype($this->tmp_location.'/temp_image'.$this->time)
					);
				}
				break;
			default:
				throw new packageException('invalid_extension', $type);
				break;
		}
		if (!$this->img){
			throw new packageException('create_error', $type);
		}
		$this->width = imagesx($this->img);
		$this->height = imagesy($this->img);
	}
	/**
	 * zwraca wymiary aktualne obrazu, badz jego uchwyt do wykozystania przez inne funkcje
	 * @param string $type typ danych do zwrucenia (img, width, height)
	 * @return mixed informacje o wymiarach, badz uchwyt pliku
	 * @uses obraz_class::$img
	 * @uses obraz_class::$width
	 * @uses obraz_class::$height
	 * @uses obraz_class::$mime
	 * @uses obraz_class::$image_type
	 */
	public function returns($type){
		switch($type){
			case'img':
				return $this->img;
				break;
			case'width':
				return $this->width;
				break;
			case'height':
				return $this->height;
				break;
			case'mime':
				return $this->mime;
				break;
			case'type':
				return $this->image_type;
				break;
			default:
				return NULL;
		}
			
	}
	/**
	 * obracanie obrazu zgodnie ze wskazowkami zegara, obraz po obrocie jest dostosowywany wymiarami
	 * @param integer $degree stopnie obrotu (0-360) 
	 * @param hex $bg_color kolor tla miejsca ktore bedzie odkryte po obrocie
	 * @param integer $transparent jesli ustawione i rozne od 0 przezroczystosc jest ignorowana
	 */
	public function rotate($degree, $bg_color, $transparent = 0){
		$this->img = imagerotate($this->img, $degree, $bg_color, $transparent);
	}
	/**
	 * umozliwia przycinanie obrazu podajac punkty poczatkowe i rozmiar ciecia
	 * @param integer $x poczatek ciecia x
	 * @param integer $y poczatek ciecia y
	 * @param integer $width szerokosc wycietego obrazu
	 * @param integer $height wysokosc wycietego obrazu
	 * @example crop(0,0, 100, 200)
	 * @uses obraz_class::$img
	 * @throws packageException crop_error
	 */
	public function crop($x, $y, $width, $height){
		$nowy = imagecreatetruecolor($width, $height);
		$bool = imagecopy ($nowy, $this->img, 0, 0, $x, $y, $width, $height);
		if(!$bool){
			throw new packageException('crop_error', $x.'-'.$y.'-'.$width.'-'.$height);
		}
		$this->img = $nowy;
	}
	/**
	 * umozliwia umieszczanie w obrazie innych obrazow
	 * @param resource $handler uchwyt do obrazu ktory ma byc umieszczony
	 * @param integer $x_poz pozycja x dla obrazu
	 * @param integer $y_poz poxycja y dla obrazu
	 * @uses obraz_class::$img
	 * 
	 */
	public function place_image($handler, $x_poz, $y_poz){
		$bool = imagecopy($this->img, $handler, $x_poz, $y_poz, 0, 0, imagesx($handler), imagesy($handler));
		if(!$bool){
			throw new packageException('merge_error', $x_poz.'-'.$y_poz.'-'.imagesx($handler).'-'.imagesy($handler));
		}
	}
	/**
	 * dodaje tekst do obrazka
	 * @param string $string tekst do wpisania
	 * @param intiger $x pozycja x
	 * @param integer $y pozycja y
	 * @param hex $color kolor w postaci #xxxxxx
	 * @param integer $size rozmiar czcionki (w punktach lub px zalezy od wersji GD)
	 * @param string $font czcionka
	 * @param integer $angle kat tekstu (0-od lewej do prawej, 90 od gory do dolu), domyslnie 0
	 * @return array zwraca tablice wspolrzednych tekstu, lub false jesli wystapil blad
	 * @example text('jakis tekst', 0, 20, '#ff0000', 12, 'arial.ttf')
	 * @example text('jakis tekst', 0, 20, '#ff0000', 12, 'arial.ttf', 90)
	 * @uses obraz_class::$img
	 * @uses obraz_class::html2rgb() 
	 */
	public function text($string, $x, $y, $color, $size, $font, $angle = 0){
		$color = self::html2rgb($color);
		$color = imagecolorallocate($this->img, $color[0], $color[1], $color[2]);
		return imagettftext($this->img, $size, $angle, $x, $y, $color, $font, $string);
	}
	/**
	 * zamyka uchwyt i ksuje plik tymczasowy
	 * @uses obraz_class::$img
	 * @uses obraz_class::$tmp_location
	 * @uses obraz_class::$time
	 */
	public function __destruct() {
		@imagedestroy($this->img);
		if(file_exists($this->tmp_location.'/temp_image'.$this->time)){
			@unlink($this->tmp_location.'/temp_image'.$this->time);
		}
	}
	/**
	 * pobiera informacje na temato obrazu, jego typ, mime, szerokosc i wysokosc
	 * @uses obraz_class::$file_reference
	 * @uses obraz_class::$mime
	 * @uses obraz_class::$image_type
	 * @uses obraz_class::$width
	 * @uses obraz_class::$height
	 */
	private function get_type(){
		$properties = getimagesize($this->file_reference['name']);
		$this->mime = $properties['mime'];
		$this->image_type = image_type_to_extension($properties[2]);
		$this->width = $properties[0];
		$this->height = $properties[1];
	}
	/**
	 * sprawdza czy istnieje i jesli nie tworzy folder dla plikow tymczasowych
	 * @uses obraz_class::$tmp_location
	 * @uses obraz_class::$error
	 * @return boolean FALSE jesli nie ma i nie udalo sie utworzyc folderu dla plikow tymaczasowych
	 */
	private function chk_tmp(){
		if(!file_exists($this->tmp_location)){
			$bool = mkdir($this->tmp_location, 0777);
			if(!$bool){
				$this->error = 'tmp_create_error';
				return FALSE;
			}
		}
		return TRUE;
	}
	/**
	 * konwertuje wartosci rgb na hex
	 * @param integer $r kolor czerwony, lub tablica kolorow
	 * @param integer $g kolor zielony
	 * @param integer $b kolor niebieski
	 * @return string zwraca wartosc hex koloru w postaci #xxxxxx
	 * @example rgb2html(1, 23, 56)
	 * @example rgb2html(array(1, 23, 56))
	 */
	static function rgb2html($r, $g = -1, $b = -1){
		if (is_array($r) && sizeof($r) == 3){
			list($r, $g, $b) = $r;
		}
		$r = intval($r); 
		$g = intval($g);
		$b = intval($b);
		$r = dechex($r<0?0:($r>255?255:$r));
		$g = dechex($g<0?0:($g>255?255:$g));
		$b = dechex($b<0?0:($b>255?255:$b));
		$color = (strlen($r) < 2?'0':'').$r;
		$color .= (strlen($g) < 2?'0':'').$g;
		$color .= (strlen($b) < 2?'0':'').$b;
		return '#'.$color;
	}
	/**
	 * konwertuje wartosci hex na tablice rgb
	 * @param string $color kolor hex w postaci #xxxxxx lub xxxxxx
	 * @return array tablica kolorow rgb
	 */
	static function html2rgb($color){
		if ($color[0] == '#'){
			$color = substr($color, 1);
		}
		if (strlen($color) == 6){
			list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
		}elseif (strlen($color) == 3){
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		}else{
			return FALSE;
		}
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return array($r, $g, $b);
	}
}
?>