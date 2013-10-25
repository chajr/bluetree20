Create page structure and routers
====================

Doctype definition
--------------
### DTD elements description

1. **root** - main element of tree, always required, contains nodes `lib, mod, css, js, page`
  * `options` - *required*, numeric value `0 or 1`, if 0 whole framework is of
2. **lib** - node with library name, that library will be always loaded in children elements
   * `on` - *required*, numeric value `0 or 1` if 0 library is switched off
3. **mod** - node with module name, that module will be always loaded in children elements
   * `on` - *required*, numeric value `0 or 1` if 0 module is switched off
   * `param` - module parameters as string separated by param_sep configuration value
   * `exec` - name of file in module directory to run if is different than default
   * `block` - name of block to load module, `order == position on tree`
4. **css** - node with css name, that css will be always loaded in children elements
   * `media` - css file media type
   * `external` - numeric value `0 or 1` if set to `1` then in node content write url of css to load
5. **js** - node with js name, that js will be always loaded in children elements
   * `external` - numeric value `0 or 1` if set to `1` then in node content write url of js to load
6. **page** - list of first level pages, node defined first nested page like: `my-site.pl/`, `my-site.pl/page`, contains nodes `menu, sub, lib, mod, css, js`
   * `id` - *required*, page id and url page name
   * `layout` - *required*, name of page main html template `if 0 dont load`
   * `external` - name of external tree xml file `like admin.xml`
   * `name` - *required*, page name, for html page title and navigation
   * `options` - *required*, page options `on, on tree, on menu, on breadcrumbs`
   * `redirect` - id or url of page to be redirected
   * `startDate` - unix timestamp with data from page will be available
   * `endDate` - unix timestamp with date until page will be unavailable
   * `changefreq` - frequency of page changes `use in sitemap`
   * `priority` - page priority `use in sitemap`
7. **sub** - list of second and other level pages, node define nested page like: `my-site.pl/page/subpage`, `my-site.pl/page/subpage/another`, contains nodes `menu, sub, lib, mod, css, js`
   * `id` - *required*, page id and url page name
   * `layout` - *required*, name of page main html template `if 0 dont load`
   * `external` - name of external tree xml file `like admin.xml`
   * `name` - *required*, page name, for html page title and navigation
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
 other page, but has common modules, libraries, css and js defined on the top of root element.
 Each page can have their own modules, libraries etc. and unlimited subpages as their children's.
 Each children ins an part of page, and parent subpage if its have it and inherit all
 libraries, modules and etc. elements defined in parent element (inheritance can be disabled).  
In that way we can create structure of dependent elements like: `my-site.pl/articles/article/id,123`
 Article is an part of articles and inherit from articles some elements. In that way we
 can create more advanced dependencies `my-site.pl/products/software/open-source/id,123` or
 `my-site.pl/admin/configuration/users/permissions`

Example of page dependency:  
| pages         | subpage level 1 | subpage level 2    | subpage level 3    |
| ------------- |:---------------:| ------------------:|-------------------:|
| index         |                 |                    |                    |
| page          | subpage         |                    |                    |
|               | subpage2        | sub-subpage        |                    |
|               |                 | some-other-subpage |                    |
|               | subpage3        | sub3               | sub-sub3           |
|               | last-one        |                    |                    |
| page2         |                 | some-other-subpage |                    |
|               | subpage         |                    |                    |

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

In that code we create in root element node named page. Give im `id=index` 
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
 In assumption we build an tree of related pages, that have some common elements.

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
Pages and subpages has some several options, that was basically described in DTD elements description.

1. **id** - most important, required and unique only for page node value. Define page name in url
 that route can find correct page/subpage. Subpges can have non unique ids, bu be careful
 with usage the same values of id, because you can get the page you don't want to.  
 Route build form id for mode rewrite: `my-site.pl/page/subpage1/subpage2`  
 Route build form id for classic url: `my-site.pl?p0=page&p1=subpage1&p2=subpage2`
2. **layout** - main layout for page, always required, define name of html file with
 template to load for current chosen page/subpage. Contain only file name without extension
 or path with filename. All templates for pages subpages are located in `BLUE_FRAMEWORK/elements/layouts`
 directory and all templates must have `html` extension.
3. **name** - this is a default page title, always required, used when page don't have meta definition.
 Value of that attribute wil be displayed as page title.
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
First is load in page tree and that will be described here, and second is load it in module...
To load css we must simply define special marker `<css>css_file_name</css>` that load
css file localized in `BLUE_FRAMEWORK/elements/css` directory. Of course we give file name without extension,
and we can give some path localized in css directory.  
Css element has some special attributes:

### Load js file

### Load for common usage

### Load for specific page sub page

Load libraries and modules
--------------
### Load library

### Load module

### Load for common usage

### Load for specific page sub page

Load external trees
--------------

Pages special options
--------------
changefreq="always" priority="0.8

Inheritance of pages, css, js, libraries and modules
--------------