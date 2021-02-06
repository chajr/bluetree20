Create and usage module
====================
Elementary part of framework are modules. And rom modules truly depends what we can
do with framework, what work will be done, what content will be displayed.

Directory structure and namespace convention
--------------
All modules are localized in `/BLUE_FRAMEWORK/modules/` directory, where we define
our module directory name eg. `module` (/BLUE_FRAMEWORK/modules/module).  
Inside, for basic usage just create php file with the same name as directory `module.php`.
Also we can have some addition directories, that will contain some other files
and all of them will be loaded automatically at module start.  
We can create some different directories and files as we want and all of them
will be loaded automatically.

```
module                 - main module directory
   |--elements         - some other elements (in this folder can be also `config.xml` for module)
   |    |--css         - styles directory
   |    |--js          - script directory
   |    |--dtd         - optionally DTD for module configuration
   |--lang             - language files directory
   |--layouts          - template directory
   |--module.php       - main module script
```

Base module
--------------
So, to create basic module, just create module directory like `module` and inside
`module.php`. Inside of `module.php` we create class with the same name as directory
and module file `class module`. Then create `__construct` method to start module.
Module reference will be saved in `loader_class->mod` variable that framework can
interact between modules.  
Constructor gets four parameters:

1. **loader_class** instance, we can get access to all libraries like display
2. **parameters** - some additional parameters for module written in `tree.xml`
3. **module name**
4. **unthrow** - from configuration, information to omit all catches exceptions

Inside of constructor we can do what we want to and what is allowed by framework
using `loader_class` methods.

```php
<?php
class module
{
    public function __construct($loaderClass, $parameters, $module)
    {
        $loaderClass->core->generate('test', $module, 'Hello world :)');
    }
}
```

That modulw is usage `loader_class` to get access to `display_class` and replace
`test` marker by given content.

That is the simplest way to create module, but not for usage all features. To create
module in framework way and usage framework features much simpler we must extend
`module_class`.

Extending module class
--------------
To get better access to framework features and create module in framework way we
must extend special core class `module_class`. That method has mapped some important
methods from core classes that we will use and implements some other features like
module error mode, or checking required libraries. `module_class` is an abstract
class so we must implement some of required methods `run and runErrorMode`.  
Instead of `__construct` method we use `run` without parameters.

The same module with the same functionality by using `module_class`:

```php
<?php
class module
    extends module_class
{
    public function run()
    {
        $this->generate('test', 'Hello world :)');
    }

    public function runErrorMode(){}
}
```

**runErrorMode** method is lunched only if `modException` was throw by module and
unthrow configuration option is set to `FALSE`.

### Method list

1. **generate** - replace content or content array _(marker or marker array, content or false if marker is array, set tru if you want to replace content in core templates)_
2. **loop** - create content like table by data in array _(marker, array of content to replaced by loop)_
3. **addMetaTag** - add complete meta tag node _(complete meta tag node)_
4. **addToMetaTag** - add some content to existing meta tag _(meta tag type, meta tag content)_
5. **lang** - return default or loaded language code  _(TRUE - default language; FALSE - loaded language; default variable is NULL)_
6. **setSession** - set information in session, default in public section _(variable name, data, storage type;user|public|display|core; default is public)_
7. **clearSession** - remove all data for given group _(user|public|display|core)_
8. **getSessionVariable** - return value of given session variable _(variable, type; user|public|display|core)_
9. **layout** - load module template _(optionally template name; if not given will load template as the same name as module `module.html`)_
10. **set** - add load css or js for module _(file name, type css or js, external or internal file, media type)_
11. **breadcrumbs** - return array with breadcrumbs list with url to pages
12. **map** - return site map with urls _(name of file to create map; default is tree.xml, if TRUE return list with pages options)_
13. **siteMap** - return site map in xml google format
14. **error** - set some messages from module _(message type critic|warning|info|ok or marker to write message, error code, message to show)_
15. **loadModuleOptions** - get one option or all options for module, stored in `elements` directory _(option name)_
16. **_translate** - give information to framework that module will have translations
17. **_setTranslationArray** - add or replace some base translations _(array of translations)_
18. **_disabled** - skip lunching given module _(module name)_
19. **_stop** - stops lunching all other modules
19. **getModuleDir** - return full module directory path

