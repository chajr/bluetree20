<?php
/**
 * example module number 2
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  mod2
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.1.0
 */
class mod2 
    extends module_class
{
    static $version         = '';
    static $name            = '';
    public $require_libs    = array('abstractSql', 'mysql', 'simpleDate');
    public $require_modules = array();

    /**
     * start module
     */
    public function run()
    {
        //set layout template
        $this->layout('lay');

        //read some data from other module
        if (isset($this->modules['modul1'])) {
            if (isset($this->modules['modul1']->moduleVariable)) {
                $this->generate('marker', $this->modules['modul1']->moduleVariable);
            }
        } else {
            $this->generate('marker', 'module1 crash and we don\'t have any information :(');
        }

        //runs test of database connection
        $this->connectionTest();

        //usage of image library
        $img = new image_class(array('name'=>'elementy/Right.png'));
        $img->rotate(90, '#ffffff');
        $img->save('elementy/Right_copy.jpg', 'jpg');

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
            'zmp.nazwa.pl', 'zmp_5', 'WE244ef43%$', 'zmp_5'
        ));

        if ($conn->err) {
            throw new modException('db_conn_error', $conn->err);
        } else {
            $this->generate('connection', 'Connection established');
        }

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
            dziuała tylko gdy ma konwertowaćz iso na utf)"
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
