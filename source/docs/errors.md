Framework errors
====================
Errors that can happen when we use framework can be split to three group. First
are script errors called by interpreter. Most of that errors will be catch by
framework by usage of output buffering and check that some error was occur.  
Framework will display that errors in more accessible form.  
All other errors are errors of framework or some of modules. most of framework
errors are handled by `coreException` class and will automatically stop framework
with some messages.  
Third group are errors called by module and stored in special array to display
them on page rendering.

Error handling
--------------
### Core exceptions
Core exceptions are called by using `throw new coreException ('error_code');` and
have own translation arrays localized in the same directory as core translations
but with special name `/BLUE_FRAMEWORK/cfg/lang/core_error_pl-PL.php`. Code of
error `error_code` is the key of translation array.  
To `coreException` we can also give some other information, using second parameter.
Using that parameter we can give some additional message about error.

```php
throw new coreException ('error_code', 'some error message');
```

**All that errors will stop framework, even throw inside module will stop all
other modules.**

**But used inside module, will show all content rendered by previous modules.**

### Module exceptions
That kind of exception `moduleException` is very important fo modules, because
its only exception that can run module in special mode using `runErrorMode()` method.  
It has the same attributes as `coreException`.

```php
throw new moduleException ('error_code', 'some error message');
```

### Other module exceptions
There is some other exceptions that stops module and collect some data, but they don't
stop other modules and framework working and cannot start module at error mode.
All of them has the same construction as previous exceptions. That exceptions
can be useful to show some information, or to stop module with some problems.  
All of them are described in module documentation [module documentation](/docs/module.md)

1. **warningException**
2. **infoException**
3. **okException**
4. **Exception**

List of all core exceptions
--------------

### core_error_0
**Error on Framework libraries loading**
That error is called only if there was some problems on loading _core_ libraries
in `starter_class::run()`

```php
$bool = starter_class::package('CORE');
benchmark_class::setMarker('load core package');
if (!$bool) {
    throw new coreException('core_error_0');
}
```

### core_error_1
**No content to display**
That error means that was no content to show after rendering core template and has
only one implementation in `starter_class::run()`

```php
if ($core->render !== NULL && !empty($core->render) && $core->render){
    echo $core->render;
    unset($core->render);
} else {
    throw new coreException ('core_error_1');
}
```

### core_error_2
**No layout to load**
That error will be called all times when template file that framework want to
load is empty, or there was some problem on template loading. Also error message
will have information about module ant template path. That error is called in
`display_class::layout()`

```php
$this->DISPLAY[$module] = starter_class::load($path, TRUE);
if (!$this->DISPLAY[$module]) {
    throw new coreException('core_error_2', $module . ' - ' . $path);
}
```

### core_error_3
**Error on loading external layout**
When display class detect in template marker for external template `{;external;([\w-])+;}`
will try to load an external template for main template. If method `starter_class::load()`
return FALSE on empty string, that error will be called. Error message will also
have path to external template and its called in `display_class::_external()`.

```php
$content   = starter_class::load($finalPath, TRUE);
if (!$content) {
    throw new coreException('core_error_3', $finalPath . '.html');
}
```

### core_error_4
**URL has unavailable characters**
If framework will detect wrong characters in URL, that error will be called. Allowed
characters depends of using mode rewrite by framework and special regular expressions
used to check characters _(different fo mode rewrite and classical URL)_. That
regular expressions are defined in `config.xml` in `reg_exp_rewrite` and
`reg_exp_classic`.Error message will also have URL path and return witch option
is selected in configuration _(mode rewrite - 1; classic url - 0)_. To find that
error code go to `globals_class::_checkParameter()`

```php
if ((bool) core_class::options('rewrite')) {
    $bool = preg_match(core_class::options('reg_exp_rewrite'), $uri);
} else {
    $bool = preg_match(core_class::options('reg_exp_classic'), $uri);
}
if (!$bool) {
    throw new coreException(
        'core_error_4',
        $uri . ' - rewrite: ' . core_class::options('rewrite')
    );
}
```

### core_error_5
**URL has to many parameters**
In framework options we can set how many parameters framework will accept. That
error will appear if we try to give by URL more variables than framework will
accept. Of course we can turn off that limit by setting `max_get` value to `0`.
Error information will also have number of max allowed parameters and is called
in `globals_class::_maxParameters()`.

```php
$option = core_class::options($option);
if ((bool)$option) {
    if ($counter > $option) {
        $inf = count($globalArray) . ' -> ' . $option;
        throw new coreException('core_error_5', $inf);
    }
}
```

### core_error_6
**One of URL parameters are to long**
If value of some parameter given in URL is to long, than limit set in configuration
by `get_len` option, then that error will be called.I we set that option to `0`
then framework will not check value limit. Error information will also have
parameter with error. That error is called in `globals_class::_maxLength()`.

