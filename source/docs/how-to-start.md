How to start using framework
====================
Describe step by step how to write working page with configuration, some module
and library.

Base configuration
--------------
First step before lunching framework is base configuration, that can be found in
`/BLUE_FRAMEWORK/cfg/config.xml`. Whole configuration description is available in
[configuration](/docs/configuration.md "How configure framework and modules")
documentation.  
Most important thing to configure is:

1. **domain** - as regular expression our domain
2. **test** - if framework will be lunched in some domain directory, give that directory name _(my-site.pl/dir)_
3. **rewrite** - set on 1 if page will use mode rewrite _(if not remember to remove .htaccess from main directory)_
4. set language configuration if page will have more than one language support

To start test version we set that options as bellow:

```xml
<option id="domain" value="#localhost#"/>
<option id="test" value=""/>
<option id="rewrite" value="1"/>
<option id="one_lang" value="0"/>
<option id="lang" value="pl-PL"/>
<option id="lang_support" value="1"/>
<langs id="lang_on" value="1">
    <lang id="pl-PL" on="1"/>
    <lang id="en-GB" on="1"/>
</langs>
```

Create simple page in tree
--------------
When framework is configured we can build structure of our site. To do this go to
`/BLUE_FRAMEWORK/cfg/tree.xml` file, where will be defined whole page structure
as xml tree of dependencies. Whole tree structure description is available in
[page-structure](/docs/page-structure.md "Create page structure and routers")
documentation.  
At the top we have defined all common libraries, modules, css and js that all
pages and subpages will use. To create base page `<page>` node must be created
that will define first level page. To define **index** page _(master page)_ just
set value of `id` to `index`: `<page id="index">`.  
Inside `page` node we can define another required _(just for that page and children pages)_
libraries, modules and subpages. But for now we must only set required attributes
for that node, that are:

1. **id** - id of first level page, must be unique for all first level pages
2. **name** - basic page name that will be used to build page map and breadcrumbs structure
3. **layout** - main layout for that page, cannot be empty file
4. **options** - some other page options

For very simply, one page site, page structure will be like this:

```xml
<!DOCTYPE root SYSTEM 'dtd/tree.dtd'>
<root options="1">
    <page id="index" name="Main Page" layout="index" options="1111"></page>
</root>
```

Create simple layout
--------------
To create basic template for page, go to `/BLUE_FRAMEWORK/elements/layouts/` directory
and create file with the name given as `layout` attribute value with `.html`
extension. For our example it will be `/BLUE_FRAMEWORK/elements/layouts/index.html`.
Inside of that file write some simply string, like _Hello World_.  
That layout is basic layout for page, all modules will load his rendered layout
inside of that basic layout.  
Full description about layouts and templates you can find in
[template](/docs/template.md "Layout and templates") documentation.

After that step, when you lunch framework directory in you browser, you will see
_Hello World_ string on the screen. That means that framework is working correctly.

![example1](/image/example1.png "example 1")

Add some metadata
--------------
Now we add some meta data like page title, page description, for page that we
already create. Whole description about usage of meta tags can be found in
[meta-tags](/docs/meta-tags.md "Meta tags usage") documentation. For now we write
only some basic elements.  
File with meta tags description is `/BLUE_FRAMEWORK/cfg/meta.xml` and has one
required node that names `default`. That node described common meta tags for all
pages, all other pages can extend them, or override. Default node has also all
elements that we ned for our main page.  
So to set title we create special node called `title` with attribute `title` and
`update`. Value of that attribute will be an page title and attribute `update` don't
have any big matter, can be set for `1` or `0`. You can also use translation
markers for display page title like `{;lang;title;}` so `display_class` will take
value of title for current language from main translation files. For all other
meta tags use `meta` node with all common attributes `name, content, update`.

For our page, default meta tag node will look like that:

```xml
<!DOCTYPE root SYSTEM "dtd/meta.dtd">
<root>
    <default val="index">
        <title title="Hello world title" update="1"/>
        <meta name="description" content="That is simple test page" />
    </default>
</root>
```

That constriction will add to our page elements title and meta tag. But only if
we put in page template special marker `{;core;meta;}`. Without that marker
page meta elements wont be displayed. So you must return to index template and
write some html with that special marker.

```html
<!doctype html>
<html>
    <head>
        <meta charset="utf-8"/>
{;core;meta;}
    </head>
    <body>
        Hello World
    </body>
</html>
```

**Meta tags works only if `meta` option in `config.xml` is set to `1`.**

