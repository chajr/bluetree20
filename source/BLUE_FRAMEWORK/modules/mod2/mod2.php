<?php
/**
 * example module number 2
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  mod2
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.1.4
 */
class mod2 
    extends module_class
{
    static $version         = '1.1.4';
    static $name            = '';
    public $require_libs    = array('sql_abstract', 'mysql', 'mysql_connection', 'simpleDate');
    public $require_modules = array();

    /**
     * start module
     */
    public function run()
    {
        //set layout template
        $this->layout('lay');

        $this->_translate();

        //read some data from other module
        if (isset($this->modules['modul1'])) {
            if (isset($this->modules['modul1']->moduleVariable)) {
                $this->generate('marker', $this->modules['modul1']->moduleVariable);
            }
        } else {
            $this->generate('marker', '{;lang;mod_crash;}');
        }

        //runs test of database connection
        $this->connectionTest();

        //usage of image library
        $img = new image_class(array('name'=>'elementy/images/bluetree_base_big.jpg'));
        $img->rotate(90, '#ffffff');
        $img->save('elementy/images/bluetree_base_big_copy.jpg', 'jpg');

        //test dates
        $this->dateTest();

        //rendering of xml form generator
        $this->generate('form', $this->formTest());
    }

    /**
     * test connection with database
     * read and save information, and last inserted id
     */
    public function connectionTest()
    {
        $conn = new mysql_connection_class(array(
            \getenv('BLUETREE_DB_HOST'),
            \getenv('BLUETREE_DB_USER'),
            \getenv('BLUETREE_DB_PASS'),
            \getenv('BLUETREE_DB'),
            \getenv('BLUETREE_DB_PORT'),
        ));

        if ($conn->err) {
            throw new modException('db_conn_error', $conn->err);
        }

        $this->generate('connection', '{;lang;connection_on;}');

        $query = new mysql_class('SELECT * FROM test');        //work with error

        if ($query->err) {
            $this->generate('select', $query->err);
        } else {
            $this->generate('select', $query->rows);
        }

        $query = new mysql_class(
            "INSERT INTO test (string) VALUES ('new test $query->rows')"
        );

        if ($query->err) {
            $this->generate('insert', $query->err);
        } else {
            $this->generate('insert', $query->id);
        }
    }

    /**
     * test dates
     * normal, formatted, differences between dates
     */
    public function dateTest()
    {
        $date = new date_class();

        $this->generate('marker1', $date);
        $this->generate('marker2', $date->getTime());
        $this->generate('marker3', $date->getDay(1));
        $this->generate('marker4', $date->getFormattedTime());
        $this->generate('marker5', $date->getMonthName());
        $this->generate('marker6', $date->getWeek());

        $date->useConversion = 1;
        $this->generate('marker7', $date->getOtherFormats(
            "%A - nazwa tygodnia zgodna z lokalizacją 
            (przy wyłączonej opcji poprawy polskich znaków, dla błędnego
            ustawiania setlocale<br/>
            działa tylko gdy ma konwertować z iso na utf)"
        ));

        $date2  = new date_class(1214257275);
        $diff   = $date->getDifference($date2, 'seconds');
        $this->generate('marker8', $diff);
    }

    /**
     * run form library
     * 
     * @return string
     */
    public function formTest()
    {
        $this->set('form', 'css');
        $this->set('form', 'js');

        $default_array = array(
            'input1'    => array('value' => 'asdfasdas'),
            'input2'    => array('value' => 'ddddddddddddd'),
            'input5'    => array('value' => 34),
            'chka'      => array('checked' => 'checked'),
            'rada'      => array(
                array('class' => 'first'),
                array('class' => 'second', 'checked' => 'checked'),
                array('class' => 'last')
            )
        );

        $form = new form_class('mod2', 'form_definition', $default_array);
        if ($this->post->incoming_form) {
            $bool = $form->valid($this->post);
            if (!$bool) {
                echo '<pre>';
                var_dump($form->error_list);
                echo '</pre>';
            }
        }

        $this->generate('post_serialize', $this->post);
        return $form->display_form();
    }

    public function runErrorMode()
    {
        
    }

    public function install()
    {

    }

    public function uninstall()
    {
        
    }
}