*if module wants to lunch method that dos not exists, then `__call` method will be lunched.*  
*that method will return `NULL` and add message to tracer _call to undefined method_*

### Variable list

1. **$version** - (static) module version number
2. **$name** - (static) full module name _(like this is my module)_
3. **$requireLibraries** - (static) array with names of libraries required to module work
4. **$requireModules** - (static) array with names of modules required to module work
5. **core** - contains `loader_class` reference 
6. **params** - list of parameters witch module starts (from tree.xml)
7. **block** - name of block to witch module rendered content will be loaded
8. **moduleName** - module name
9. **get** - contains `get` object
10. **post** - contains `post` object
11. **session** - contains `session` object
12. **cookie** - contains `cookie` object
13. **files** - contains `files` object
14. **modules** - list of loaded and lunched modules
15. **error** - array with list of error/information count
16. **mobileBrowser** - if TRUE means that framework was lunched by mobile browser

### Static methods list
Access to some other core libraries useful methods:

1. **starter_class::path()** - return framework main path (to use files included in BLUE_FRAMEWORK)
2. **starter_class::load()** - load file or file content (can read some file variable) _(path to file inside of BLUE_Framework, read or not file content, loading type)_
3. **core_class::options()** - return all or single framework options _(option name)_
4. **error_class::log()** - create log file _(file prefix, data to save, log time, path to save log file)_
5. **error_class::other()** - return some information like _ip, browser, url and date_ as array
6. **files::exists()** - check that file exists, give full path to file
7. **globals_class::destroy()** - destroy all data stored in global arrays (like `$_GET`, `$_POST` etc.)
8. **get::realPath()** - return repair path for elements
9. **option_class::load()** - load options for given module and save it in array, as second parameter we can force reload all options
10. **option_class::show()** - return value of single option

Incoming data
--------------
Almost all global arrays are rewrite to special objects, data that they have is removed
and to access that data we must use that special objects. All that special objects
use `globals_class` so all data that their contains we get by object variable.  
Name of variable is the same name as global array key `$_GET['a'] => get->a`, so in
POST or FILES global data, each variable key is the name of input.

### GET
#### get data
To use data from **GET** we must use `$this->get->variableKey`. That will return
data for given value or NULL if variable don't exists.

```php
public function run()
{
    $variable = $this->get->variable;
    $this->generate('variable', $variable);
}
```

#### get methods
By `$this->get` we can also get access to some useful get class methods.

1. **getLanguage** - return language code or `NULL` if language library is off
2. **getCurrentPage** - return path value of current page
3. **getParentPage** - return parent of current page, or page given in parameter
4. **getMasterPage** - return master page, defined in `<page>` node
5. **fullGetList** - return full array with pages/subpages and their GET parameters
6. **pageType** - return page type (html|css|js)
7. **path** - return main path for page, or complete path with subpages

### POST
Access to post data we get using the same way as get data `$this->post->variableKey`.

### COOKIE
Access to cookie data we get using the same way as get data `$this->cookie->variableKey`.  
That class has only one method:
**setCookies** - set cookie file that exist on object, with default lifetime value

### SESSION
Access to session variable is the same way that previous examples, but session has
specific data storage, split to some other arrays. Data in session is keep in that
arrays: `public, core, user, display`.

1. **public** - default array to store data, access to this data as the same as post and get
2. **core** - store some core information
3. **user** - store user information
4. **display** - store data that will be used to replace some special markers when page will be rendered

#### session methods

1. **set** - set some variable in session object _(var name, var value, group name public|core|session|display)_
2. **returns** - return all data for given group _(group name public|core|session|display)_
3. **clear** - clear data in session in given group _(group name public|core|session|display)_

