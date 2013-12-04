Create page structure and routers
====================

Doctype definition
--------------
### DTD nodes description

1. **root** - main node of tree, always required, contains nodes `lib, mod, css, js, page`
  * `options` - *required*, numeric value `0 or 1`, if 0 whole framework is of
2. **lib** - node with library name, that library will be always loaded in children nodes
  * `on` - *required*, numeric value `0 or 1` if 0 library is switched off
3. **mod** - node with module name, that module will be always loaded in children nodes
  * `on` - *required*, numeric value `0 or 1` if 0 module is switched off
  * `param` - module parameters as string separated by param_sep configuration value
  * `exec` - name of file in module directory to run if is different than default
  * `block` - name of block to load module, `order == position on tree`
4. **css** - node with css name, that css will be always loaded in children nodes
  * `media` - css file media type
  * `external` - numeric value `0 or 1` if set to `1` then in node content write url of css to load
5. **js** - node with js name, that js will be always loaded in children nodes
  * `external` - numeric value `0 or 1` if set to `1` then in node content write url of js to load
6. **page** - list of first level pages, node defined first nested page like: `my-site.pl/`, `my-site.pl/page`, contains nodes `menu, sub, lib, mod, css, js`
  * `id` - *required*, page id and url page name
  * `layout` - *required*, name of page main html template `if 0 dont load`
  * `external` - name of external tree xml file `like admin.xml`
  * `name` - *required*, page name, for html page title in navigation and site map *(wont be used as page title)*
  * `options` - *required*, page options `on, on tree, on menu, on breadcrumbs`
  * `redirect` - id or url of page to be redirected
  * `startDate` - unix timestamp with data from page will be available
  * `endDate` - unix timestamp with date until page will be unavailable
  * `changefreq` - frequency of page changes `use in sitemap`
  * `priority` - page priority `use in sitemap`
7. **sub** - list of second and other level pages, node define nested page like: `my-site.pl/page/subpage`, `my-site.pl/page/subpage/another`, contains nodes `menu, sub, lib, mod, css, js`
  * `id` - *required*, page id and url page name
  * `layout` - *required*, name of page main html template `if 0 don't load`
  * `external` - name of external tree xml file `like admin.xml`
  * `name` - *required*, page name, for html page title in navigation and site map *(wont be used as page title)*
  * `options` - *required*, page options `on, on tree, on menu, on breadcrumbs, inheritance`
  * `redirect` - id or url of page to be redirected
  * `startDate` - unix timestamp with data from page will be available
  * `endDate` - unix timestamp with date until page will be unavailable
  * `changefreq` - frequency of page changes `use in sitemap`
  * `priority` - page priority `use in sitemap`
8. **menu** - list of menu ids witch page/subpage will be linked `main sets main site menu`

### DTD Structure

```
<!ELEMENT root (lib*, mod*, css*, js*, page*)>
    <!ATTLIST root options (1|0) #REQUIRED>
    <!ELEMENT lib (#PCDATA)>
        <!ATTLIST lib on (1|0) #REQUIRED>

    <!ELEMENT mod (#PCDATA)>
        <!ATTLIST mod on (1|0) #REQUIRED>
        <!ATTLIST mod param CDATA #IMPLIED>
        <!ATTLIST mod exec CDATA #IMPLIED>
        <!ATTLIST mod block CDATA #IMPLIED>

    <!ELEMENT css (#PCDATA)>
        <!ATTLIST css media CDATA #IMPLIED>
        <!ATTLIST css external (0|1) #IMPLIED>

    <!ELEMENT js (#PCDATA)>
        <!ATTLIST js external (0|1) #IMPLIED>

    <!ELEMENT page (menu*, sub*, lib*, mod*, css*, js*)>
        <!ATTLIST page id ID #REQUIRED>
        <!ATTLIST page layout CDATA #REQUIRED>
        <!ATTLIST page external CDATA #IMPLIED>
        <!ATTLIST page name CDATA #REQUIRED>
        <!ATTLIST page options CDATA #REQUIRED>
        <!ATTLIST page redirect CDATA #IMPLIED>
        <!ATTLIST page startDate CDATA #IMPLIED>
        <!ATTLIST page endDate CDATA #IMPLIED>
        <!ATTLIST page changefreq CDATA #IMPLIED>
        <!ATTLIST page priority CDATA #IMPLIED>
        <!--page priority-->

    <!ELEMENT sub (menu*, sub*, lib*, mod*, css*, js*)>
        <!ATTLIST sub id CDATA #REQUIRED>
        <!ATTLIST sub layout CDATA #REQUIRED>
        <!ATTLIST sub name CDATA #REQUIRED>
        <!ATTLIST sub external CDATA #IMPLIED>
        <!ATTLIST sub options CDATA #REQUIRED>
        <!ATTLIST sub redirect CDATA #IMPLIED>
        <!ATTLIST sub startDate CDATA #IMPLIED>
        <!ATTLIST sub endDate CDATA #IMPLIED>
        <!ATTLIST sub changefreq CDATA #IMPLIED>
        <!ATTLIST sub priority CDATA #IMPLIED>

    <!ELEMENT menu (#PCDATA)>
```