![example2](/image/example2.png "example 2")

Add base styles and scrips
--------------
Ok now we have basic html page, but its look is not much pretty. To change view
of our example page we must include some styles and sometimes some java scripts.
Full description of usage that elements can be found in
[scripts_and_styles](/docs/scripts_and_styles.md "Scripts and Styles") and in
[page-structure](/docs/page-structure.md "Load css and js scripts") in Load css
and js scripts section.  
For noe we set some simply styles and scripts for all pages, and only for main page.
That elements we define in `/BLUE_FRAMEWORK/cfg/tree.xml` using for that special
nodes `<css>` and `<js>`, and like in meta tags to use defined styles and scripts
in page we must use special markers to display them, `{;core;css;}` fo styles and
`{;core;js;}` for scripts. So our `/BLUE_FRAMEWORK/elements/layouts/index.html`
file content will look like that:

```html
<!doctype html>
<html>
    <head>
        <meta charset="utf-8"/>
{;core;meta;}
{;core;css;}
    </head>
    <body>
        Hello World
        {;core;js;}
    </body>
</html>
```

And now updated structure for `/BLUE_FRAMEWORK/cfg/tree.xml` and created some
nodes for css and js. All css and js files that are defined in `tree.xml` file
are localed inside of `/BLUE_FRAMEWORK/elements/css/` and `/BLUE_FRAMEWORK/elements/js`
directories.

```xml
<!DOCTYPE root SYSTEM 'dtd/tree.dtd'>
<root options="1">
    <css>sample1</css>
    <js>sample1</js>
    <page id="index" name="Main Page" layout="index" options="1111">
        <css>sample2</css>
        <js>sample2</js>
    </page>
</root>
```

And to show changes we add that files and some content to them.

Create `/BLUE_FRAMEWORK/elements/css/sample1.css` and write inside:

```css
html{
    background-color:#000;
    color: #fff;
}
```

Create `/BLUE_FRAMEWORK/elements/css/sample2.css` and write inside:

```css
html{
    color: #aaa;
}
.test_div{
    text-align:center;
    border: 1px solid #fff;
    padding:10px;
}
```

Create `/BLUE_FRAMEWORK/elements/css/sample1.js` and write inside:

```java
alert('first script');
```

Create `/BLUE_FRAMEWORK/elements/css/sample2.js` and write inside:

```java
alert('second script');
```

_All `sample2` files will be loaded only for `index` page._

After that changes and refresh page you will see something like this:

![example3](/image/example3.png "example 3")

Create simple module
--------------
That was all for creating some simple page. No we go to real making framework usage
**Modules**. Modules are part of framework that real decide what opened page will
look ad what exactly page will do. Full description about modules can be found in
[module](/docs/module.md "Create and usage module") documentation.  
To create simple module go to `/BLUE_FRAMEWORK/modules/` directory and create some
directory like `test`, that will be also our base module name. Inside create
`test.php` file, that will be our module main file. Structure of that file will
look like bellow:

```php
<?php
class test extends module_class
{
    public function run()
    {
        echo 'Module Hello World';
    }
    public function runErrorMode(){}
}
```

If module extends `module_class` it must have `run()` method and `runErrorMode`
that last one for now will be empty and will be filled later. For now that module
do nothing and also its not loaded after framework starts. To run that module we
must define in in page structure tree so framework can load it and lunch.  
Go to `tree.xml` file and add our module to create page using `<mod>` node with
name of created module.

```xml
<root options="1">
    <css>sample1</css>
    <js>sample1</js>
    <page id="index" name="Main Page" layout="index" options="1111">
        <mod on="1">test</mod>
        <css>sample2</css>
        <js>sample2</js>
    </page>
</root>
```

That will lunch our module only for index page and all index children's.  
After refresh page we will see another string `Module Hello World` on the top of
the page, but if you go to page source you will see that string from module is
displayed before html page structure.

![example4](/image/example4.png "example 4")

That is happening because all modules are lunched before whole structure to display
is created, so rendering of page is make at the end of framework usage. That
example show us only that module works and whole module operations are executed
before page rendering.

Display module content
--------------
Ok, but we want to display content from module in place that we want in created
html structure. To do this we use some special methods to access `display_class`
methods responsible for replacing some special content markers. Go to our index
page main template _(index.html)_ and replace `Hello World` by `{;hello_marker;}`.  
Now go to our `test` module and inside `run()` method replace echo statement by
`$this->generate('hello_marker', 'Hello World', 'core')`