```php
if (core_class::options('get_len')) {
    $length = mb_strlen($parameter);
    if ($length > core_class::options('get_len')) {
        throw new coreException('core_error_6', $parameter);
    }
}
```

### core_error_7
**To many POST parameters**
That error is similar to `core_error_5` but concerns data given in `POST`. If to
framework will be given to many POST variables, then framework will throw that
exception. Also we can turn of checking number of given parameters by setting
`max_post` to `0`. Error information will also have number of max allowed
parameters and is called in `globals_class::_maxParameters()`.

```php
$option = core_class::options($option);
if ((bool)$option) {
    if ($counter > $option) {
        $inf = count($globalArray) . ' -> ' . $option;
        throw new coreException('core_error_7', $inf);
    }
}
```

### core_error_8
**To many files send**
Works the same as `core_error_7` and `core_error_5`. Also we can turn of checking
number of given parameters by setting `files_max` to `0`. Error information will
also have number of max allowed files and is called in `globals_class::_maxParameters()`.

```php
$option = core_class::options($option);
if ((bool)$option) {
    if ($counter > $option) {
        $inf = count($globalArray) . ' -> ' . $option;
        throw new coreException('core_error_8', $inf);
    }
}
```

### core_error_9
**Error on mata description file loading**
That error will be called when `meta_class` want to load `meta.xml` file using
`xml_class::loadXmlFile()`. If that method will return `FALSE` then `core_error_9`
exception will be throw by framework, with path to `meta.xml` file. But only if
framework has turned on `meta` option to use `meta_class`.

```php
$bool   = $xml->loadXmlFile(
    starter_class::path('cfg') . 'meta.xml',
    TRUE
);
if (!$bool) {
    throw new coreException(
        'core_error_9',
        starter_class::path('cfg') . 'meta.xml'
    );
}
```

### core_error_10
**Send file ae to big**
Another framework input data restriction is max file size that we want to send.
That error is called in `files::run()` method and also return name of file that
was send to framework. Max single file size is defined by `file_max_size` option
in `config.xml`.

```php
if ($file['size'] > core_class::options('file_max_size')) {
    throw new coreException('core_error_10', $file['name']);
}
```

### core_error_11
**All send files are to big**
That error is connected with previous `core_error_10` exception, also both of them
are called inside the same method `files::run()`. That error is called when sum
of sie all send files are bigger than limit set by `files_max_size` in configuration.

```php
$this->_uploadFullSize += $file['size'];
$maxSize                =  core_class::options('files_max_size');
if ($this->_uploadFullSize > $maxSize) {
    throw new coreException('core_error_11', 'max: ' . $maxSize);
}
```

### core_error_12
**Error when moving send file to directory**
That error is called when we want to get content of uploaded file. To get content
of uploaded file framework must put it to some temporary directory and when moving
operation will fail, then framework throw that error inside of `files::_single()`
method. That error can be called also when framework want to move uploaded file
destination directory inside of `files::_put()` method. Error will also has
information about file and destination.

```php
$name = starter_class::path('TMP') . 'tmp';
$bool = move_uploaded_file($this->$file, $name);
if (!$bool) {
    throw new coreException(
        'core_error_12',
        $this->$file . ' => ' . $name
    );
}
```

```php
$bool = move_uploaded_file($filename, $destination);
if (!$bool) {
    throw new coreException(
        'core_error_12', $filename . ' => ' . $destination
    );
}
```

### core_error_13
**Unable to load page structure**
When framework want to read page structure and there was some error when reading
file or some problems with page structure, then framework will throw that exception.
Exception is called by two methods, basic is `tree_class::load()` used to handle
all pages and second is `tree_class::map()` called to return page structure map.
Both of them also return information about error from `xml_class`.

```php
$bool = $this->_treeStructure->loadXmlFile(
    starter_class::path('cfg') . $xml . '.xml',
    TRUE
);
if (!$bool) {
    throw new coreException(
        'core_error_13 ',
        $this->_treeStructure->err . ' ' . $xml
    );
}
```

### core_error_14
**No main page defined**
For each page we want to display framework check that page exists in structure
and depends of configuration `error404` will redirect to _404_ page or _main page_.
But if `error404` redirecting is turned off and _main page_ dos not exist, then
framework will throw `core_error_14` exception in `tree_class::_chk404()`.

```php
if (!$this->_mainPage && (bool)core_class::options('error404')) {

    //some code

    header("Location: /$path");
    exit;
} elseif (!$this->_mainPage && !(bool)core_class::options('error404')) {
    $this->_mainPage = $this->_treeStructure->getId('index');
}
if (!$this->_mainPage) {
    throw new coreException('core_error_14');
}
```