Page and subpage idea
--------------
Pages and subpages create site tree defined in tree.xml file. Each page is independent of
other page, but has common modules, libraries, css and js defined on the top of root node.
Each page can have their own modules, libraries etc. and unlimited subpages as their children's.
Each children ins an part of page, and parent subpage if its have it and inherit all
libraries, modules and etc. nodes defined in parent node (inheritance can be disabled).  
In that way we can create structure of dependent nodes like: `my-site.pl/articles/article/id,123`
Article is an part of articles and inherit from articles some nodes. In that way we
can create more advanced dependencies `my-site.pl/products/software/open-source/id,123` or
`my-site.pl/admin/configuration/users/permissions`

Example of page dependency:  
```php
idex
page
  |--subpage
  |--subpage2
  |     |--sub-subpage
  |     |--some-other-subpage
  |--subpage3
  |     |--sub3
  |          |--sub-sub3
  |--last-one
page2
  |--subpage
```

Order of the nodes
--------------
All nodes must be written in proper order. That order written in tree DTD.  
In root node we decelerate nodes in that order:

1. libraries
2. modules
3. css files
4. java script files
5. pages definitions

Similar order we must keep in page and subpage structure. Bau then we have an new node `menu`.

1. menu definitions
2. subpages
3. libraries
4. modules
5. css files
6. java script files

Create base structure
--------------
### Create main page
To create simply start page we must use construction like bellow:

```xml
<!DOCTYPE root SYSTEM 'dtd/tree.dtd'>
<root options="1">
    <page id="index" name="Main test page" layout="index" options="1111">
        <lib on="1">library</lib>
        <mod on="1">module</mod>
        <css external="0">css</css>
        <js>js</js>
    </page>
</root>
```

In that code we create in root node named page. Give im `id=index` 
to inform framework to run it as site main page. In that example wi also add one
library, module, js and css. This page can be run as `my-site.pl` or `my-site.pl/index`

### Create other pages
To create another first level page, we put another node named page.

```xml
<!DOCTYPE root SYSTEM 'dtd/tree.dtd'>
<root options="1">
    <page id="index" name="Main test page" layout="index" options="1111">
        <lib on="1">library</lib>
        <mod on="1">module</mod>
        <css external="0">css</css>
        <js>js</js>
    </page>
    
    <page id="another" name="Main test page 2" layout="index" options="1111">
        <css external="0">css</css>
        <js>js</js>
    </page>
</root>
```

This example use the same layout, but no library and module. Access to that page
 we can get by putting in url `my-site.pl/another`

### Create sub pages
To create pages tree we must create subpages, that will be part of some page. All
subpages will inherit styles, scripts, modules and libraries of main page, or
main subpage. We can create an unlimited nested subpages with working inheritance.
In assumption we build an tree of related pages, that have some common nodes.

```xml
<page id="another" name="Main test page 2" layout="index" options="1111">
    <sub id="subpage" name="Test subpage" layout="subpage_layout" options="11111">
        <lib on="1">subpage_library</lib>
        <mod on="1">subpage_module</mod>
        <css>sub_css</css>
        <js>sub_js</js>
    </sub>

    <lib on="1">library</lib>
    <mod on="1">module</mod>
    <css external="0">css</css>
    <js>js</js>
</page>
```

This example show simply subpage that we can access by `my-site.pl/another/subpage`
 url address. Example of many subpages look like that:

