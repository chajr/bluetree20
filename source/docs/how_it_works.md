How Blue Framework work exactly
====================
In this documentation is described step by step how module is working from backed
site. To describe it we use example scripts from
[how-to-start](/docs/how-to-start.md "How to start using framework") documentation
and turned on tracer mode, that we can see witch parts of framework are lunched.

Start framework
--------------
index.php - that file is our main file that handle all framework elements and
is always lunched in every request that will return some html, cdd or js content.  
If we open that file we will se some procedural code that is responsible for
starting framework and some additional elements. First we have loading of benchmark
libraries, we do this manually, because framework is not lunched yet.

```php
starter_class::load('packages/tester/benchmark_class.php');
starter_class::load('packages/tester/tracer_class.php');
benchmark_class::start();
```

Next we have loading of `error_class` to handle all error that can be occurs
inside of script, and we check that class was loaded properly.

```php
$bool = starter_class::load('packages/CORE/error_class.php');
if (!$bool) {
    die ('missing error_class :(');
}
```

After that we start page buffering and use special function `fatal` form
`error_class` that runs `error_class::fatal()` file to check buffer content that
some PHP errors was occurs.  
After load error handling and start buffering we can star our framework core. But
first we set our first marker `start error handling and session`. And after that
we have `starter_class::run();` that will lunch our framework. So now we go inside
that method and let see what is happening there.  
Inside of that method we have our first trace marker that set information about
start framework. But if we look ona tracer list we see that our tracer is on second
place and first is tracer about loading `error_class`. That because `starter_class::load()`
has own tracer handling that is called when framework load some file. But only if
`tracer_class` exist, so we have only information about loading `error_class`.  
Next we set error handling for PHP errors and block try/catch that will load all
required for framework files, start framework core, handle errors if there was
some and show rendered content.

```php
try {
    $bool = starter_class::package('CORE');
    benchmark_class::setMarker('load core package');

    if (!$bool) {
        throw new coreException('core_error_0');
    }

    benchmark_class::startGroup('core group');
    $core = new core_class();
    benchmark_class::endGroup('core group');

    if ($core->render !== NULL && !empty($core->render) && $core->render){
        echo $core->render;
        unset($core->render);
    } else {
        throw new coreException ('core_error_1');
    }

} catch (coreException $errorCore) {
    $errorCore->showCore();
}
```

After display content of some critical framework errors we set last benchmark
marker `the end`, closing buffer and send it output to browser, close benchmark
and if must show information about benchmark and all tracer lunches.

```php
ob_end_flush();
benchmark_class::stop();

echo benchmark_class::display();
echo tracer_class::display();
tracer_class::saveToFile();
```

![tracer1](/image/tracer1.png "tracer 1")
On that image we see some tracer call that load all framework libraries.

And that all for index file and run core libraries. Now we will describe how its
look inside core libraries using for that tracer log table.

Lunch core framework libraries
--------------

### core_class::__construct()
Starts framework working

### option_class::load()
Load all framework options from `config.xml`

![tracer2](/image/tracer2.png "tracer 2")

### Checking for technical break and setting timezone

```php
$this->_checkTechBreak();
$this->_setTimezone();
```

### core_class::_runBaseObjects()
Next step framework lunch some basic objects that handle all requests for page and
other common objects _(get, post, lang_class, error_class)_. `lang_class` check
also given language and make redirect to correct if some problem was detected.

![tracer3](/image/tracer3.png "tracer 3")

### Check page type
After that framework check witch page type we have in request _(html, css or js)_
and run special methods depends of chosen page type.

```php
if ($this->_get->pageType() === 'html') {
    benchmark_class::startGroup('html group');
    $this->_htmlPage();
    benchmark_class::endGroup('html group');
} else {
    $this->_otherPage();
}
```

![tracer4](/image/tracer4.png "tracer 4")

**For our documentation we will describe next steps based on `html` page type**

### Start important objects
Now framework lunch some objects that are important only for `html` page like
starting `session, cookie, files` classes to handle some incoming data and clear
global arrays so to get access for incoming data in modules must be used some
special methods.

![tracer5](/image/tracer5.png "tracer 5")

### Page structure
Next framework start `tree_class` object to get whole page structure. In that step
framework check if page given in URL exist in structure. If not redirecting for
main page or 404 page _(depends of framework option)_ and if exist check page
options and set default template to load.

```php
$this->_tree = new tree_class(
    $this->_get->fullGetList(),
    $this->_lang->lang
);
benchmark_class::setMarker('runs tree object');
```

![tracer6](/image/tracer6.png "tracer 6")

### Create page content
When framework know witch page must be loaded and witch main template will be loaded
can run `display_class` to handle and store module content rendering.

```php
$this->_display = new display_class(array(
    'template'  => $this->_tree->layout,
    'get'       => $this->_get,
    'session'   => $this->_session,
    'language'  => $this->_lang->lang,
    'css'       => $this->_tree->css,
    'js'        => $this->_tree->js,
));
benchmark_class::setMarker('runs display object');
```

![tracer7](/image/tracer7.png "tracer 7")

`tree_class` to get framework page structure, next lunching `display_class` to
create base array for content for all modules, `meta_class` to handle meta tags.  
another step inside that method is start `loader_class` and loading libraries
and modules and working that library will be described in other steps.

### Create object to handle meta tags

```php
$this->_meta = new meta_class($this->_get->fullGetList());
benchmark_class::setMarker('runs meta object');
```