### core_error_15
**Page is disabled**
When client try to access to page/subpage in structure, that was disabled _(first
option in `options` string set to `0`)_ then framework will throw exception with
that code. That error is called inside of `tree_class::_on()` method.

```php
private function _on()
{
    $options = $this->_mainPage->getAttribute('options');
    if (!(bool)$options{0}) {
        throw new coreException('core_error_15');
    }
}
```

### core_error_16
**No skeleton defined**
That error is called when `tree_class` cant find main layout to load. Its called
in `tree_class::__construct()` and also contains value of `$this->_layout` variable.  
Main layouts are localized in `BLUE_FRAMEWORK/elements/layouts` and their give
also structure for modules and all other elements to build page.

```php
if (!$this->layout) {
    throw new coreException('core_error_16', $this->layout);
}
```

### core_error_17
**Given language was disabled**
In framework configuration file, when language handling is enabled `lang_support = 1`
we define witch languages are supported by framework. We must define for that
default language `lang` that will be lunched when no language was given in URL and
list of supported languages in `lang_on` configuration section. If client will
use in URL language code that is not exist in `lang_on` list, then framework
will throw `core_error_17` exception in `lang_class::__construct()` with language
code in error information.

```php
if ($languageCode && !in_array($languageCode, $this->_options['lang_on'])) {
    throw new coreException('core_error_17', $languageCode);
}
```

### core_error_18
**No main page for meta description**
When framework is building meta description for all pages, use for all of them
base meta descriptions that are defined for main page `index`. If `meta_class::__construct()`
cant find that base page meta elements, then framework will call `core_error_18`
exception.

```php
$index = $xml->getId('index');
if (!$index) {
    throw new coreException('core_error_18');
}
```

### core_error_19
**No language to load**
That error will be called when framework try to load language array file. The
problem can be in not existing language file, or language file are empty. Framework
firstly try toi load file with the same language code as given in URL, but if
there will be some error try to load similar language array _(eg. en-US is given
language code but file don't exist, so framework will try to load en-GB language
file)_. If framework cant load any language file, the throw that error in
`lang_class::setTranslationArray()` method.

```php
if ($languageCode) {
    $lang = $this->loadLanguage($path . $mod, $languageCode);
} else {
    $lang = $this->loadLanguage($path . $mod);
}
if (!$lang) {
    if ($type) {
        return FALSE;
    } else {
        if ($languageCode) {
            throw new coreException(
                'core_error_19',
                $path . $mod . '_' . $languageCode
            );
        }
        throw new coreException(
            'core_error_19',
            $path . $mod . '_' . $this->lang
        );
    }
}
```

### core_error_20
**No required module/library**
That error is called when framework has some problems with loading module or library.
Basic reason is that module or library dos not exist in structure, but is set on
in `tree.xml`.  
That error can be called in couple of places in framework.

Fist place is inside of `loader_class::_load()` method and can be called for module
or library.

```php
if ($type === 'lib') {
$libs = starter_class::package($name);
if (!$libs) {
    throw new coreException('core_error_20', $name);
}
$this->lib = array_merge($this->lib, $libs);
} elseif ($type === 'mod') {

    //some code

    $path = 'modules/' . $name . '/' . $exe . '.php';
    $bool = starter_class::load($path);

    if (!$bool) {
      throw new coreException('core_error_20', $name . ' - ' . $exe);
    }

    //some code

}
```

Second call of that exception depends on configuration sets `core_procedural_mod_check`.
If that option is set to `1` then framework will check that module is class. But
that error will occur only if framework cant read file content.

```php
$content = starter_class::load($path, TRUE);
if (!$content) {
    throw new coreException('core_error_20', $info);
}
```

Las one call exist inside of `module_class::_checkRequiredLibraries()` when module
has defined array of required libraries, and some of them don't exist on loaded
libraries list. Error will also have some information about missing library.

```php
if (!empty($this->requireLibraries) && !(bool)$this->_unThrow) {
    foreach ($this->requireLibraries as $lib) {
        $bool = in_array($lib, $this->core->lib);
        if (!$bool) {
            throw new coreException(
                'core_error_20',
                $this->moduleName . ' - ' . $lib
            );
        }
    }
}

if (!empty($this->requireModules) && !(bool)$this->_unThrow) {
    foreach ($this->requireModules as $mod) {
        $bool = array_key_exists($mod, $this->modules);
        if (!$bool) {
            throw new coreException(
                'core_error_20',
                '{;lang;module;} ' . $this->moduleName 
                 . ' {;lang;require;} ' . $mod
            );
        }
    }
}
```

### core_error_21
**Modules and libraries must be object**
In previous error description was information about checking that module is class.
That error is called in `module_class::_checkRequiredLibraries()` method after
loading content of module file _(if there was no core_error_20 exception)_. If
that error will occur, that means the module file is not a class but procedural.

