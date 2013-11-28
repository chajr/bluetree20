URL conventions
====================
Framework is build to work with two types of URL. Firs is default URL construction
named _classic URL_ and second use Apache mode rewrite and is called _rewrite_ or
_mode rewrite_. Framework has also some special markers, that can automatically
convert given string with parameters to one of URL types.  
URL is always split to some specific sections. Base is domain address, second
if framework is localed in some specific folder inside domain is called `test` and
must be defined in configuration. Next important thing is language code, that will
be always in URL if language handling is turn on. Each page _(and even js or css)_
are part of some language. After that we have page structure _(page and subpages)_
and at the end optionally parameters.

Mode rewrite
--------------
Like was described before, URL is slit to some parts. In mode rewrite switch on
URL will be like that:

```
http://my-site.pl/testdir/pl-PL/main_page/sub1/sub2/param1,123/param2,567/param3,value
\____/\_________/\______/\____/\________/\___/\___/\_________/\_________/\___________/
  1        2         3      4       5      6    7       8          9          10
```

1. **protocol**
2. **domain**
3. **test** - some optional directory where framework can be localized
4. **language** - language code, always required if language support is on _(will redirect automatically if code is missing to default language)_
5. **page** - main page defined in root node in `tree.xml`
6. **subpage** - child of `main_page`
7. **subpage** - child of `sub1` page
8. **parameter** - contains key and value separated by comma `,`
9. **parameter** - contains key and value separated by comma `,`
10. **parameter** - contains key and value separated by comma `,`

All strings that can be used for URL is defined by regular expressions in `config.xml`
in `reg_exp_rewrite` option.

Classic URL
--------------
In classic URL tribe, position of elements _(exclude test directory)_ don't have
any matter, but very important are key names for values.

```
http://my-site.pl/testdir?core_lang=pl-PL&p0=main_page&p1=sub1&p2=sub2&param1=123&param2=567&param3=value
\____/\_________/\______/\______________/\___________/\______/\______/\_________/\_________/\___________/
  1        2         3          4              5         6       7         8          9          10
```

1. **protocol**
2. **domain**
3. **test** - some optional directory where framework can be localized
4. **core_lang** - language code, always required if language support is on _(will redirect automatically if code is missing to default language)_
5. **p0** - main page defined in root node in `tree.xml`, main page will always be in p0 variable
6. **p1** - child of `main_page`
7. **p2** - child of `sub1` page
8. **param1** - contains key and value
9. **param2** - contains key and value
10. **param3** - contains key and value

**For pages `p` is very important to give correct numeric value, because by that
value framework will search page nesting.**

All strings that can be used for URL is defined by regular expressions in `config.xml`
in `reg_exp_classic` option.

JS and CSS
--------------
To get js and css content we use special URL, where we have type, module name and
name of file to load, some description can be found in
[scripts_and_styles](/docs/scripts_and_styles.md "Scripts and Styles")
documentation.

Example of that URL for mode rewrite and classic URL:

```
http://my-site.pl/en-GB/core_css/core,css1/core,elusive-webfont/core,elusive-webfont-ie7/core,css2/core,css3/modul1,base2/mod2,form/
```

```
http://my-site.pl/?core_lang=en-GB&p0=core_css&core=css1&core=elusive-webfont&core=elusive-webfont-ie7&core=css2&core=css3&modul1=base2&mod2=for
```

In classic URL as `p0` parameter give type that can be `core_css` or `core_js`
and construction like`module_name=file` to merge correct files.

Access to some other files
--------------
To access some other files, like images, documents etc. just use normal URL for
that. Framework `.htaccess` allow all URL that will be end with file extension,
so you can access to all files that are localed inside of page.  
Only restricted directory is `BLUE_FRAMEWORK` that is secured by `deny from all`
instruction.

Markers for URL
--------------
Framework has some special markers build in `display_class` to convert them for
correct URL depends of chosen framework configuration `rewrite`. Thanks that we 
can easily change page URL, because we give only list of pages and parameters into
marker, and framework will automatically convert it.  
Whole description of usage that markers can be found in
[template](/docs/template.md "Layout and templates") in URL markers section.
