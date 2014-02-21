<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_libraries
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_libraries
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = 'libraries usage module example';
    public $requireLibraries    = array('image');
    public $requireModules      = array();

    public function run()
    {
        $this->_prepareLayout();
        $this->generate('libraries', var_export($this->core->lib, TRUE));

        $this->_baseLibraryUsage();
        $this->_staticLibraryUsage();
    }

    /**
     * load layout template, run translations
     */
    protected function _prepareLayout()
    {
        $this->layout('index');
        $this->_translate();
    }

    /**
     * run image library and use their methods to rotate image
     */
    protected function _baseLibraryUsage()
    {
        $options = [
            'name'=>'elementy/images/bluetree_base_big.jpg'
        ];

        $img = new image_class($options);
        $img->rotate(rand(10, 170), '#222222');
        $img->save('elementy/images/bluetree_base_big_copy.jpg', 'jpg');
    }

    /**
     * use static methods of validate library
     */
    protected function _staticLibraryUsage()
    {
        $emailCorrect   = 'some@test_email.com';
        $emailIncorrect = 'some_test_email.com';

        $checkFirstEmail    = simpleValid_class::mail($emailCorrect);
        $checkSecondEmail   = simpleValid_class::mail($emailIncorrect);

        $this->generate([
            'first_email'   => strtoupper(var_export($checkFirstEmail, TRUE)),
            'second_email'  => strtoupper(var_export($checkSecondEmail, TRUE)),
        ]);
    }

    public function runErrorMode(){}
}
