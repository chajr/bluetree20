<?PHP
/**
 * umozliwia zarzadzanie folderem jako obiektem
 * @author chajr <chajr@bluetree.pl>
 * @package disc
 * @version 1.0
 * @copyright chajr/bluetree
 */
class folder_class extends disc_class{
	public $elements;
	public $size;
	
	public function __construct($path){
		if(!file_exists($path)){
			//tworzy folder
		}else{
            //odczytuje folder
        }
   	}
	public function del(){

	}
	public function mov(){

	}
	public function ren(){
		
	}
}
?>