```xml
<page id="another" name="Main test page 2" layout="index" options="1111">
    <sub id="subpage" name="Test subpage" layout="subpage_layout" options="11111">
        <sub id="sub_subpage" name="Another subpage 2" layout="sub_subpage_layout" options="11111">
            <css>sub_css</css>
            <js>sub_js</js>
        </sub>

        <lib on="1">subpage_library</lib>
        <mod on="1">subpage_module</mod>
        <css>sub_css</css>
        <js>sub_js</js>
    </sub>

    <sub id="subpage2" name="Test subpage 2" layout="subpage_layout" options="11111">
        <css>sub_css</css>
        <js>sub_js</js>
    </sub>

    <lib on="1">library</lib>
    <mod on="1">module</mod>
    <css external="0">css</css>
    <js>js</js>
</page>
```

Access to subpages in above example: `my-site.pl/another/subpage/sub_subpage`, `my-site.pl/another/subpage2`.

### Page and subpage options
Pages and subpages has some several options, that was basically described in DTD nodes description.

1. **id** - most important, required and unique only for page node value. Define page name in url
 that route can find correct page/subpage. Subpges can have non unique ids, bu be careful
 with usage the same values of id, because you can get the page you don't want to.  
 Route build form id for mode rewrite: `my-site.pl/page/subpage1/subpage2`  
 Route build form id for classic url: `my-site.pl?p0=page&p1=subpage1&p2=subpage2`
2. **layout** - main layout for page, always required, define name of html file with
 template to load for current chosen page/subpage. Contain only file name without extension
 or path with filename. All templates for pages subpages are located in `BLUE_FRAMEWORK/elements/layouts`
 directory and all templates must have `html` extension.
3. **name** - this is a default page title, always required, used to create bread crumbs
and site maps. To create that elements we don't use meta page title, because it can
be modified by modules.
4. **options** - page options, always required, described as four binary values `0 or 1`. Subpage has five of it. Contains the following information:
  * first index - if 0 page will be disabled, if 1 page work normally
  * second index - if 0 page will be disabled for sitemap creating, if 1 page work normally
  * third index - if 0 page will be disabled for menus, if 1 page work normally
  * fourth index - if 0 page will be disabled for breadcrumbs, if 1 page work normally
  * fifth index - `only for subpages, define inheritance` if 0 page will have switched off inheritance for it own and children, if 1 page work normally
5. **external** - name of other tree file, with other defined page/subpage structure.
 Give as file name, without extension, localed in `BLUE_FRAMEWORK/cfg` directory.
 That attribute can be used to very big structures (external tree is loaded only if page with it was called)
 or some pages that has other destiny, like admin panel. External tree replace main tree, so
 all common modules, libraries, scripts, js must be loaded again.
6. **redirect** - here give an page /subpage route, or some url that framework will redirect.
7. **startDate** - as unix timestamp, give the date fom page will be available
8. **endDate** - as unix timestamp, give the date up to page will be available
9. **changefreq** - attribute for creating sitemap, inform how often page will be checked by bot
10. **priority** - attribute for creating sitemap, inform how big priority page has instead to other pages

Load css and js scripts
--------------
### Load css file
To use cascade style sheets on page we must load it on. We can make it by two ways.
First is load in page tree and that will be described here, and second is load it in module.
To load css we must simply define special marker `<css>css_file_name</css>` that load
css file localized in `BLUE_FRAMEWORK/elements/css` directory. Of course we give file name without extension,
and we can give some path localized in css directory.  
Css node has some special attributes:

* **media** - describe type of css to load `print, screen etc.`
* **external** - numeric value `0 or 1` if set to `1`, if set to 1 then will load css form external source (as name give full url)

### Load js file
Using java script files in page structure is similar than css files. We can make in by the same ways
as css (in module and tree). We load js file be marker `<js>js_file_name</js>`
that will read js from `BLUE_FRAMEWORK/elements/js` directory. Remember to give
only file name, without extension.
JS nodes has only one special attribute:    
* **external** - numeric value `0 or 1` if set to `1`, if set to 1 then will load js form external source (as name give full url)  

### Load for common usage
If we wat to use `css, js, modules or libraries` for all pages we decelerate it in root node
 before pages definitions. If page has inheritance switched off, then all nodes
 from parent nodes, even from root node. All children pages also will have
 cleared inheritance.

```xml
<root options="1">
    <lib on="1">library</lib>  -\
    <mod on="1">module</mod>   --\___ nodes that will be always loaded
    <css>css</css>             --/
    <js>js</js>                -/

    <page id="index" name="Main Page" layout="index" options="1111">
    </page>
</root>
```

### Load for specific page or subpage
To use `css, js, modules or libraries` for specific page or subpage, just decelerate 
it for that page or subpage. But remember that all children subpages will have 
all decelerated nodes as we have in pages idea.
In pages and subpages we decelerate nodes after pages/subpages

