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

### Page options

Load css and js scripts
--------------
### Load css file

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