Framework benchmark
====================
Blue framework has included two libraries responsible for framework benchmark.
One is responsible for checking performance (time that interpreter spend on class
or methods run, memory usage), and second to check step by step witch methods are
started and what parameters they have. Both of them are included by default and
can be turned off for whole framework only in `index.php` file.

Framework performance
--------------
Allow to check how many time interpreter spend to lunch given part of code, how
much memory was used and percent value of used whole framework time.  
To start performance we must load `benchmark_class` library from `tester` package.
That library is default loaded in `index.php` and started by `benchmark_class::start();`
method.  
To check some part of code we paste some marker inside code with some values and
benchmark class will return how much time interpreter spend on working for previous
marker, or from `start()` method.

```php
benchmark_class::start();
starter_class::run();
benchmark_class::setMarker('the end');
```

That example will return information about working of `starter_class::run();`
method.  
If we have a lot of markers we can organize them to group. Then all markers will
work normal, but at the end of group we get time and memory usage value for that
group.

```php
benchmark_class::startGroup('html group');
$this->_htmlPage();
benchmark_class::endGroup('html group');
```

Inside `_htmlPage()` method we have some markers, and `endGroup()` method will
collect all that marker values and return sum of it. In start group and end group
method we must give the same value (what is group name), because there is possibility
to make group inside another group.

To display values of marker use:

```php
echo benchmark_class::stop();
echo benchmark_class::display();
```

benchmark_class methods:

1. `start()` - start collecting markers data _(if we give param value FALSE, then benchmark will be off)_
2. `setMarker()` - set marker _(some marker description)_
3. `startGroup()` - start collecting markers data in group _(group name)_
4. `endGroup()` - finish counting given group of markers_(group name)_
5. `stop()` - stop working benchmark_class and set final time
6. `display()` - show table of marker values
7. `turnOffBenchmark()` - turn off benchmark, all next markers wont be collected, `display()` return nothing
8. `turnOnBenchmark()` - turn on benchmark to normal work

_All benchmark methods are static._

![Framework benchmark content example](/image/benchmark.png "Benchmark")

Trace of method usage
--------------
That library allow to retrieve information what framework exactly do step by step
with such information as script file, run method, given to method parameters etc.  
That information can be very useful when we want debug, and check how framework
work (also module).  
To start tracer we must load `tracer_class` library from `tester` package.
That library is default loaded in `index.php` and don't need to be started, markers
are stored automatically.
To check some part of code we paste some marker inside code with some values and
tracer class will return information about method that is started with that marker.

```php
static final function run($params)
{
    tracer_class::marker(array('step info', debug_backtrace()));
}
```

That is basic tracer class marker usage.

Like in benchmark class we can group tracer to groups (like one class == one group)
by using colors for tracer information background. To use that just paste as third
parameter color hex value:

```php
tracer_class::marker(array('step info', debug_backtrace(), '#006C94'));
```

To display values of marker just use:

```php
echo tracer_class::display();
```

benchmark_class methods:

1. `start()` - optional method, will start tracer with `Tracer started` message _(if we give param value FALSE, then tracer will be off wont work)_
2. `marker()` - set marker _(array(marker name, debug backtrace info, group color))_
3. `stop()` - add information `Tracer ended`
4. `display()` - return full tracing data as html content
5. `saveToFile()` - save tracer information to file using `error_class::log()`, file is localized in `BLUE_FRAMEWORK/log/tracer*`
6. `turnOffBenchmark()` - turn off tracer, all next markers wont be collected, `display()` return nothing
7. `turnOnBenchmark()` - turn on tracer to normal work

_All tracer methods are static._

1. **#000000** ![image black](/image/000000.png "#000000") starter class
2. **#6d6d6d** ![image black](/image/6d6d6d.png "#6d6d6d") default colors
3. **#4d4d4d** ![image black](/image/4d4d4d.png "#4d4d4d") default colors
4. **#006c94** ![image black](/image/006c94.png "#006c94") core class
5. **#7e3a02** ![image black](/image/7e3a02.png "#7e3a02") xml class
6. **#00306a** ![image black](/image/00306a.png "#00306a") get class
7. **#6802cf** ![image black](/image/6802cf.png "#6802cf") language class
8. **#002046** ![image black](/image/002046.png "#002046") post class
9. **#ff0000** ![image black](/image/ff0000.png "#ff0000") error class (start library, not error in page)
10. **#374557** ![image black](/image/374557.png "#374557") session class
11. **#516e91** ![image black](/image/516e91.png "#516e91") files class
12. **#213a59** ![image black](/image/213a59.png "#213a59") cookie class
13. **#004396** ![image black](/image/004396.png "#004396") global class
14. **#bf5702** ![image black](/image/bf5702.png "#bf5702") tree class
15. **#006400** ![image black](/image/006400.png "#006400") display class
16. **#008e85** ![image black](/image/008e85.png "#008e85") loader class
17. **#900000** ![image black](/image/900000.png "#900000") methods responsible for error handling