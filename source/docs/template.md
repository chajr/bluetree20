Layout and templates
====================

Basic information
--------------
Whole layout is created in `tree.xml` definition, where we create page/subpages structure.
There we load main template, that is always required for page or subpage. In that template
 we define some special markers with block name or module name that will be replaced
 by content generated by module.  
In that way we create basic layout.

Main templates
--------------
Min templates are used only by pages and subpages. Localized in `BLUE_FRAMEWORK/elements/layouts`
 hes two roles. First is basic layout for chosen page and second is layout for chosen page.
 In that second role we can apply some special markers that will be replaced by content
 generated by modules.  
Beside templates we define for pages/subpages we have some predefined templates
 for special usage:

1. **empty.html** - helping when we want to lunch some page without content to return, to avoid empty template error
2. **error.html** - base template to show critical framework errors
3. **error_critic.html** - display some critic errors from modules, don't stop framework
4. **error_info.html** - display some information from modules
5. **error_ok.html** - display some success messages
6. **error_pointer.html** - display errors added manually by module
7. **error_warning.html** - display some warnings from modules
8. **techbreak.html** - display information about technical break if `techbreak` value is set to 1 in `config.xml`

Of course we must have some template for index page. Filename has `html` extension
 but content of the file can be anything that we want to display for given page `xml, json, etc.`.  
But template **cannot be empty**, must have at last one marker that will be replaced by content
 or some string to display.

External templates
--------------
In all templates we can load some other templates by special marker. All that external templates
must be localized in directory where main template is localed. To load external template
use `{;external;template_name;}` marker. Of course put only template fle name
without file extension. as template name we can give path to external file, relative
to default template.

Module templates
--------------
Each module can has their own templates to display some content. That templates are
localized in module directory `/BLUE_FRAMEWORK/modules/module_name/layouts/some_template.html`.
In default module don't have any template, we must inform framework that module will
use template by special method where we put template name. If module extends `module_class`
use this method: `$this->layout('some_template');` otherwise `$this->core->layout('module_name', 'some_template');`.

### Loading module template to core template.
To load template generated by module we must use some special markers. There are
two ways. First and the simplest is usage module marker `{;mod;module_name;}`. That
marker will be automatically replaced by content generated by `module_name` module.  
Second way is usage block markers, used to load couple modules in one place in core
template, or in some other module templates. To define that module will be loaded
into block, go to `tree.xml` and in module declaration add attribute `block` with
value that will be block name.

Using markers
--------------
Generating content is based on replacing by `display_class` special string constructions
 named `markers`. Some markers are automatically detected by `display_class` and
 replaced, other need to be replaced by some special methods. All markers we can group
 by scope that are lunched, and operations what they make.  
All core markers will be replaced when template is rendering.

### Simple content marker
Most simple marker is used to display some string content. To use it in template paste
 `{;code;}` marker and in module use `$this->generate('code', 'some content to display');`.
 That will replace `{;code;}` by `some content to display` string. If marker wont be replaced
 by any evaluation of `generate` method it will be automatically removed from template
 by `_clean` method run on template rendering.  
We can put in template so many markers on the same name as we want and all of them will
 be replaced by the same content at the first run of `generate` method.

### Optional content markers
In templates we can use some simple logic using for that special markers. Content between
 that special markers will be displayed only if all markers in that group will be
 replaced by some content, or empty strings.

```
{;op;group_name;}
some optional content {;show;}
{;op_end;group_nem;}

{;op;group_name;}
some optional content 2 {;show;}{;show2;}
{;op_end;group_nem;}
```

In that example `some optional content` will be displayed only of `{;show;}` marker
 was replaced by empty string `$this->generate('show', '');`, but `some optional content 2`
 wont be replaced because `{;show2;}` marker is not replaced.

### Array data markers
To create similar data like tables or list of some data we can use special marker
 group to generate content by loop. To do this we use `$this->loop('list', $dataArray)`
 and as `$dataArray` we give an array of arrays where key is some special marker.  