### FILES
This class store information about all send to framework files and also can make
some operations on that files, like move to specific directory.  
This object store in one key all uploaded file information.

```php
array(
    'name'      => uploaded file name,
    'type'      => uploaded file type,
    'tmp_name'  => name of file uploaded to server tmp directory,
    'error'     => upload error,
    'size'      => size of uploaded file,
    'extension' => uploaded file extension,
    'basename'  => uploaded file base name
);
```

If some upload error was occurred, then will be also stored in one public variable
`uploadErrors` that will contain array of errors with key _(uploadErrors['input'])_.

#### files methods

1. **move** - move uploaded file to given directory _(destination or array of destination or array of files and their destination, name of file to save)_
2. **read** - get data from file, or fromm all files in object _(key name - the same as input name)_
3. **uploadFullSize** - size of all uploaded files in bytes
4. **returns** - return array with some file values files name, their types, or errors _(value type name|type|tmp_name etc.)_
5. **exist** - check that file exists _(path)_
6. **single** - return content of single file _(key name - the same as input name)_

If directory for file don't exist, will be automatically created  
If file name will have restricted symbols in name, then `core_error_25` will be throwed.

Module templates
--------------
Each module if want to display some data must have to use framework templates system.
It based on the special markers system, that will be replaced using some special
methods for that. Framework allows to three level template access, first is `core`
templates and allow to display content in templates defined in `tree.xml`. Second
way and basic for module is to use module template. Last wey is to create instance
of `display_class` inside of module and give rendered content to some `generate` method.

### Module template
All templates for module are localized in special module directory `modules/module/layouts`
in `*.html` file. To use that template by framework use method `$this->layout('template');`
when as parameter we give name of template.  
After that we can get access to render some content by special methods by writing
`$this->generate('marker', 'Hello world :)')`.  
All methods are described in template.md documentation.

### Main template
To get access to core template we use the same way as to module template, but
with another parameter as last method parameter.  
`$this->generate('marker', 'Hello world :)', 'core')` - that construction allow
to replace content in core template.

### Additional templates
Another way to usage template system is to create new object from `display_class` class.
That way directly show any content but can be very useful to create complicated
module template.  
By that way we create many objects and give them some templates, replace content
and at the end join all of them and replace some marker in module template.

```php
public function run()
{
    $this->layout('module_template');

    $firstTemplate = new display_class(
        'independent' => TRUE,
        'template'    => 'template/address/template.html'
    );
    $secondTemplate = new display_class(
        'independent' => TRUE,
        'template'    => 'template/address/template2.html'
    );

    $firstTemplate->generate('marker', 'some content');
    $firstTemplate->generate('another_marker', 'some another content');

    $secondTemplate->generate('mark', 'second template content');

    $this->generate('first_template', $firstTemplate->render());
    $this->generate('second_template', $secondTemplate->render());
}
```

### All configuration for display_class
1. **template** - path to template, required for core and independent usage
2. **independent** - if used by core give FALSE, if want to be used independent set on TRUE
3. **get** - get class instance, required for core
4. **session** - session class instance, required for core
5. **language** - language class instance, required for core
6. **css** - list of loaded css files, required for core
7. **js** - list of loaded js files, required for core
8. **options** - framework options, required for core
9. **clean** - turn off cleaning template from unused markers if set to FALSE, default TRUE

### Session data display
Last way to render some content in template is use to this `SESSION`. We can put
some data into session like that:

```php
$this->setSession(
    'marker_name',
    'some data to render on page',
    'display'
);
```

All markers `{;session_display;marker_name;}` will be replaced by content from
that declaration.

Module additional files
--------------
All modules create some group of files that are required to proper module work.
That can be some other php files, that give some useful classes, or some other
files like java scripts or styles.

### Other scripts
Because loader_class load only base module file, other files we must load manually.
To do this use `starter_class::load('path');` method.

