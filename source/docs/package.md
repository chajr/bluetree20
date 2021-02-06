Create and usage of packages and libraries
====================

What is package
--------------
Packages are some special group for libraries that create a coherent whole. Packages
can be used as whole, or just single library. And finally library is class that
give some common useful methods, or objects. In packages we can easily create
data models, abstract classes, interfaces, helper class etc. that can be used
multiple times by modules. There is only one special package named `CORE` that
give framework libraries.  
Basically library don't has any interaction with page, that is done by module. Library
has only some common methods that can be used by modules.

Package create
--------------
All libraries are grouped in package and localized in `BLUE_FRAMEWORK/packages/package_name`.
Inside `package_name` directory will be all libraries. Each library is just an
php class, so inside library file just create some class and library is done.  
Only restriction is file name and class name that must end by `_class` suffix.
Like `some_library_class.php` and inside:

```php
class some_library_class
{
    //class content
}
```

### Abstract and interfaces
Sometimes we want to use some abstract classes and interfaces. That classes must
have different suffix to load it before main classes, `_interface` fo interfaces
and analogical `_abstract` for abstract classes. That allow `starter_class` to
load that files before main classes.

Loading packages and single libraries
--------------
All packages and libraries that we want to use are decelerated in `tree.xml` the
same way as modules, for all pages in `root` node, for specific page/subpage in
their node. But as names we can give whole package, or some specific libraries.
To load whole package use `<lib on="1">package_name</lib>` (on="1" means that
package is on).  
To load one or more specific libraries use `<lib on="1">package_name/library</lib>`
for load only on `library` form `package_name` or to load many libraries, give their
names separated by coma `<lib on="1">package_name/library,lib2,lib3</lib>`.

### Library order for extending
All libraries for given package are loaded in alphabetical order, but only if we
load them by give only package name. That information is very important if you want
to use `extend`. Extended class must be loaded before class that use it.  
When we load single libraries form package, order will be the same as we give in
method parameter, so that is also important for extending class (remember to put
interfaces before abstract and abstract before class when loading single library).

Package usage
--------------
All libraries we can use as normal classes by:

```php
$library = new some_library_class();
```

or if they have static methods by:

```php
$value = some_library_class::method();
```

Load library manually
--------------
There is possibility to load library without usage `tree.xml`, directly inside
module. To do this use `starter_class::package('name');`, where as parameter give
package or package/module name as the same way as in `tree.xml`. But in that way
modules lunched before wont have access to that library, and library wont be
added to list, so checking required library fro library loaded on that way will
cause an error.