Base markers is `{;start;group_name;}` that determinate the beginning of group and
 `{;end;group_name;}` given at the end of group. Inside of group we must create
 markers that wil contains two elements. First is group name `group_name` second
 is name of key that will contains value `{;group_name;key;}`. Each of that markers
 will be replaced by data given in array. We have also some optionally  group of markers
 to show content if array with data is empty. At the beginning use `{;start_empty;group_name;}`
 marker and at end `{;end_empty;group_name;}`. Between that two markers write
 content to be displayed if data for loop markers will be empty or NULL.  
Data for `loop` method:

```php
$dataArray = array(
    0 => array('id' => 1, 'content' => 'example 1'),
    1 => array('id' => 2, 'content' => 'example 2'),
    2 => array('id' => 3, 'content' => 'example 3'),
);
$this->loop('list', $dataArray);
```

Template structure data:

```html
Data list marker structure with empty array example:
{;start;list;}
<div>{;list;id;}. Content {;list;content;}.</div>
{;end;list;}
{;start_empty;list;}
<div>There was no data to show.</div>
{;end_empty;list;}
```

That marker structure will display that html content:

```html
<div>1. Content example 1.</div>
<div>2. Content example 2.</div>
<div>3. Content example 3.</div>
```

If `$dataArray` will be empty or NULL, renderer will display
 `<div>There was no data to show.</div>`.

#### Optional and simple markers in array data markers
WE can also use some other markers with array markers. Simply markers will always
 display the same content in each row, and optionally marker display only content
 if inside of group all markers will be replaced.  
If we use some simply markers inside of array data markers, is good to performance
 firstly run of `generate` simply marker method and after that run `loop` method.

```php
$dataArray = array(
    0 => array('id' => 1, 'content' => 'example 1'),
    1 => array('id' => 2, 'content' => 'example 2', 'optional' => ''),
    2 => array('id' => 3, 'content' => 'example 3'),
);
$this->generate('special', 'Special');
$this->loop('list', $dataArray);
```

```html
{;start;list;}
<div>{;list;id;}. {;special;} content {;list;content;}{;op;optional;}, bla bla{;list;optional;}{;op_end;optional;}.</div>
{;end;list;}
```

That part of code will display:

```html
<div>1. Special content example 1.</div>
<div>2. Special content example 2, bla bla.</div>
<div>3. Special content example 3.</div>
```

### Core markers used in main template
#### Meta data marker
**{;core;meta;}** - Allow to display metadata nodes in main templates

#### Cascade Style Sheaths marker
**{;core;css;}** - Allow to display nodes with url to css _(internal or external)_

#### Java Script marker
**{;core;js;}** - Allow to display nodes with url to js scripts _(internal or external)_

#### External template marker
**{;external;template_name;}** - Load some another template, localed in the same directory
**{;external;some_directory/template_name;}** - Load some another template, localed in the _some_directory_ directory

#### Module content marker
**{;mod;module_name;}** - Replaced by content from module (only in core templates) named `module_name`

#### Module block marker
**{;block;block_name;}** - Replaced by content from modules that has decelerated `block` attribute in `tree.xml` with the same value of `attribute` and `block_name`

#### Special messages block marker
1. **{;core_error;}** - Display error messages
2. **{;core_warning;}** - Display warning messages
3. **{;core_info;}** - Display information messages
4. **{;core_ok;}** - Display success messages

### Core markers used in module templates _(can be used in main template)_
#### URL markers
1. **{;core;domain;}** - Replaced by actually lunched domain optionally with test directory `(http://my-site.pl)`
2. **{;core;lang;}** - Replaced by actually lunched language code, like `pl-PL/`
3. **{;core;mainpath;}** - Replaced by actually lunched domain with test directory is set and language code `http://my-site.pl/pl-PL/`
4. **{;path;/strona/podstrona/param,val/p2,v/;}** - Convert given key => value data to mode rewrite url or classic url
5. **{;full;/strona/podstrona/param,val/p2,v/;}** - Convert and apply key => value data domain with language code
6. **{;rel;/strona/podstrona/param,val/p2,v/;}** - Convert and apply key => value data domain with language code, relative to current page

In that markers you can use markers replaced from `display_class`. By this feature
you can put in path marker an parent marker to build return to previous page
button

```
{;core;domain;}{;core;lang;}{;path;/{;parent;};}
```

That construction will return something like this: `http://my-site.pl/?core_lang=pl-PL&p0=parent_page`

