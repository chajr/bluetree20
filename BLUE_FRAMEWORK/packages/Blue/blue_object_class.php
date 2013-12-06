<?php
/**
 * blue_object_class_class
 * create basically object to store data or models and allows to easily access to object
 * Blue Object will use almost all objects in Blue Framework
 *
 * @category    BlueFramework
 * @package     Blue
 * @subpackage  Object
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.1.0
 * 
 * 
 * kiedy obiekt zaiinicjalizowany juz z danymi i dodane zostaja dane, czy ma ustawic original data na NULL??
 * tak aby przy przywracaniu i sprawdzaniu original nowe dane nie istanialy
 * 
 * 
 */
class blue_object_class
{
    /**
     * if there was some errors in object, that variable will be set on true
     *
     * @var bool
     */
    protected $_hasErrors = FALSE;

    /**
     * will contain list of all errors that was occurred in object
     * 
     * 0 => ['error_key' => 'error information']
     *
     * @var array
     */
    protected $_errorsList = [];

    /**
     * array with main object data
     * @var array
     */
    protected $_DATA = [];

    /**
     * keeps data before changes (set only if some data in $_DATA was changed)
     * @var
     */
    protected $_originalDATA = [];

    /**
     * @var array
     */
    protected static $_cacheKeys = [];

    /**
     * @var boolean
     */
    protected $_dataChanged = FALSE;

    /**
     * create new Blue Object, optionally with some data
     * there are some types we can give to convert data to Blue Object
     * like: json, xml, default is array
     *
     * @param mixed $data
     * @param string|null $type
     */
    public function __construct($data = NULL, $type = NULL)
    {
        $this->initializeObject();

        switch ($type) {
            case 'json':
                $this->_appendJson($data);
                break;

            case 'xml':
                $this->_appendXml($data);
                break;

            default:
                $this->_appendArray($data);
                break;
        }

        $this->afterInitializeObject();
    }

    /**
     * return from DATA value for given object attribute
     *
     * @param string $key
     * @return mixed
     */

    public function __get($key)
    {
        $key = $this->_convertKeyNames($key);
        return $this->getData($key);
    }

    /**
     * save into DATA value given as object attribute
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $key = $this->_convertKeyNames($key);
        $this->_putData($key, $value);
    }

    /**
     * check that variable exists in DATA table
     * 
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        $key = $this->_convertKeyNames($key);
        return $this->hasData($key);
    }

    /**
     * remove given key from DATA
     * 
     * @param $key
     */
    public function __unset($key)
    {
        $key = $this->_convertKeyNames($key);
        $this->unsetData($key);
    }

    /**
     * allow to access DATA keys by using special methods
     * like getSomeData() will return _DATA['some_data'] value or
     * setSomeData('val') will create DATA['some_data'] key with 'val' value
     * 
     * @param string $method
     * @param array $arguments
     * @return blue_object_class|bool|mixed
     */
    public function __call($method, $arguments)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->_convertKeyNames(substr($method, 3));
                if (isset($arguments[0])) {
                    return $this->getData($key, $arguments[0]);
                }
                return $this->getData($key);

            case 'set':
                $key = $this->_convertKeyNames(substr($method, 3));
                if (isset($arguments[0])) {
                    return $this->setData($key, $arguments[0]);
                }
                return $this->setData($key);

            case 'has':
                $key = $this->_convertKeyNames(substr($method, 3));
                return $this->hasData($key);

            default:
                $method = substr($method, 0, 5);
                if ($method === 'unset') {
                    $key = $this->_convertKeyNames(substr($method, 5));
                    return $this->unsetData($key);
                }