```php
<?php
class test extends module_class
{
    public function run()
    {
        $this->generate('hello_marker', 'Hello World', 'core');
    }
    public function runErrorMode(){}
}
```

After page refresh you will se only one string `Hello World` in place that you
paste our marker. By generate method we can replace all markers inside our module
template _(skip last generate method parameter)_, some other module template
if module was called before current module or we can replace some markers inside
main page template. To use that feature paste as third parameter module name or
`core` if you want to replace main template marker.

![example5](/image/example5.png "example 5")

**One time replaced marker is not existing anymore, so markers can be replaced
only one time.**

Create module layout
--------------
Main page template is like main layout, it have base structure for modules template.
Each module can have own template, or templates. To use module template firstly
we must create file inside module `layout` directory. Better description can be
found in [module](/docs/module.md "Module templates") in Module templates section.  
Create `/BLUE_FRAMEWORK/modules/test/layouts/test_template.html` file and inside
create some html content like below:

```html
<div class="test_div">
    {;hello_marker;}
</div>
```

Next go to page template, and replace our marker `{;hello_marker;}` by `{;mod;test;}`
so we inform framework to replace that marker by content rendered for module. Next
go to `test` module and make some changes in `run` method. From `generate` method
remove last parameter `core`, and before write method that will load template
for our module `$this->layout('test_template')`. Our modified `test.php` file will
look like that:

```php
<?php
class test extends module_class
{
    public function run()
    {
        $this->layout('test_template');
        $this->generate('hello_marker', 'Hello World');
    }
    public function runErrorMode(){}
}
```

Thanks that view of our test page after refresh will change and will look like
image bellow:

![example6](/image/example6.png "example 6")

Using translations
--------------
Framework has system of automatic translations, by using for that special markers,
if we turn on usage of language. First to use that feature we must create translation
files for each allowed language _(If some translation file wont be found, framework
will try to load similar language and if that language wont be fond will load
translations for default language)_. Whole description about using translations
can be found in [template](/docs/template.md "Translation markers") in Translation
markers section and also in [module](/docs/module.md "Internationalization") in
Internationalization section. Remember that core has own translation files _(can
 be used by modules)_ and each module has own translation files.
So create two translation files for page template, and two another form module
with given content:

###/BLUE_FRAMEWORK/cfg/lang/core_en-GB.php

```php
<?php
$content = array(
    'core_translation_1'    => 'some core translation',
    'core_translation_2'    => 'some another core translation',
);
```

###/BLUE_FRAMEWORK/cfg/lang/core_pl-PL.php

```php
<?php
$content = array(
    'core_translation_1'    => 'jakieś tułmaczenie z jądra',
    'core_translation_2'    => 'jakieśkolejne tułmaczenie z jądra',
);
```

###/BLUE_FRAMEWORK/modules/test/lang/test_en-GB.php

```php
<?php
$content = array(
    'module_translation_1'    => 'some module translation',
    'module_translation_2'    => 'some another module translation',
);
```

###/BLUE_FRAMEWORK/modules/test/lang/test_pl-PL.php

```php
<?php
$content = array(
    'module_translation_1'    => 'jakieś tułmaczenie z modułu',
    'module_translation_2'    => 'jakieśkolejne tułmaczenie z modułu',
);
```

Next step we must inform module to use translations by add simple method in `run()`
at the top of method. Page templates don't require that kind of actions, because
core templates are translated automatically if renderer found translation markers.

```php
<?php
class test extends module_class
{
    public function run()
    {
        $this->_translate();
        $this->layout('test_template');
        $this->generate('hello_marker', 'Hello World');
    }
    public function runErrorMode(){}
}
```

Last thing that must be done for usage translations is to paste translation markers
in templates. In page template `/BLUE_FRAMEWORK/elements/layouts/index.html` change
content to look like bellow:

```html
<!doctype html>
<html>
    <head>
        <meta charset="utf-8"/>
{;core;meta;}
{;core;css;}
    </head>
    <body>
        {;lang;core_translation_1;}
        <br/>
        {;mod;test;}
        {;core;js;}
    </body>
</html>
```

and in module template to look like that:

<div class="test_div">
    {;hello_marker;}
</div>
<div class="test_div">
    {;lang;module_translation_1;} - {;lang;module_translation_2;} - {;lang;core;core_translation_1;}
</div>

So after refresh our page will look like that:

![example7b](/image/example7b.png "example 7 english")