```php
$bool = preg_match($this->_class, $content);
if(!$bool){
    throw new coreException('core_error_21', $info);
}
```

### core_error_22
**No proper object to load**
That is another error connected with the module loading and starting. Before
framework will try tu lunch module class, first check that class exists inside of
``loader_class::_run()` method. If module file don't have required class then
framework will throw `core_error_22` exception with information about required
module and class.

```php
$bool = class_exists($execute);
if (!$bool) {
    throw new coreException('core_error_22', $execute . ' - ' . $module);
}
```

### core_error_23
**Page will be displayed soon**
For pages and subpages can be set up date from witch page will be available by
setting up `startDate` option in `tree.xml`. If framework detect that day and
current day will be earlier, then `core_error_23` will be throw with date from
witch page will be available. Framework check dates for pages inside of
`tree_class::_checkDate()` method. Dates for pages cant be set for main
page, because main page is not checked.

```php
if (!$node) {
    $node = $this->_mainPage;
}

$time = $node->getAttribute('startDate');
if ($time && $time > time()) {
    if (!$node) {
        $date = strftime('%c', $time);
        throw new coreException('core_error_23', $date);
    }
    return FALSE;
}
```

### core_error_24
**Page loose their valid**
That error is very similar with previous, but fot that framework check the expire
date for page or subpage. If page is not available, because date set up by
`endDate` option in `tree.xml` is lower than current date.

```php
if (!$node) {
    $node = $this->_mainPage;
}

$time = $node->getAttribute('endDate');
if ($time && $time < time()) {
    if (!$node) {
        $date = strftime('%c', $time);
        throw new coreException('core_error_24', $date);
    }
    return FALSE;
}
```

### core_error_25
**Wrong key name for variable**
Because framework store all input data _(like GET, POST)_ as class variables, where
key from global array is name of variable in class, framework must check that
name of key can be converted to PHP class variable name. That exception is throw
inside of `globals_class::_checkKey()` but that method is called each time when
framework try to set up some variable in methods that extends `globals_class`.
Exception will also give key value and allowed chars regular expression that is
defined in configuration be `global_var_check` option.

```php
protected function _checkKey($key)
{
    $keyCheck = preg_match(core_class::options('global_var_check'), $key);
    if (!$keyCheck) {
        throw new coreException(
            'core_error_25',
            $key . ' - rewrite: ' . core_class::options('global_var_check')
        );
    }
}
```

Methods that use checking of variable names:

1. **globals_class::__get()**
2. **globals_class::__set()**
3. **globals_class::_add()**
4. **post::run()**
5. **cookie::run()**
6. **session::run()**
7. **session::set()**
8. **session::clear()**
9. **files::run()**
10. **files::move()**
11. **files::_single()**
12. **files::_put()**

Other core errors
--------------
Exceptions handle almost all framework errors that can be happen on framework
working, but there are also some critical errors, that cause framework cant handle
exceptions or any other errors.  
That errors will be displayed as simple string and immediately stop framework by
use `exit;` instruction.

### Main configuration error
That error is called if framework was unable to read main configuration file
`config.xml` or there was some syntax or DTD problem. Message will also contains
what error was occur.

```php
$bool = $xml->loadXmlFile(
    starter_class::path('cfg') . 'config.xml',
    TRUE
);
if (!$bool) {
    $path = starter_class::path('cfg');
    echo 'Main configuration load error<br/>' . $path . 'config.xml<br/>';
    echo $xml->err;
    exit;
}
```

### Technical break
Framework has special option in `config.xml` to temporary stop working whole framework.
By setting `techbreak` option to `1` we stop framework on `core_class::__construct()`.
There is special method that check that option `core_class::_checkTechBreak()` and
try to get special template for **technical break**. If that template dos not
exists, framework will display simple message _Technical BREAK_.

```php
if (self::$_options['techbreak']) {
    $break = starter_class::load('elements/layouts/techbreak.html', TRUE);
    if (!$break) {
        echo 'Technical BREAK';
    } else {
        echo $break;
    }
    exit;
}
```

### No default error pack
Another special error is error with missing default language package and that is
specific error, because its can be called only if there was some other error in
framework. Inside of `error_class::addError()` is called method `error_class::_statement()`
and if that method cant read language pack to handle some other error _(probably
module exception or information)_ that special error will be called.

```php
$bool = $this->_pack("cfg/lang/core_error");
if ($bool) {

//some code

} else {
    @trigger_error(
        'No default error pack<br/>' . $mod . '<br/>' . $errorCode
    );
}
```

### missing error_class :(
That is last of specific errors, lunched only on start framework if `error_class`
file don't exist. So probably we wont never see this error, of course if we copy
our framework files properly.

```php
$bool = starter_class::load('packages/CORE/error_class.php');
if (!$bool) {
    die ('missing error_class :(');
}
```