                $this->_errorsList[] = [
                    'wrong_method' => get_class($this) . ' - ' . $method
                ];
                return FALSE;
        }
    }

    /**
     * return DATA content if try to access object by var_export() function
     * 
     * @return mixed
     */
    public function __set_state()
    {
        return $this->getData();
    }

    /**
     * return object data as string representation
     * 
     * @return string
     */
    public function __toString()
    {
        $this->_prepareData();
        return implode(', ', $this->getData());
    }

    /**
     * return boolean information that object has some error
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->_hasErrors;
    }

    /**
     * return single error by key, ora list of all errors
     *
     * @param string $key
     * @return mixed
     */
    public function getObjectError($key)
    {
        if ($key) {
            return $this->_errorsList[$key];
        }

        return $this->_errorsList;
    }

    /**
     * remove single error, or all object errors
     *
     * @param string $key
     * @return blue_object_class
     */
    public function clearObjectError($key)
    {
        if ($key) {
            unset ($this->_errorsList[$key]);
        }

        $this->_errorsList = [];

        return $this;
    }

    /**
     * return serialized object data
     *
     * @param boolean $skipObjects
     * @return string
     */
    public function serialize($skipObjects = FALSE)
    {
        $this->_prepareData();
        $temporaryData = $this->getData();

        if ($skipObjects) {
            $temporaryData = [];

            foreach ($this->_DATA as $key => $value) {
                if (is_object($value)) {
                    $temporaryData[$key] = '{;skipped_object;}';
                } else {
                    $temporaryData[$key] = $value;
                }
            }
        }

        return serialize($temporaryData);
    }

    /**
     * return data for given key if exist in object, or all object data
     *
     * @param null|string $key
     * @return mixed
     */
    public function getData($key = NULL)
    {
        $this->_prepareData();

        if (!$key) {
            return $this->_DATA;
        }

        if (isset($this->_DATA[$key])) {
            return $this->_DATA[$key];
        }

        return NULL;
    }

    /**
     * set some data in object
     * can give pair key=>value or array of keys
     *
     * @param string|array $key
     * @param mixed $data
     * @return blue_object_class
     */
    public function setData($key, $data = NULL)
    {
        if(is_array($key)) {
            foreach ($key as $dataKey => $data) {
                $this->_putData($dataKey, $data);
            }

        } else {
            $this->_putData($key, $data);
        }

        return $this;
    }

    /**
     * return original data for key, before it was changed
     *
     * @param null|string $key
     * @return mixed
     */
    public function getOriginalData($key = NULL)
    {
        $this->_prepareData();

        if (!$key) {
            return array_merge($this->_DATA, $this->_originalDATA);
        }

        if (isset($this->_originalDATA[$key])) {
            return $this->_originalDATA[$key];

        } else if (isset($this->_DATA[$key])) {
            return $this->_DATA[$key];
        }

        return NULL;
    }

    /**
     * check if data with given key exist in object, or object has some data
     * if key wast given
     *
     * @param null|string $key
     * @return bool
     */
    public function hasData($key = NULL)
    {
        if (!$key && !empty($this->_DATA)) {
            return TRUE;
        }

        if (isset($this->_DATA[$key])) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * check that given data and data in object are the same
     * checking by === operator
     * possibility to compare with origin data
     *
     * @param string|array $key
     * @param mixed $dataToCheck
     * @param boolean $origin
     * @return bool
     */
    public function compareData($key, $dataToCheck, $origin = NULL)
    {
        if (is_array($key)) {
            if ($origin) {
                if ($dataToCheck === $this->_originalDATA) {
                    return TRUE;
                }
            } else {
                if ($dataToCheck === $this->_DATA) {
                    return TRUE;
                }
            }
        } else {
            if ($origin) {
                if (isset($this->_originalDATA[$key])) {
                    if ($dataToCheck === $this->_originalDATA[$key]) {
                        return TRUE;
                    }
                } else if ($dataToCheck === NULL) {
                    return TRUE;
                }
            } else {
                if (isset($this->_DATA[$key])) {
                    if ($dataToCheck === $this->_DATA[$key]) {
                        return TRUE;
                    }
                } else if ($dataToCheck === NULL) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * destroy key entry in object data, or whole keys
     * automatically set data to original array
     *
     * @param string|null $key
     * @return blue_object_class
     */
    public function unsetData($key = NULL)
    {
        if ($key === NULL) {
            $this->_dataChanged  = TRUE;
            $mergedData          = array_merge($this->_DATA, $this->_originalDATA);
            $this->_originalDATA = $mergedData;
            $this->_DATA         = [];

        } elseif (isset($this->_DATA[$key])) {
            $this->_dataChanged = TRUE;
            $this->_setInOriginData($key, $this->_DATA[$key]);
            unset ($this->_DATA[$key]);
        }

        return $this;
    }

    /**
     * set object key data to NULL
     *
     * @param string $key
     * @return blue_object_class
     */
    public function clearData($key)
    {
        $this->_putData($key, NULL);
        return $this;
    }

    /**
     * replace changed data by original data
     *
     * @param string $key
     * @return blue_object_class
     */
    public function restoreData($key)
    {
        if ($key === NULL) {
            $mergedData     = array_merge($this->_DATA, $this->_originalDATA);
            $this->_DATA    = $mergedData;
        } else {
            if (isset($this->_originalDATA[$key])) {
                $this->_DATA[$key] = $this->_originalDATA[$key];
            }
        }

        return $this;
    }

    /**
     * return object as string
     * each data value separated by coma
     * 
     * @return string
     */
    public function toString()
    {
        $this->_prepareData();
        return $this->__toString();
    }

    /**
     * return data as json string
     * 
     * @return string
     */
    public function toJson()
    {
        $this->_prepareData();
        return json_encode($this->getData());
    }

    /**
     * return object data as xml representation
     * 
     * @param bool $addCdata
     * @param string|boolean $dtd
     * @param string $version
     * @return string
     */
    public function toXml($addCdata = TRUE, $dtd = FALSE, $version = '1.0')
    {
        $this->_prepareData();

//        $xml = '<?xml version="' . $version . ' encoding="UTF-8"? >' . "\n";
//        if (!empty($rootName)) {
//            $xml .= "<$rootName>\n";
//        }
//
//        foreach ($this->_DATA as $key => $value) {
//            if (is_object($value) || is_array($value)) {
//                $value = serialize($value);
//            }
//
//            if ($addCdata) {
//                $value = "<![CDATA[$value]]>";
//            }
//            $key = str_replace(' ', '_', $key);
//
//            $xml .= "<$key>$value</$key>\n";
//        }
//
//        if (!empty($rootName)) {
//            $xml .= "</$rootName>\n";
//        }
        $dtd = '';
        $xml = new xml_class($version, 'UTF-8');

        if ($dtd) {
            $dtd = "<!DOCTYPE root SYSTEM '$dtd'>";
        }

        $xml = $this->_arrayToXml($this->_DATA, $xml, $addCdata, $xml);
        //$xml->
        
        //atrybuty trzymane w tablicy attributes
        //dane z noda trzymane w tablicy cdata

        return $dtd . $xml->saveXmlFile(FALSE, TRUE);
    }

    /**
     * return object attributes as array
     * without DATA
     * 
     * @return mixed
     */
    public function toArray()
    {
        $attributesArray = [];

        foreach ($this as $name => $value) {
            if ($name === '_DATA') {
                continue;
            }

            $attributesArray[$name] = $value;
        }

        return $attributesArray;
    }

    /**
     * return information that some data was changed in object
     *
     * @return bool
     */
    public function hasDataChanged()
    {
        return $this->_dataChanged;
    }

    /**
     * apply given json data as object data
     * 
     * @param string $data
     * @return $this blue_object_class
     */
    protected function _appendJson($data)
    {
        $jsonData = json_decode($data);

        if ($jsonData) {
            $this->setData($jsonData);
        }

        return $this;
    }

    protected function _appendXml()
    {
        //tylko plaskie xmle bez atrybutow??
        //jesli ma atrybutu tworzy array z nimi
        
        //zrobic to tak aby mozna bylo obslugiwac w append i save drzewo z tree.xml
    }

    /**
     * set data given in constructor
     *
     * @param mixed $data
     * @return blue_object_class
     */
    protected function _appendArray($data)
    {
        if (is_array($data)) {
            $this->_DATA = $data;
        } else {
            $this->_DATA['default'] = $data;
        }

        return $this;
    }

    /**
     * set original data, only if doest exists
     *
     * @param string $key
     * @param mixed $data
     * @return blue_object_class
     */
    protected function _setInOriginData($key, $data)
    {
        if (    isset($this->_DATA[$key])
            && !isset($this->_originalDATA[$key])
        ) {
            $this->_originalDATA[$key] = $data;
        }

        return $this;
    }

    /**
     * insert single key=>value pair into object, with key conversion
     * and set _dataChanged to true
     * also set original data for given key in $this->_originalDATA
     *
     * @param string $key
     * @param mixed $data
     * @return blue_object_class
     */
    protected function _putData($key, $data)
    {
        $this->_dataChanged = TRUE;
        $this->_setInOriginData($key, $data);
        $this->_DATA[$key] = $data;

        return $this;
    }

    /**
     * convert given object data key (given as came case method)
     * to proper construction
     *
     * @param string $key
     * @return string
     */
    protected function _convertKeyNames($key)
    {
        if (isset(self::$_cacheKeys[$key])) {
            return self::$_cacheKeys[$key];
        }

        $convertedKey = strtolower(
            preg_replace('/(.)([A-Z0-9])/', "$1_$2", $key
        ));
        self::$_cacheKeys[$key] = $convertedKey;
        return $convertedKey;
    }

    /**
     * recursive method to create structure xml structure of object DATA
     * 
     * @param $data
     * @param xml_class $xml
     * @param boolean $addCdata
     * @param xml_class|DOMElement $parent
     * @return mixed
     */
    protected function _arrayToXml($data, xml_class $xml, $addCdata, $parent)
    {
        foreach ($data as $key => $value) {
            $key = str_replace(' ', '_', $key);
            if (is_object($value)) {
                $value = serialize($value);
            }

            
            //sprawdzenie czy ma tablice attributes
            //tworzy atrybuty dla elkementu i usuwa z temp data tak aby element nie byl juz tablica
            if (isset($value['attributes'])) {
                
                unset ($value['attributes']);
            }

            if (is_array($value)) {
                $children = $xml->createElement($key);
                $this->_arrayToXml($value, $xml, $addCdata, $children);
                $parent->appendChild($children);
                continue;
            }

            if ($addCdata) {
                $cdata      = $xml->createCDATASection($value);
                $element    = $xml->createElement($key);
                $element->appendChild($cdata);
            } else {
                $element = $xml->createElement($key, $value);
            }

            $parent->appendChild($element);
        }

        return $xml;
    }

    protected function _traveler($method, $methodAttributes, $data = NULL)
    {
        if (!$data) {
            $data = $this->_DATA;
        }

        foreach ($data as $key => $value) {
            if (is_object($value)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->_traveler($method, $methodAttributes, $key, $value);
            } else {
                $this->$method($key, $value, $methodAttributes);
            }
        }

        return $data;
    }

    /**
     * can be overwritten by children objects to start with some special
     * operations
     */
    public function initializeObject(){}

    /**
     * can be overwritten by children objects to start with some special
     * operations
     */
    public function afterInitializeObject(){}

    /**
     * can be overwritten by children objects to make some special process on
     * data before return
     */
    protected function _prepareData(){}
}