![tracer8](/image/tracer8.png "tracer 8")

### Loading libraries and modules
That step in `_htmlPage()` method is very big step, because it will load all
chosen libraries for selected page and also load and starts all modules. In first
step inside `loader_class::__construct()` we have detecting type of browser, next
is loading libraries and las is loading and lunching modules.

```php
$this->_detectMobileBrowser();
$this->_load('lib');
$this->_load('mod');
```

### Loading libraries
Now framework will load library files that was decelerated in `tree.xml` file and
store all of loaded libraries in array as class names.

```php
$libs = starter_class::package($name);
if (!$libs) {
    throw new coreException('core_error_20', $name);
}
$this->lib = array_merge($this->lib, $libs);
```

![tracer9](/image/tracer9.png "tracer 9")

### Loading and lunching libraries
Now is the most important moment, loading and lunching libraries. Each library is
started immediately after load. Also before loading module framework check that
some other module or framework don't stop lunching modules, or som of them are
skipped. After that framework check and set block form module, load it, validate
_(if required)_ and finally lunch module.

```php
if ($this->stop) {
    break;
}
if (in_array($name, $this->_tree->$type)) {
    continue;
}
if ($val['exec']) {
    $exe = $val['exec'];
} else {
    $exe = $name;
}
$this->_setBlock($val['block'], $name);
$path = 'modules/' . $name . '/' . $exe . '.php';
$bool = starter_class::load($path);
if (!$bool) {
    throw new coreException('core_error_20', $name . ' - ' . $exe);
}
$this->_validate($path, $exe, $name);
$this->_run($exe, $name, $val['param']);
```

![tracer10](/image/tracer10.png "tracer 10")

### Create meta tags
Now we going to finish framework work and show up that work results _(rendered
content or some errors)_. First step to finish work is creating all meta tags
elements. Using `$this->_meta->render($this->_display);` framework will render
complete to insert in page meta tags and title.

### Checking errors
Next framework check that some errors or messages was occurred during framework
module lunching. If some errors or messages was detected replace special markers
with that messages content.

## Return to part with common for all pages type methods

### Language support
Now we return to `core_class::__construct()` method to start other common methods.
First of them is `core_class::_setLanguage();` that starts framework translations.
Firstly framework collect all required translations files into one common array
and in next step search in templates for all translations markers to replace them
by founded translations. If framework wont found translation for marker will
leave translation key with brackets `{code_not_found}`.

```php
protected function _setLanguage()
{
    tracer_class::marker(array(
        'setting language',
        debug_backtrace(),
        '#006C94'
    ));

    $this->_lang->setTranslationArray();
    benchmark_class::setMarker('set translation array');

    $this->_lang->translate($this->_display);
    benchmark_class::setMarker('start translation');
}
```

![tracer11](/image/tracer11.png "tracer 11")

### Rendering whole page
When templates are ready and translated framework can merge them into one full
template to be displayed, that process cal _rendering_. In that step framework
lunch special method from `display_class` and content that method will return is
whole ready to display page content.

```php
$this->render = $this->_display->render();
benchmark_class::setMarker('rendering');
```

All module templates and main template are stored in special array inside of
`display_class` and rendering process firstly merge that templates into one, by
replacing special markers _(mod and block)_ by content from modules. When all
templates are merged replace other un replaced markers using for that special
methods:

```php
$this->_link('css');
$this->_link('js');
$this->_session();

$this->DISPLAY = $this->DISPLAY['core'];

$this->_path();
$this->_clean();
$this->_compress();

if (!(bool)$this->_options['debug']) {
    ob_clean();
}
return $this->DISPLAY;
```

First set styles links, next java script links and replace markers by data stored
in session. After that replace array with templates by full merged content and
replace path markers by URL paths _(depends of configuration)_. In another step
remove all un replaced markers, so we don't see any marker on page in browser and
depends of configuration compress content. Last thing to do is remove debugger
information, depends of configuration option and finally return complete content
to be displayed in the browser (or some data for script for ajax request). That
content is saved into `$this->render` variable that `starter_class` can get
content to display.

![tracer12](/image/tracer12.png "tracer 12")

### Transform global arrays
Because framework has special classes to handle global arrays _(GET, POST, SESSION
etc.)_ at the ending of work clear all global arrays `core_class_transformGlobalArrays()_`
in cas if someone try to set some data by using in example `$_SESSION['key'] = 'some value';`.  
After clearing global arrays framework again set all data into `$_SESSION and $_COOKIE`
arrays with data stored in framework `session and cookie` objects that PHP can handle dem.

```php
if ($this->_get->pageType() === 'html') {
    globals_class::destroy();
    benchmark_class::setMarker('destroy global arrays');

    $this->_session->setSession();
    benchmark_class::setMarker('set data in session');

    $this->_cookie->setCookies();
    benchmark_class::setMarker('set data in cookie');
}
```

![tracer13](/image/tracer13.png "tracer 13")

And that part, code finish working of `core_class` and returns to `starter_class`.

### Show rendered content
When `core_class` stop working and if there was no error, last thing to do is
display full rendered content to show it in browser.

```php
if ($core->render !== NULL && !empty($core->render) && $core->render){
    echo $core->render;
    unset($core->render);
} else {
    throw new coreException ('core_error_1');
}
```

All next step is described at the beginning of document and has no really big
matter for finishing framework work.  
And that's all **Framework finished his job :)**

Tracer colors
--------------

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
