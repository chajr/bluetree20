Meta tags usage
====================
Blue Framework has some special library to handle html meta tags nodes.  
Use of meta tags we can make on three ways, hardcode in template, build meta tree
and define in module.  

Basic information
--------------
The easiest way to create meta tag is write it in template that will be used by some page.
But on that way we don't have any control on it, exclude translations.  
All other methods require to add special marker `{;core;meta;}` that will be replaced by
meta tags build by `meta_class` library.

Create tree of meta tags
--------------
Basically to use meta tags we use special xml `/BLUE_FRAMEWORK/cfg/meta.xml` where we define
meta tags for all pages, or just default (required) used for all pages without their own
definitions of meta tags. Important is we can create meta tags only for pages
because subpages are children of page and in assumption their an part of page.

# Doctype definition

### DTD nodes description

1. **root** - main node of m,eta tags, always required, contains nodes `default, page`
2. **default** - node for default mata tags, _always required_ written when page don't have own values or have update value, contains nodes `title, meta`
  * `val` - *required*, information to witch page 1tree.xml1 will appeal current value
3. **page** - node for other pages mata tags, _always required_ written when page don't have own values or have update value, contains nodes `title, meta`
  * `val` - *required*, information to witch page in `tree.xml` will appeal current value
4. **meta** - inside of `default or page` node create single meta tag
  * `content` - *required*, some content of meta element (works the same as in HTML)
  * `update` - numeric value `0 or 1` if 1 will update value of the same node in `default` _only for page, nou used in default_
  * `name` - works the same as in HTML
  * `http-equiv` - works the same as in HTML
4. **title** - inside of `default or page` node create node with page title
  * `update` - *required*, numeric value `0 or 1` if set to 1 will update main title with given content, otherwise will display defined title
  * `title` - *required*, page title

### DTD structure

```
<!ELEMENT root (default, page*)>
    <!ELEMENT default (title, meta*)>
        <!ATTLIST default val ID #REQUIRED>

    <!ELEMENT page (title, meta*)>
        <!ATTLIST page val ID #REQUIRED>

    <!ELEMENT meta (#PCDATA)>
        <!ATTLIST meta content CDATA #REQUIRED>
        <!ATTLIST meta update (0|1) #IMPLIED>
        <!ATTLIST meta name CDATA #IMPLIED>
        <!ATTLIST meta http-equiv CDATA #IMPLIED>

    <!ELEMENT title (#PCDATA)>
        <!ATTLIST title update (0|1) #REQUIRED>
        <!ATTLIST title title CDATA #REQUIRED>
```

### Build meta tag tree
In `/BLUE_FRAMEWORK/cfg/meta.xml` create `<default>` node, that is always required
with required children `title`. That allow you to set title for all pages and subpages.  
Code will look like that:

```xml
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root SYSTEM "dtd/meta.dtd">
<root>
    <default val="index">
        <title title="Some page title" update="1"/>
    </default>
</root>
```

In tah example value of `<default val="index">` val attribute don't have big matter.
Its usable only when we create different pages meta structure. With set on `index`
script will work on couple microseconds faster on main page :)

Non required, but helpful meta tags paste bellow `title` node, order is important.

```xml
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root SYSTEM "dtd/meta.dtd">
<root>
    <default val="index">
        <title title="Some page title" update="1"/>
        <meta name="robots" content="index,follow,all"/>
        <meta name="keywords" content="blue framework, test, meta example"/>
        <meta name="description" content="That is page for testing meta tags"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    </default>
</root>
```

In that example we have created title, and meta for robots, page description and
default encoding.

For other pages we define some new meta tags, or update default, so we can have
differed title and some other meta information.

```xml
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root SYSTEM "dtd/meta.dtd">
<root>
    <default val="index">
        <title title="Some page title" update="1"/>
        <meta name="robots" content="index,follow,all"/>
        <meta name="keywords" content="blue framework, test, meta example"/>
        <meta name="description" content="That is page for testing meta tags"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    </default>
    <page val="other_page">
            <title title=" - other" update="1"/>
            <meta name="robots" content="index,follow"/>
            <meta name="keywords" content=", other example, other page" update="1"/>
            <meta name="description" content=" on other page" update="1"/>
    </page>
</root>
```

That example will join some data, and some will be replaced. After entering on
`my-site.pl/other_page` we will get page with title `Some page title - other`
, robots will be replaced by `index,follow` and keywords and description will
be updated to `blue framework, test, meta example, other example, other page`
and `That is page for testing meta tags on other page`.

### Translations
Instead of each node text values to display, we can use translation markers. That
allow us to display different content depends of chosen page language. All translations
will be loaded from translations in`/BLUE_FRAMEWORK/cfg/lang/`.

Some translations for `en-GB`:

```php
$content = array(
    'page_title'          => 'english title',
    'meta_keys'           => 'english, meta example, english keys',
    'page_description'    => 'English description for page',
);
```

```xml
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root SYSTEM "dtd/meta.dtd">
<root>
    <default val="index">
        <title title="{;lang;page_title;}" update="1"/>
        <meta name="robots" content="index,follow,all"/>
        <meta name="keywords" content="{;lang;meta_keys;}"/>
        <meta name="description" content="{;lang;page_description;}"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    </default>
</root>
```

That will display page with defined in translation file information.

Define meta tags in module
--------------
Last way to set some meta information is usage some special methods in module.  
We have two of them, on is create whole meta node and second is responsible for
update meta node information defined in `content` attribute.

### Create meta tag node
To do this use `$this->addMetaTag` method witch has only on parameter, that is
full meta tag element.

```php
$this->addMetaTag('<meta name="some_meta" content="some content"/>');
```

### Update existing meta tag node
To do this use `$this->addToMetaTag` method witch has two parameters. First is node
name that is defined in `name` attribute, and second is value to add.

```php
$this->addToMetaTag('keywords', ', module keyword');
```