```php
$additionalScript = $this->getModuleDir() . 'file.php';
starter_class::load($additionalScript);
```

### JS and CSS files
Sometimes module will require some specific CSS styles or java scripts. To load them
we use special method `$this->set()` with specific parameter to inform witch file
must be loaded (js|css).  
All files are localed in specific directories:  
java scripts -> `module/elements/css`  
css -> `module/elements/css`

### Module configuration
Last example of specified module additional file is configuration file. That file
is localized in `module/elements/` directory nad named: `config.xml`. That file
has xml structure, and to use it is recommended to use DTD of main configuration.
To load module configuration and get some options from them use `$this->loadModuleOptions()`
without parameter to load all configuration or give configuration key name to get
single configuration value.

Internationalization
--------------
To use translations, first we must switch them on in main configuration  by setting
`lang_support` option to `1` and enabling language we wont to use.

```xml
<langs id="lang_on" value="1">
    <lang id="pl-PL" on="1"/>
    <lang id="en-GB" on="1"/>
    <lang id="en-US" on="1"/>
</langs>
```

That will enable translations but only for core templates. To use translations in
module templates we must inform framework by using special method `$this->_translate()`.  
Nex thing we must create translation files for module and each language that we
have switched on in framework `module/lang/module_en-GB.php`. All translations are
stored as an array of `key => value` where key is the value of `{;lang;}` marker.
That array is stored in `$content` variable.

```php
$content = array(
    'translation_key'       => 'some text to translate',
    'translation_key_2'     => 'some other text to translate',
}
```

In module template just use marker with that construction: `{;lang;translation_key;}`.

Some specific thing about translations is possibility to use translations from
core translations and display some content in specific language that is different
than language given in URL.  
Usage of core translations: `{;lang;core;string_to_translate;}`
Usage of translations form specific language: `{;lang-en-GB;string_to_translate;}`
Usage of translations form specific language from core: `{;lang-en-GB;core;string_to_translate;}`

Error handling
--------------
Sometimes in module we have some error or warning and we want to show them. Blue
Framework has special method to show that kind of information and special array
that count appears of that information.

### Error, warning, info, success information
All that information use the same method `$this->error()` but with different parameters.
All of them are stored in special array and will be placed in special marker depends
of chosen group (error|warning|info|success). That markers looks like that `{;core_type;}`
where type is group name `{;core_error;}`. In that markers will be displayed all
information called by `error()` method and by `exception` usage.  
Base construction looks like that: `$this->error('type', 'title', 'message')`. In
message parameter we can use translation markers.

#### Error
To create error information _(wont stop module script)_ use type `critic`
`$this->error('critic', 'title', 'message')`. That will add error to special array
and set value of array `$this->error` to:

```php
array (
  'ok'      => NULL,
  'info'    => NULL,
  'warning' => NULL,
  'critic'  => 1,
)
```

To display messages with type `critic` use that marker: `{;core_error;}`.

#### Warning
To create warning information use type `warning` `$this->error('warning', 'title', 'message')`.
That will add warning to special array and set value of array `$this->error` to:

```php
array (
  'ok'      => NULL,
  'info'    => NULL,
  'warning' => 1,
  'critic'  => NULL,
)
```

To display messages with type `warning` use that marker: `{;core_warning;}`.

#### Information
To create information use type `info` `$this->error('info', 'title', 'message')`.
That will add info to special array and set value of array `$this->error` to:

```php
array (
  'ok'      => NULL,
  'info'    => 1,
  'warning' => NULL,
  'critic'  => NULL,
)
```

To display messages with type `info` use that marker: `{;core_info;}`.

#### OK
To create success information use type `ok` `$this->error('ok', 'title', 'message')`.
That will add success to special array and set value of array `$this->error` to:

```php
array (
  'ok'      => 1,
  'info'    => NULL,
  'warning' => NULL,
  'critic'  => NULL,
)
```

To display messages with type `ok` use that marker: `{;core_ok;}`.