```xml
<page id="index" name="Main Page" layout="index" options="1111">
    <sub id="index" name="Main Sub Page" layout="index" options="1111"></sub>

    <lib on="1">library</lib>  -\
    <mod on="1">module</mod>   --\___ nodes that will be always loaded for given page
    <css>css</css>             --/
    <js>js</js>                -/
</page>
```

Load libraries and modules
--------------
### Load library
Library in framework give some common for modules logic. Basically modules give some 
methods that can be used by modules to do something. Create node with library declaration 
`<lib on="1">library</lib>` will only load library file or files tht must be run 
in modules.  
We have two possibilities to load library, one by loading full package with all library files 
and second to load one single, or some library files.

* loading full package - just give package name `<lib on="1">valid</lib>`
* loading single library - is more complicated, because we give package name and library file separated by `/` `<lib on="1">db/mysql</lib>`
* loading some files from package - we just separate files by coma `,` `<lib on="1">db/mysql,mssql,fb</lib>`

Library node has only one attribute:

* **on** - `required` as integer value `1 or 0` that inform to load or not library

Libraries are grouped in packages localized in `/BLUE_FRAMEWORK/packages/` directory.  
Remember to give library file name without `_class` suffix and without extension.  
Framework is flexible in level that use many other scripts by library in framework.

### Load module
Modules are responsible for elementary system work. All we want to make by using 
that framework will be written in modules. Create node with module declaration 
`<mod on="1">module</mod>` will load module and lunch method `run` decelerated in
module class file.

Library node has some following options:

1. **on** - `required` the same as in library, allow to load and lunch module or not
2. **param** - module parameters as string separated by param_sep configuration value `param1::param2::param3`, parameters will be returned as array
3. **exec** - name of file in module directory to lunch module. Default module is lunched from php file with name of library, here we can give some other file
4. **block** - name of block in template (`{;block;some_block;}`) to load module, `order == position on tree`

Modules are localized in `/BLUE_FRAMEWORK/modules/` directory with their css, js and layouts  
Remember to give library file name without `_class` suffix and without extension.  

### Load for common usage
To load library and module (module will be lunched) for common usage, just put node
in root node, like css or js.
 
```xml
<root options="1">
    <lib on="1">library</lib>  -\
    <mod on="1">module</mod>   --\___ nodes that will be always loaded
    <css>css</css>             --/
    <js>js</js>                -/

    <page id="index" name="Main Page" layout="index" options="1111">
    </page>
</root>
```

### Load for specific page sub page

```xml
<page id="index" name="Main Page" layout="index" options="1111">
    <sub id="index" name="Main Sub Page" layout="index" options="1111"></sub>

    <lib on="1">library</lib>  -\
    <mod on="1">module</mod>   --\___ nodes that will be always loaded for given page
    <css>css</css>             --/
    <js>js</js>                -/
</page>
```

Load external trees
--------------
Framework allow to use some different routers declaration. Of course `tree.xml`
will be always loaded, but in main tree we can decelerate that some pages/subpages
load some other tree that will replace default tree. All libraries, modules, css
and js will be removed and loaded from new tree. External trees ale localed in
the same directory where default `BLUE_FRAMEWORK/cfg` and to load it we give
an **external** attribute with file name (without extension) to page or subpage node.

Inheritance of pages, css, js, libraries and modules
--------------
Structure of pages and subpages will default inherit all nodes that have parent node.
All children nodes will have css, js and libraries of parent element and also will
run modules of all parent elements. Of course we can skip that feature by setting
inheritance option to 0. Its anf fifth index of node options attribute
`options="11111" - on` `options="11110" - off`.  
Inheritance can be only switched off for subpages, main pages will always have nodes
decelerated in root element.

Usage translations for page name
--------------
Basically `name` attribute of page and subpage don't handle translations, but that
attributes is used only for building page map `tree_class::map()` and breadcrumbs
`tree_class::$breadcrumbs` so it will be used inside of modules. If we want to
display value of `name` attribute by module and we want translation to them, just
paste translation marker with the same construction used in module to display
translation from core `{;lang;core;translation_code;}`:

```xml
<page id="index" name="{;lang;core;main_page;}" layout="index" options="1111">
    <sub id="index" name="{;lang;core;main_sub_page;}" layout="index" options="1111"></sub>

    <lib on="1">library</lib>
    <mod on="1">module</mod>
    <css>css</css>
    <js>js</js>
</page>
```