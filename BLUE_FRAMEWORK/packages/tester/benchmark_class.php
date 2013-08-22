<?php
/**
 * allows to check performance of framework
 *
 * @category    BlueFramework
 * @package     tester
 * @subpackage  benchmark
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.5.0
 */
class benchmark_class
{
    /**
     * contains data about benchmark started memory usage
     * @var integer
     */
    protected static $_sessionMemoryStart = 0;

    /**
     * contains data about started benchmark session time
     * @var integer
     */
    protected static $_sessionBenchmarkStart = 0;

    /**
     * contains data about benchmark marker time
     * @var integer
     */
    protected static $_sessionBenchmarkMarker = 0;
    
    /**
     * contains data about benchmark markers
     * @var array
     */
    protected static $_sessionBenchmarkMarkers = array();

    /**
     * contains data about benchmark finish time
     * @var integer
     */
    protected static $_sessionBenchmarkFinish = 0;

    /**
     * contains information that benchmark is on or off
     * @var bool
     */
    protected static $_sessionBenchmarkOn = TRUE;

    /**
     * start benchmark, and set in internal session start time
     * 
     * @param boolean $on
     */
    public static function start($on = TRUE)
    {
        if ($on) {
            $time                           = microtime(TRUE);
            self::$_sessionMemoryStart      = memory_get_usage();
            self::$_sessionBenchmarkStart   = $time;
            self::$_sessionBenchmarkMarker  = $time;
        } else {
            self::$_sessionBenchmarkOn      = FALSE;
        }
    }

    /**
     * setr marker and set in session time of run up to current position
     * 
     * @param string $name name of marker
     */
    public static function setMarker($name)
    {
        if (self::$_sessionBenchmarkOn) {
            $markerTime = microtime(TRUE) - self::$_sessionBenchmarkMarker;

            self::$_sessionBenchmarkMarker    = microtime(TRUE);
            self::$_sessionBenchmarkMarkers[] = array(
                $name,
                $markerTime,
                memory_get_usage()
            );
        }
    }

    /**
     * stop benchmark, and time counting, save last run time
     */
    public static function stop()
    {
        if (self::$_sessionBenchmarkOn) {
            self::$_sessionBenchmarkFinish = microtime(TRUE);
        }
    }

    /**
     * prepare view and display list of markers, their times and percentage values
     */
    public static function display()
    {
        $display = '';
        if (self::$_sessionBenchmarkOn) {
            $display = '<div style="
            color: #FFFFFF;
            background-color: #3d3d3d;
            border-color: #FFFFFF;
            border-width: 1px;
            border-style: solid;
            margin-left: auto;
            margin-right: auto;
            width: 90%;
            text-align: center;
            margin-bottom:25px;
            margin-top:25px;
            ">';

            $benchmarkStartTime = self::$_sessionBenchmarkStart;
            $benchmarkEndTime   = self::$_sessionBenchmarkFinish;
            $total              = ($benchmarkEndTime - $benchmarkStartTime) *1000;
            $formatTime         = number_format($total, 5, '.', ' ');
            $memoryUsage        = memory_get_usage()/1024;

            $display .= 'Total application runtime: '
                . $formatTime 
                . ' ms&nbsp;&nbsp;&nbsp;&nbsp;Total memory usage: '
                . number_format($memoryUsage, 3, ',', '')
                . ' kB<br /><br />';
            $display .= 'Marker times:<br /><table style="width:100%">'."\n";
               foreach (self::$_sessionBenchmarkMarkers as $marker) {
                    $time     = number_format($marker[1] *1000, 5, '.', ' ');
                    $percent  = ($marker[1] / $total) *100000;
                    $percent  = number_format($percent, 5);
                    $ram      = ($marker[2] - self::$_sessionMemoryStart) / 1024;
                    $display .= '<tr><td style="width:40%">' . $marker[0] . '</td>'."\n";
                    $display .= '<td style="width:20%">' . $time . ' ms</td>'."\n";
                    $display .= '<td style="width:20%">' . $percent . ' %</td>'."\n";
                    $display .= '<td style="width:20%">' . number_format($ram, 3, ',', '') . ' kB</td>
                    </tr>'."\n";
            }
            $display .= '</table></div>';
        }
        return $display;
    }

    /**
     * turn off benchmark
     */
    public static function turnOffBenchmark()
    {
        self::$_sessionBenchmarkOn = FALSE;
    }
    
    /**
     * turn on benchmark
     */
    public static function turnOnBenchmark()
    {
        self::$_sessionBenchmarkOn = TRUE;
    }
}
