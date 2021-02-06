<?php
/**
 * allows to manipulate on image
 *
 * @category    BlueFramework
 * @package     image
 * @subpackage  image
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.8.0 beta
 * 
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
class image_class
{
    /**
     * quality of jpeg output file form 0 to 100 (default 100)
     * @var integer
     */
    public $jpgQuality = 100;

    /**
     * png output file compression form 0 to 9
     * @var integer 
     */
    public $pngQuality = 9;

    /**
     * temporary directory location
     * @var string
     */
    public $tmpLocation;

    /**
     * filters for png files
     * PNG_ALL_FILTERS 
     * PNG_NO_FILTER 
     * @var string 
     */
    public $pngFilter = '';

    /**
     * image type in mime representation
     * @var string
     */
    private $_mime = NULL;

    /**
     * image type, set up as extension
     * @var string 
     */
    private $_imageType = NULL;

    /**
     * information about given image file
     * @var array
     */
    private $_fileReference;

    /**
     * opened image handler
     * @var resource
     */
    protected $_imageHandler;

    /**
     * width of processing image
     * @var integer
     */
    protected $_imageWidth;

    /**
     * height of processing image
     * @var integer 
     */
    protected $_imageHeight;

    /**
     * unix timestamp, used when creating temporary file
     * @var integer 
     */
    private $_time;

    /**
     * get information about file, and create handle to image
     * or create new image, with specific dimensions and background
     * 
     * @param array $file file informations (name, extension, path or dimensions, save path)
     * @param boolean $chk if TRUE check that given in array uploaded file has some error
     * @example new image_class(array('name' => 'tmp/file'));
     * @example new image_class(array('name' => 'elements/images/image.jpg'));
     * @example new image_class(array('binary' => TRUE 'name' => 'binary ...')); create from binary (without base64_decode)
     * @example new image_class(array('x' => 100, 'y' => 100, 'color' => '#000000', 'alpha' => 5)); alpha 0-127
     * @throws packageException('upload_error', $this->_fileReference['error'])
     */
    public function __construct($file, $chk = FALSE)
    {
        $this->tmpLocation = starter_class::path(TRUE) . '/' . $this->tmpLocation;
        $this->_checkTempDirectory();
        $this->_fileReference   = $file;
        $this->_time            = time();

        if (isset($file['x']) && isset($file['y'])) {
            $this->_imageHandler    = imagecreatetruecolor($file['x'], $file['y']);
            $this->_imageHeight     = $file['y'];
            $this->_imageWidth      = $file['x'];
            $colors                 = self::html2rgb($this->_fileReference['color']);

            if (isset($this->_fileReference['alpha'])) {
                imagealphablending($this->_imageHandler, 0);
                $color = imagecolorallocatealpha(
                    $this->_imageHandler,
                    $colors[0],
                    $colors[1],
                    $colors[2],
                    $this->_fileReference['alpha']
                );

            } else {

                $color = imagecolorallocate(
                    $this->_imageHandler,
                    $colors[0],
                    $colors[1],
                    $colors[2]
                );
            }

            @imagefilledrectangle(
                $this->_imageHandler,
                0,
                0,
                $this->_imageWidth,
                $this->_imageHeight,
                $color
            );
        } else{ 
            if ($chk && $this->_fileReference['error'] !== UPLOAD_ERR_OK) {
                throw new packageException(
                    'upload_error',
                    $this->_fileReference['error']
                );
            }

            if (!isset($this->_fileReference['binary'])) {
                $this->_getImageType();
            }
            $this->createImage();
        }
    }

    /**
     * change image dimensions
     * with or without keeping aspect ratio
     * 
     * @param integer $imageWidth
     * @param integer $imageHeight
     * @param boolean $ratio if TRUE will keep aspect ratio
     * @param boolean $width aspect ratio force (if TRUE force by width, ale height)
     * @example resize(100, 200, TRUE)
     * @example resize(100, 200)
     * @example resize(600, 400, TRUE, FALSE)
     * @throws packageException('resample_error')
     */
    public function resize($imageWidth, $imageHeight, $ratio = FALSE, $width = TRUE)
    {
        if ($ratio) {
            if (($this->_imageHeight < $this->_imageWidth) && $width) {
                $newImage = imagecreatetruecolor(
                    $imageWidth,
                    ($imageWidth/$this->_imageWidth) * $this->_imageHeight
                );
            } else {
                if ($width) {
                    $newImage = imagecreatetruecolor(
                        $imageWidth,
                        ($imageWidth/$this->_imageWidth) * $this->_imageHeight)
                    ;
                } else {
                    $newImage = imagecreatetruecolor(
                        ($imageHeight/$this->_imageHeight) * $this->_imageWidth,
                        $imageHeight
                    );
                }
            }
        } else {
            $newImage = imagecreatetruecolor($imageWidth, $imageHeight);
        }

        imagealphablending($newImage, 0);
        $newX   = imagesx($newImage);
        $newY   = imagesy($newImage);
        $bool   = @imagecopyresampled(
            $newImage,
            $this->_imageHandler,
            0,
            0,
            0,
            0,
            $newX,
            $newY,
            $this->_imageWidth,
            $this->_imageHeight
        );

        if (!$bool) {
            throw new packageException('resample_error');
        }

        $this->_imageHeight     = $newY;
        $this->_imageWidth      = $newX;
        $this->_imageHandler    = $newImage;
    }

    /**
     * save images in given directory, with given format
     * or return data to directly display
     * 
     * @param string|boolean $destination file destination or FALSE if must be to display
     * @param string $type file type (jpg/jpeg, gif, png, bmp)
     * @param boolean $extension if TRUE add extension to file, defined in $type variable
     * @example save('folder/podfolder/nowa_nazwa.jpg', 'jpg')
     * @example save('folder/podfolder/nowa_nazwa', 'jpeg', 1)
     * @throws packageException unknow_file_type, save_error
     */
    public function save($destination, $type, $extension = FALSE)
    {
        if ($destination) {
            if ($extension) {
                $destination = $destination . '/' . $type;
            }
        } else {
            $location = NULL;
        }

        switch ($type) {
            case "jpg":case "jpeg":
                $bool = imagejpeg(
                    $this->_imageHandler,
                    $destination,
                    $this->jpgQuality
                );
                break;
            
            case "gif":
                $bool = imagegif($this->_imageHandler, $destination);
                break;
            
            case "png":
                if ($this->pngFilter) {
                    $bool = imagepng(
                        $this->_imageHandler,
                        $destination,
                        $this->pngQuality,
                        $this->pngFilter
                    );
                } else {
                    $bool = imagepng(
                        $this->_imageHandler,
                        $destination,
                        $this->pngQuality
                    );
                }
                break;
            
            case "bmp":
                $bool = imagewbmp($this->_imageHandler, $destination);
                break;
            
            default:
                throw new packageException('unknow_file_type', $type);
                break;
        }

        if (!$bool) {
            throw new packageException('save_error', $destination);
        }
    }

    /**
     * return image size in bytes, and ir binary representation
     * allow to add escape sequences for binary image representation
     * 
     * @param string $type image type (jpg, png, gif...)
     * @param boolean $slashes if TRUE add escape sequences
     * @return array (size, bin)
     * @throws packageException tmp_file_open_error
     */
    public function binary($type, $slashes = FALSE)
    {
        $this->_checkTempDirectory();
        $arr = array();

        $this->save($this->tmpLocation, $type, 'temp_image' . $this->_time);
        $f = @fopen($this->tmpLocation . '/temp_image' . $this->_time, 'r');

        if (!$f) {
            throw new packageException(
                'tmp_file_open_error',
                $this->tmpLocation . '/temp_image' . $this->_time
            );
        }

        $arr['size']    = @filesize($this->tmpLocation.'/temp_image'.$this->_time);
        $arr['bin']     = fread($f, $arr['size']);

        if ($slashes) {
            $arr['bin'] = addslashes($arr['bin']);
        }

        fclose($f);
        return $arr;
    }

    /**
     * create image handler to process inside class
     * allow to make many operations on same file
     * like get many resolutions form one image class
     * 
     * @throws packageException invalid_extension, create_error, tmp_file_open_error
     */
    public function createImage()
    {
        if ($this->_imageHandler) {
            @imagedestroy($this->_imageHandler);
        }

        if (!$this->_imageType) {
            $type = 'binary';
        } else {
            $type = $this->_imageType;
        }

        switch ($type) {
            case ".jpg":case ".jpeg":
                $this->_imageHandler = @imagecreatefromjpeg(
                    $this->_fileReference['name']
                );
                break;

            case ".gif":
                $this->_imageHandler = @imagecreatefromgif(
                    $this->_fileReference['name']
                );
                break;

            case ".png":
                $this->_imageHandler = @imagecreatefrompng(
                    $this->_fileReference['name']
                );
                break;

            case ".bmp":
                $this->_imageHandler = @imagecreatefromwbmp(
                    $this->_fileReference['name']
                );
                break;

            case "binary":
                $binaryData             = base64_decode($this->_fileReference['name']);
                $this->_imageHandler    = @imagecreatefromstring($binaryData);

                if ($this->_imageHandler) {
                    $this->_checkTempDirectory();
                    $f = @fopen(
                        $this->tmpLocation . '/temp_image' . $this->_time, 'r'
                    );

                    if (!$f) {
                        throw new packageException(
                            'tmp_file_open_error',
                            $this->tmpLocation . '/temp_image' . $this->_time
                        );
                    }

                    @fclose($f);
                    @file_put_contents(
                        $this->tmpLocation . '/temp_image' . $this->_time,
                        $binaryData
                    );

                    $this->_imageType = image_type_to_extension(
                        exif_imagetype(
                            $this->tmpLocation . '/temp_image' . $this->_time
                        )
                    );
                }
                break;

            default:
                throw new packageException('invalid_extension', $type);
                break;
        }

        if (!$this->_imageHandler){
            throw new packageException('create_error', $type);
        }

        $this->_imageWidth  = imagesx($this->_imageHandler);
        $this->_imageHeight = imagesy($this->_imageHandler);
    }

    /**
     * return current image, image information, or its handler to use other functions
     * 
     * @param string $type data type to return (img, width, height, mime, type)
     * @return mixed
     */
    public function returns($type)
    {
        switch ($type) {
            case'img':
                return $this->_imageHandler;
                break;

            case'width':
                return $this->_imageWidth;
                break;
 
            case'height':
                return $this->_imageHeight;
                break;

            case'mime':
                return $this->_mime;
                break;
            
            case'type':
                return $this->_imageType;
                break;

            default:
                return NULL;
        }
    }

    /**
     * rotate image correct with clockwise rotation
     * after rotation image is adapted to dimensions
     * 
     * @param integer $degree (0 to 360) or (-360 to 0)
     * @param string $backgroundColor background color in hex values (#xxxxxx)
     * @param integer $transparent
     */
    public function rotate($degree, $backgroundColor, $transparent = 0)
    {
        $colors     = self::html2rgb($backgroundColor);
        $color      = imagecolorallocate(
            $this->_imageHandler,
            $colors[0],
            $colors[1],
            $colors[2]
        );

        $this->_imageHandler = imagerotate(
            $this->_imageHandler,
            $degree,
            $color,
            $transparent
        );
    }

    /**
     * allow to crop image by giving start point and dimensions
     * 
     * @param integer $startX
     * @param integer $startY
     * @param integer $width 
     * @param integer $height
     * @example crop(0, 0, 100, 200)
     * @throws packageException crop_error
     */
    public function crop($startX, $startY, $width, $height)
    {
        $newImage   = imagecreatetruecolor($width, $height);
        $bool       = imagecopy (
            $newImage,
            $this->_imageHandler,
            0,
            0,
            $startX,
            $startY,
            $width,
            $height
        );

        if (!$bool) {
            throw new packageException(
                'crop_error',
                $startX . '-' . $startY . '-' . $width . '-' . $height
            );
        }

        $this->_imageHandler = $newImage;
    }

    /**
     * allow to put one image in another
     * 
     * @param image_class $handlerObject
     * @param integer $xPosition
     * @param integer $yPosition
     * @throws packageException merge_error
     */
    public function placeImage(image_class $handlerObject, $xPosition, $yPosition)
    {
        $handler        = $handlerObject->returns('img');
        $imageWidth     = imagesx($handler);
        $imageHeight    = imagesy($handler);
        $bool           = imagecopy(
            $this->_imageHandler,
            $handler,
            $xPosition,
            $yPosition,
            0,
            0,
            $imageWidth,
            $imageHeight
        );
        
        if (!$bool) {
            throw new packageException(
                'merge_error',
                $xPosition . '-' . $yPosition . '-' . $imageWidth . '-' . $imageHeight
            );
        }
    }

    /**
     * add some text to image
     * 
     * @param string $string
     * @param int $xPosition
     * @param integer $yPosition
     * @param string $color hex color (#xxxxxx)
     * @param integer $size font size (on points or pixels, depends on GD version)
     * @param string $font
     * @param integer $angle (0-from left to right, 90-from top to down), default: 0
     * @return array|boolean array of string position, or FALSE if some error occurred
     * @example text('some string', 0, 20, '#ff0000', 12, 'arial.ttf')
     * @example text('some string', 0, 20, '#ff0000', 12, 'arial.ttf', 90)
     */
    public function text(
        $string,
        $xPosition,
        $yPosition,
        $color,
        $size,
        $font,
        $angle = 0
    ){
        $color = self::html2rgb($color);
        $color = imagecolorallocate(
            $this->_imageHandler,
            $color[0],
            $color[1],
            $color[2]
        );

        return imagettftext(
            $this->_imageHandler,
            $size,
            $angle,
            $xPosition,
            $yPosition,
            $color,
            $font,
            $string
        );
    }

    /**
     * close image handling and removes temporary file
     */
    public function __destruct()
    {
        @imagedestroy($this->_imageHandler);
        $fileLocation = $this->tmpLocation . '/temp_image' . $this->_time;
        
        if (file_exists($fileLocation)) {
            @unlink($fileLocation);
        }
    }

    /**
     * get information about image, its type, mime, width, height
     */
    protected function _getImageType()
    {
        $properties         = getimagesize($this->_fileReference['name']);
        $this->_mime        = $properties['mime'];
        $this->_imageType   = image_type_to_extension($properties[2]);
        $this->_imageWidth  = $properties[0];
        $this->_imageHeight = $properties[1];
    }

    /**
     * check that temporary directory exists, if not create it
     * 
     * @throws packageException tmp_create_error
     */
    protected function _checkTempDirectory()
    {
        if (!file_exists($this->tmpLocation)) {
            $bool = mkdir($this->tmpLocation, 0777);

            if (!$bool) {
                throw new packageException(
                    'tmp_create_error',
                    $this->tmpLocation
                );
            }
        }
    }

    /**
     * convert RGB values to hex
     * 
     * @param integer|array $red color or array of colors
     * @param integer $green
     * @param integer $blue
     * @return string return hex color (#xxxxxx)
     * @example rgb2html(1, 23, 56)
     * @example rgb2html(array(1, 23, 56))
     */
    static function rgb2html($red, $green = -1, $blue = -1)
    {
        if (is_array($red) && sizeof($red) === 3) {
            list($red, $green, $blue) = $red;
        }

        $red    = intval($red);
        $green  = intval($green);
        $blue   = intval($blue);

        $red    = dechex($red < 0 ? 0: ($red > 255 ? 255: $red));
        $green  = dechex($green < 0 ? 0: ($green > 255 ? 255: $green));
        $blue   = dechex($blue < 0 ? 0: ($blue > 255 ? 255: $blue));

        $color  = (strlen($red) < 2 ? '0': '').$red;
        $color .= (strlen($green) < 2 ? '0': '').$green;
        $color .= (strlen($blue) < 2 ? '0': '').$blue;

        return '#' . $color;
    }

    /**
     * convert hex color value to RGB
     * 
     * @param string $color hex color in #xxxxxx or xxxxxx
     * @return array
     */
    static function html2rgb($color)
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) === 6) {
            list($r, $g, $b) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            );
        } elseif (strlen($color) === 3) {
            list($r, $g, $b) = array(
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2]
            );
        } else {
            return FALSE;
        }

        $r = hexdec($r);
        $g = hexdec($g); 
        $b = hexdec($b);
        return array($r, $g, $b);
    }
}