Then change chosen language in URL to other defined in `config.xml` and refresh
page, content will change to that:

![example7a](/image/example7a.png "example 7 polish")

Errors and information from module
--------------
Sometimes we can inform user about some action that was happen, about error or
module exception _(when module don't work properly)_. To do that we can use build
in framework system of messages and exceptions. Whole description about usage can
be found in [errors](/docs/errors.md "Framework errors") documentation.

### Exceptions
Exceptions are used to handle some module, framework or even libraries errors.
But can also be used to stop working module and display some information, not only
error _(error, warning, info, success)_. Now we make three things, add some exception
into module and force framework tu run module in error mode, and in that mode add
some information message to display.  
First go to our main template `/BLUE_FRAMEWORK/elements/layouts/index.html` and
add special markers that will handle all kind of messages. Each marker handle only
specific messages group.

```html
<!doctype html>
<html>
    <head>
        <meta charset="utf-8"/>
{;core;meta;}
{;core;css;}
    </head>
    <body>
        <div class="messages">
            {;core_error;}
            {;core_warning;}
            {;core_info;}
            {;core_ok;}
        </div>
        {;lang;core_translation_1;}
        <br/>
        {;mod;test;}
        {;core;js;}
    </body>
</html>
```

Because framework use for each kind of message own template, we must create them
all inside of `/BLUE_FRAMEWORK/elements/layouts/`. For that example we create only
two of them, one for error and one fore success message. Examples bellow has some
new markers, all of them re described in [template](/docs/template.md "Layout and templates")

#### /BLUE_FRAMEWORK/elements/layouts/error_critic.html

```html
{;start;errors;}
<div class="error_block">
    <b>{;errors;error_code;}</b>
    {;op;extend_message;}<br/>
    {;errors;extend_message;}{;op_end;extend_message;}<br/>
    {;op;module;}<br/>
    {;errors;module;}{;op_end;module;}
</div>
{;end;errors;}
```

#### /BLUE_FRAMEWORK/elements/layouts/error_ok.html

```html
{;start;errors;}
<div class="success_block">
    <b>{;errors;error_code;}</b>
    {;op;extend_message;}<br/>
    {;errors;extend_message;}{;op_end;extend_message;}<br/>
    {;op;module;}<br/>
    {;errors;module;}{;op_end;module;}
</div>
{;end;errors;}
```

No we make some simply styles to recognize messages easier. Go to file
`/BLUE_FRAMEWORK/elements/css/sample1.css` and add some styles fo our new elements:

```css
.error_block{
    width:80%;
    padding:10px;
    text-align:center;
    margin: 10px auto;
    border:1px solid #f00;
}

.success_block{
    width:80%;
    padding:10px;
    text-align:center;
    margin: 10px auto;
    border:1px solid #0f0;
}
```

Now we modify our test module to show up that messages. Change whole module content
to look like that. Usage of translations for messages and how to replace error
codes are described in [template](/docs/template.md "Layout and templates") and
[errors](/docs/errors.md "Framework errors"):

```php
<?php
class test extends module_class
{
    public function run()
    {
        $this->_translate();
        $this->layout('test_template');
        $this->generate('hello_marker', 'Hello World');

        throw new modException('error_from_module', 'bla bla bla');
    }

    public function runErrorMode()
    {
        $this->error(
            'ok',
            'success',
            'module started in error mode'
        );
    }
}
```

Now if we reload test page we get content looking like that:

![example8](/image/example8.png "example 8")

### Messages

Create simple library
--------------
Some part of codes can be common for some modules. That can be some model, helpers
or some other libraries that provide some  helpful methods. That kind of classes
in framework are called _libraries_ that can be grouped in packages. Package is
simply a directory that will have libraries that have some similar or common
elements. All libraries from package can be load by giving only package name _(
will load all libraries from package)_ or some libraries name _(will load ony
that libraries from package)_.  
Go to `/BLUE_FRAMEWORK/packages/` directory and you will see some existing packages
**`CORE` is package with framework libraries, so that directory we can not change**  
Create new directory for our new package named `pack`. Inside create file named
`test_lib_class.php` and inside add content:

```php
<?php
class test_lib_class
{
    protected $_param = NULL;

    public function __construct ($param)
    {
        $this->param = $param;
    }

    public function getParam($transform = FALSE)
    {
        if ($transform) {
            return strtoupper($this->param);
        }
        return $this->param;
    }
}
```

So noe we have library that take in create some string and can return it looking
the same as given or converted to upper case. Now we will apply that library into
module, but first we must add that library to `tree.xml` that framework can load
it.

```xml
<root options="1">
    <lib on="1">pack</lib>
    <css>sample1</css>
    <js>sample1</js>
    <page id="index" name="Main Page" layout="index" options="1111">
        <css>sample2</css>
        <js>sample2</js>
        <mod on="1">test</mod>
    </page>
</root>
```

Now library can be used by all modules in each page and subpage. Go to our test
module and chang whole content to look like that:

```php
<?php
class test extends module_class
{
    public function run()
    {
        $this->_translate();
        $this->layout('test_template');
        $this->generate('hello_marker', $this->prepareContent());
    }

    public function prepareContent()
    {
        $string     = 'bla bla bla';
        $content    = '';
        $lib        = new test_lib_class($string);

        $content .= $lib->getParam();
        $content .= ' - ';
        $content .= $lib->getParam(TRUE);

        return $content;
    }

    public function runErrorMode()
    {
        $this->error(
            'ok',
            'success',
            'module started in error mode'
        );
    }
}
```

No we can go to our browser and refresh page, so we can see result of our changes.

![example9](/image/example9.png "example 9")

Create block for module
--------------
Another useful feature is to load some modules exactly in given place, that is
allowed by creating group marker. That marker will be replaced by content of modules
that was assigned to block, in the same order that modules was lunched.  
Go to our page template `/BLUE_FRAMEWORK/elements/layouts/index.html` and replace
our module marker `{;mod;test;}` to that one: `{;block;test_block;}`. `test_block`
will be our identifier that we will call to put module content for that block.  
Now go to `tree.xml` and find our module definition node `<mod>` and add new
attribute with block identifier as value `block="test_block"`. After refresh page
content should not change, but now all modules that will be defined with the same
attribute value, appear at this place in main page structure.

![example9](/image/example9.png "example 9")

Create another module and load it to block
--------------
So now to better test block feature we will create simply module with simply
content only to load it in test block place.  
Create `/BLUE_FRAMEWORK/modules/second/` directory for our second module.
Now create template for module with this content and name it `second_template`:

```html
<div class="test_div">
    {;another_module;}
</div>
```

And create module class with this content:

```php
<?php
class second extends module_class
{
    public function run()
    {
        $this->layout('second_template');
        $this->generate('another_module', 'second module content');
    }

    public function runErrorMode(){}
}
```

Last thing that must be done, add module definition to `tree.xml` so we have
structure like that:

```xml
<root options="1">
    <lib on="1">pack</lib>
    <css>sample1</css>
    <js>sample1</js>
    <page id="index" name="Main Page" layout="index" options="1111">
        <mod on="1" block="test_block">test</mod>
        <mod on="1" block="test_block">second</mod>
        <css>sample2</css>
        <js>sample2</js>
    </page>
</root>
```

Now go to main page and reload it. Page content will change and will look like this:

![example10](/image/example10.png "example 10")

Create subpage with some module
--------------
Last thing we will make in that documentation is to create some subpage, that will
be children of our index page.  
First we make URL to our new page in `test` module template using for that some
special markers, so we can get URL to our new page independently form `rewrite` option.

```html
<div>
    <a href="{;core;domain;}{;core;lang;}{;path;/index/test_page;}">
        Go to subpage
    </a>
</div>
<div class="test_div">
    {;hello_marker;}
</div>
<div class="test_div">
    {;lang;module_translation_1;} - {;lang;module_translation_2;} - {;lang;core_translation_1;}
</div>
```

Now we define our new page in `tree.xml` using the same main template as main
page and apply to in our second module. But we will turn off usage all parent
modules, styles and scripts by setting `sub` node options to `11110`. Default
framework will load all styles, scripts, modules and libraries from parent page
so we wont be see any differences, to see that differences we must turn off
inheritance:

```xml
<root options="1">
    <lib on="1">pack</lib>
    <css>sample1</css>
    <js>sample1</js>
    <page id="index" name="Main Page" layout="index" options="1111">
        <sub id="test_page" name="Subpage" layout="index" options="11110">
            <mod on="1" block="test_block">second</mod>
        </sub>

        <css>sample2</css>
        <js>sample2</js>
        <mod on="1" block="test_block">test</mod>
        <mod on="1" block="test_block">second</mod>
    </page>
</root>
```

And that all, when you refresh page, you will se link to new test page, after click
our new page with only `second` module will be displayed.

![example11](/image/example11.png "example 11")
