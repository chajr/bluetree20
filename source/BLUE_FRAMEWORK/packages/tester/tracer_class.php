<?php
/**
 * trace witch classes, files, functions are run
 * 
 * @author Michał Adamiak <chajr@bluetree.pl>
 * @version 1.5.1
 * @copyright chajr/bluetree
 * @package     tester
 * @subpackage  tracer
*/
class tracer_class
{
    /**
     * contains data to display
     * @var string 
     */
    protected static $_display = '';

    /**
     * keep information about marker times
     * @var array
     */
    protected static $_session = array();

    /**
     * information that tracer is on, or off
     * @var boolean 
     */
    protected static $_tracerOn = TRUE;
    
    protected static $_divStyles = [
        'c'            => 'width:3%;float:left;padding:5px 0',
        'name'         => 'width:12%;float:left;padding:5px 0',
        'time'         => 'width:10%;float:left;padding:5px 0',
        'file'         => 'width:16%;float:left;padding:5px 0',
        'line'         => 'width:3%;float:left;padding:5px 0',
        'function'     => 'width:11%;float:left;padding:5px 0',
        'class'        => 'width:15%;float:left;padding:5px 0',
        'type'         => 'width:0%;float:left;padding:5px 0',
        'args'         => 'width:30%;float:left;padding:5px 0',
    ];

    /**
     * if set to false tracing data wont be displayed, for only saving file
     * @var boolean 
     */
    static $display = TRUE;

    /**
     * contains number of step for current given marker
     * @var integer
     */
    static $traceStep = 0;

    /**
     * starting tracing
     * 
     * @param boolean $on
     */
    public static function start($on = TRUE)
    {
        if ($on) {
            self::marker(array('Tracer started'));
        } else {
            self::$_tracerOn = FALSE;
        }
    }

    /**
     * create marker with given data
     * optionally add debug_backtrace and marker background color
     * @param array $data
     * 
     * @example marker(array('marker name'))
     * @example marker(array('marker name', debug_backtrace()))
     * @example marker(array('marker name', debug_backtrace(), '#000000'))
     */
    public static function marker($data)
    {
        $defaultData = array('', NULL, NULL);
        $data = array_merge($data, $defaultData);

        if ((bool)self::$_tracerOn) {
            ++self::$traceStep;

            $time = microtime(TRUE);
            $time = preg_split('#\.|,#', $time);
            if (!isset($time[1])) {
                $time[1] = 0;
            }
            $markerTime = gmstrftime('%d-%m-%Y<br/>%H:%M:%S:', $time[0]) . $time[1];

            if (!$data[1]) {
                $data[1] = array(array(
                    'file'      => '', 
                    'line'      => '', 
                    'function'  => '', 
                    'class'     => '', 
                    'type'      => '', 
                    'args'      => ''
                ));
            }

            if (isset($data[1][0]['args']) && is_array($data[1][0]['args'])) {
                foreach ($data[1][0]['args'] as $arg => $val) {
                    if (is_object($val)) {
                        $data[1][0]['args'][$arg] = serialize($val);
                    }
                }
            }

            self::$_session['markers'][] = array(
                'time'      => $markerTime,
                'name'      => $data[0],
                'debug'     => $data[1],
                'color'     => $data[2]
            );
        }
    }

    /**
     * add information about stop tracing
     */
    public static function stop()
    {
        self::marker(array('Tracer ended'));
    }

    /**
     * return full tracing data as html content
     * 
     * @return string
     */
    public static function display()
    {
        if (self::$_tracerOn && self::$display) {
            self::stop();
            self::$_display = '<div style="
            color: #FFFFFF;
            background-color: #3d3d3d;
            margin: 25px auto;
            width: 99%;
            text-align: center;
            padding:1%;
            overflow:hidden;
            font-size:12px;
            ">';

            self::$_display .= '
                Tracer
                <br /><br />
            ';

            self::$_display .= 'Markers time:<br /><div style="color:#fff;text-align:left">' . "\n";

            self::$_display .= '
                    <div style="background-color:#6D6D6D">
                        <div style="' . self::$_divStyles['c'] . '">C</div>
                        <div style="' . self::$_divStyles['name'] . '">Name</div>
                        <div style="' . self::$_divStyles['time'] . '">Time</div>
                        <div style="' . self::$_divStyles['file'] . '">File</div>
                        <div style="' . self::$_divStyles['line'] . '">Line</div>
                        <div style="' . self::$_divStyles['function'] . '">Function</div>
                        <div style="' . self::$_divStyles['class'] . '">Class</div>
                        <!--<div style="' . self::$_divStyles['type'] . '">T</div>-->
                        <div style="' . self::$_divStyles['args'] . '">Arguments</div>
                        <div style="clear:both"></div>
                    </div>
                ';

            $l = 0;
            foreach (self::$_session['markers'] as $marker) {
                if ($l %2) {
                    $background = 'background-color:#4D4D4D';
                } else {
                    $background = '';
                }

                if ($marker['color']) {
                    $background = 'background-color:' . $marker['color'];
                }

                self::$_display .= '<div style="' . $background . '">
                    <div style="' . self::$_divStyles['c'] . '">' 
                    . ++$l . '</div>'."\n";

                self::$_display .= '<div style="' . self::$_divStyles['name'] . '">' 
                    . $marker['name'] . '</div>'."\n";

                self::$_display .= '<div style="' . self::$_divStyles['time'] . '">' 
                    . $marker['time'] . '</div>'."\n";

                self::$_display .= '<div style="' . self::$_divStyles['file'] . '">' 
                    . $marker['debug'][0]['file'] . '</div>'."\n";

                self::$_display .= '<div style="' . self::$_divStyles['line'] . '">' 
                    . $marker['debug'][0]['line'] . '</div>'."\n";

                self::$_display .= '<div style="' . self::$_divStyles['function'] . '">' 
                    . $marker['debug'][0]['function'] . '</div>'."\n";

                self::$_display .= '<div style="' . self::$_divStyles['class'] . '">' 
                    . $marker['debug'][0]['class'] . '</div>'."\n";

//                self::$_display .= '<div style="' . self::$_divStyles['type'] . '">' 
//                    . $marker['debug'][0]['type'] . '</div>'."\n";

                self::$_display .= '<div style="' . self::$_divStyles['args'] . '"><pre>' 
                    . var_export($marker['debug'][0]['args'], TRUE) 
                    . ' </pre></div>'."\n";

                self::$_display .= '<div style="clear:both"></div></div>';
            }
            self::$_display .= '</div></div>';
        }
        return self::$_display;
    }

    /**
     * save tracing data to log file
     */
    public static function saveToFile()
    {
        if (self::$_tracerOn) {
            self::display();
            self::$_display .= '<pre>' . var_export($_SERVER, TRUE) . '</pre>';
            error_class::log('tracer', self::$_display);
        }
    }

    /**
     * turn off tracer
     */
    public static function turnOffTracer()
    {
        self::$_tracerOn = FALSE;
    }

    /**
     * turn on tracer
     */
    public static function turnOnTracer()
    {
        self::$_tracerOn = TRUE;
    }
}
