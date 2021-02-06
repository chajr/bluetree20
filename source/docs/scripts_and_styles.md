Scripts and Styles
====================
Most of pages that will be created in framework will required some Java Scripts
and CSS. Framework has separated files for that, one part is for main page structure
and that files are localized inside of `/BLUE_FRAMEWORK/elements/` and second
parts is for modules _(each module can have own css nad js)_. Framework build
special URL for js and css, and return merged content from all required css or js
files.

Basic usage
--------------
First thing that we must do to use js and css in our page is put in correct place
special markers that will be replaces by html nodes responsible for loading for
page js and css. For js is `{;core;js;}` and for css is `{;core;css;}`. Framework
will automatically replace that markers by html nodes when rendering the whole
page.  
To load css you must put that special marker in `<head></head>` section
for js it don't has big matter, but the best place is at the bottom of page
before page close markers `</body></html>`.

### Core usage
To load base js and css for page, you must define them in `tree.xml` page structure
file using for that special nodes. All that is described in
[page-structure](/docs/page-structure.md "Page structure") documentation in **Load css
and js scripts** section.

### Module usage
As was written before, we can define some css and js files for modules. Each module
can have unlimited css and js files and they loaded only for that module, but only
if module use special method `$this->set()` to load them. Thanks that we can use
different styles and scripts for module, depends of module work. Description of
usage module css and js are described in [module](/docs/module.md "Module")
documentation in **JS and CSS files** and **Method list** section.

Merging files
--------------
Because we can have many modules, with many js and css files that can make many
URL to that files. To avoid many request for page, framework automatically merge
that files in one file and return that file by single URL. To get that files
collection framework use special URL with names of modules and files to load.  
Each of that URL have two basic elements, first is language code, that allow to
use in that files translation markers _(because merging files run part of core)_
and second is type of merging `core_js` or `core_css`. After that two parameters
URL will have `key => value` pair where key is module name and value will be name
of file to merge.

Handling markers
--------------
Because to merge files is used framework _(exactly part of core_class)_ we can use
some markers for each module and core, as we use in module or core templates.

### Core markers
We can use all core markers that are described in [template](/docs/template.md "Template")
documentation in **Core markers used in main template** section, to get correct
path for some required in eg. css files images.

### Translation markers
To do this use the same translation marker construction as for templates `{;lang;code}`
that are described in [template](/docs/template.md "Template")
documentation in **Translation markers** section and also in
[module](/docs/module.md "Module") in **Internationalization** section.  

Scripts and styles from external source
--------------
There is also possibility to load some js or css from external servers, like if
we want jQuery library it can be load from google page.  
To use that feature we use special attribute `external="1"` when we create node
in `tree.xml` for loading js or css and as name of file give path to server.
We can also load js or css from external source inside of module. Just in
`$this->set()` method give full URL to file, and as third parameter give `external`.
`$this->set('url', 'js', 'external');