#### To specific marker
There is possibility to give marker name to witch won content of message will be
written, that allow to set some information without usage special markers and
`$this->error` array. To do this in place of type parameter use marker key.

```php
$this->error('marker_key', 'title', 'message');
```

### Exceptions
Each module support handling of Exceptions that will stop module work and will
add some `critic` error to list. There are couple exceptions that module handle:

1. **coreException** - will show `critic` error message information and stops working of all modules
2. **modException** - will show `critic` error message information and try to lunch module in error mode `runErrorMode`.
3. **warningException** - will show `warning` message information and stop module working
4. **infoException** - will show `info` message information and stop module working
5. **okException** - will show `ok` message information and stop module working
6. **Exception** - catch all not handled exceptions, catch their messages and create coreException. If there was some error in coreException stop all modules.

Of course we can create own `try/catch` block inside module and handle catch error
by own way.

### Error mode
If module throw `modException` framework will try to start module in error mode
using to this `runErrorMode` method decelerated in module. That possibility can
be useful to revert some operation that module make, after module crash. Also when
`modException` was throw, framework will add `critic` message to list.  
If module in error mode will throw `coreException`, then all modules will be stop
and `critic` error will be set up.  
If module in error mode will throw `modException`, only `critic` error message will
be set up. No other exceptions are handled in error mode.

Interaction between modules
--------------
All modules can communicate with some other modules, but that depends of lunch order
decelerated in `tree.xml`. That allows to give some instructions to some other modules
or use some common data, that was returned by some other module method.  
Of course we can use SESSION for that, but we must remember to remove data from
session after other module use it. Better way is use to do this Blue Framework way.  
To use that feature we simply use `$this->modules` variable, that store all lunched
modules as their instance. So if we want to give data to some other module just
create in module class public variable like `$this->exchange = 'some data';`, and in
other module, lunched after first module use `$otherData = $this->modules['module_name']->exchange`.
In that way we can also easily lunch some other modules public methods!!

### Stop running other modules
Another thing that we can make with modules form some other module is to stop lunching
them. To do this we use one method `$this->_disabled('module')` with module class name
as parameter. That inform framework to skip module lunch, but remember that we can
only skip modules that will be lunchad after module in witch we run `_disabled` method.  
Another thing is stopping all modules that will be lunched after our module and
to do this use `$this->stop()` method.

Meta data
--------------
Sometimes some specific pages require to change some meta data of page. Mostly
that will be some pages like article or gallery, etc. For that kind of pages we
must change page title, add some description to page, change keywords and all
meta elements we want.  

### Add new meta elements
To create new meta element we have only one method `$this->addMetaTag()` that
takes only one parameter, witch is full mete tag element.

```php
$this->addMetaTag('<meta charset="UTF-8"/>');
```

That will add meta tag to html structure, but only if in `head` node will be
`{;core;meta;}` marker.  
That method will only add meta, so if in the page structure exist the same meta
it wont be replaced, but two the same meta will appear on rendered page.

### Update existing meta tag
Another useful method is method to update existing meta tag `$this->addToMetaTag()`
that takes two parameters. First is name of meta tag `name=""` and second is value
to add.

```php
$this->addToMetaTag('keywords', ', keyword one, keyword two');
```
That method can update only element created by `meta_class` and declared in `meta.xml`.

Usage modules in tree.xml structure
--------------
All modules we decelerate in xml page structure and implement to specific page, or
to all pages if we create module inside `root` node. Typical module declaration
looks like that: `<mod on="1">module</mod>`. Attribute on inform framework to use
or not given module _(1 - module is on, 0 - module is off)_. That attribute is
always required.  
Module node has also some other attributes:

1. `param` - module parameters as string separated by param_sep configuration value
2. `exec` - name of file in module directory to run if is different than default
3. `block` - name of block to load module, `order == position on tree`

Order of modules in xml structure is very important. Modules will be lunched in the
same order that they created in structure. So if some modules required data from
another module, want to stop them, wants lunch to other module method and etc.
remember about that order.