### Translation markers
#### Base translations
For text we want to translate use special markers `lang`. That marker will be replaced by
 content defined in translation files. That id described in `language-usage.md` file.
 Simply to use translation use construction `{;lang;content_code;}` that will be replaced
 by content for chosen language. If translation wont be found, then framework will show language code `{content_code}`.  

#### Force translation language
If we want to always display some content in specific language (eg. in english) we use
 special construction of language marker with language code `{;lang-en-GB;content_code;}`.
 That will always display text in english translation, of course is english language is on
 and language file with that code was loaded.

#### Translation from other scope
There is possibility to use translation files from other modules (if their was loaded)
 or core. To use that translation possibility us construction like: `{;lang;module_name;translation_code;}`.
 To use translations from core replace `module_name` by `core`.

#### Translation from other scope with forced language
Also we can join _force translation_ with _other scope translation_ by using this construction:
 `{;lang-en-GB;module_name;translation_code;}`. That will use translation from
 `module_name` and force translation to `en-GB` language.

```
We can replace marker by some another marker, that will be changed by some other
module methods, or core methods. We can make this because `display_class` replace
markers on the fly.
```

### Usage of chars in markers
System of markers has some restrictions with usage some chars in markers construction.
Basically all content markers to be detected (for remove unused markers) use that
 regular expression **`{;[\w=\-|&();\/,]+;}`**. So all markers must be compatible
 with that regular expression. The same regular expression is used to remove URL markers
 so that will also determinate usage of chars in URL path.  
In simple marker you can use chars as you want to wor, but remember that mark with
 some un allowed chars wont be removed from template if wasn't replaced by some content.

Access to core templates by module
--------------
Each module can generate som content in own template (if has some) and also in core templates.
 But marker can be replaced only one time, when some other module will replace content
 marker, that marker will be unavailable. To replace some content in core template
 use `generate` method with third parameter set on `TRUE`  
`$this->generate('marker_code', 'some new content', TRUE);`  
If module dos not extends `module_class` we can get access to core template by
 `$this->core->generate('marker_code', 'core', 'some new content');`  
In that way we can also have **access to template of some other module**, just at second
 parameter give module name. But remember that the module we want to replace content
 must be loaded before module in witch we want to have access and must have market
 that we want to replace by content.

Usage some other templates in module
--------------
All that templates described in that documentation are integrated part of
 framework `display_class` that is run by core, and all of them are loaded
 automatically. But we can also usage in module some other templates, without
 usage of loaded in core `display_class`. That is possible because we can use
 `display_class` in independent tribe. That tribe allow to use that class inside
 of some module and replace some marker by rendered content using `$this->generate()`
 method. In that tribe we must give path to template that is localed somewhere in
 `/BLUE_FRAMEWORK` directory.

```php
$independent = new display_class(array(
    'independent' => TRUE,
    'template'    => 'template/address/template.html'
));
$independent->generate('some_marker', 'some content');
$data = $independent->render();
```

### Usage translations and some other codes
Because `display_class` remove all not replaced markers, we cannot use any features
related to translation or core markers. But if we want to display rendered by
`display_class` on some other module or core templates we can turn off cleaning
markers in `display_class`, so all markers that was leaved will be replaced or
cleaned by `display_class` lunched by `core`.  
To use that feature just give special option to `display_class` instance:

```php
$independent = new display_class(array(
    'independent' => TRUE,
    'template'    => 'template/address/template.html',
    'clean'       => FALSE
));
```

All options are described in [module](/docs/module.md "Create and usage module")
in All configuration for display_class section.

Display data from session by special markers
--------------
Last way to render some content in template is use to this `SESSION`. We can put
some data into session in special group and after that in next framework instance
special markers will be replaced by data from session.  
*That way don't handle translations*

```php
$this->setSession(
    'marker_name',
    'some data to render on page',
    'display'
);
```

After that declaration and page refresh we will get that message on each template
where we put special marker `{;session_display;marker_name;}`.  
But if we want to use that feature when we use some additional templates, we must
also give `session` object to `display_class` `__construct`.

```php
$firstTemplate = new display_class(
        'independent' => TRUE,
        'template'    => 'template/address/template.html',
        'session'     => $this->_session
    );
```