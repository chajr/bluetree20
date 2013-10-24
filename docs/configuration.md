How configure framework and modules
====================

Configuration of framework
--------------
Main configuration file is located in: `/BLUE_FRAMEWORK/cfg/config.xml`. This file 
also use `/BLUE_FRAMEWORK/cfg/dtd/config.dtd` to define configuration id

### Domain section
In this part of configuration we set the following information's:

1. **domain** - main site address without protocol `my-site.pl` as regular expression eg. #(www.)?my-site\.pl#
2. **debug** - as numeric value `1 or 0` inform to show full error information or not
3. **core_mail** - administrator e-mail address
4. **error404**- as numeric value `1 or 0` inform to show full 404 error page or not, if page wasn't found will redirect to 404 page
5. **meta** - as numeric value `1 or 0` inform to use `meta_class` to handle html meta elements
6. **techbreak** - as numeric value `1 or 0`, if 1 stops framework and display technical break page
7. **subdomain** - if different on 0, then use subdomain as main page, value number is page a nesting level

```xml
    <option id="domain" value=""/>  
    <option id="debug" value=""/>  
    <option id="core_mail" value=""/>  
    <option id="error404" value=""/>  
    <option id="meta" value=""/>  
    <option id="techbreak" value=""/>  
    <option id="subdomain" value=""/>  
```

### Incoming data section (GET, POST etc.)
In this part of configuration we set the following information's:

1. **reg_exp_rewrite** - regular expression to check that url is ok when mode rewrite is enabled, default: `#^[\w-\\,\/]*$#`
2. **reg_exp_classic** - regular expression to check that url is ok when mode rewrite is disabled, default: `#^[\w\.\/?=&amp;-]*$#`
3. **global_var_check** - regular expression to check all variable key names given in post,files etc, default: `#^[a-zA-Z][\w]*$#`
4. **max_get** - max number of URL parameters `0 -no limit`, include pages and subpages
5. **max_post** - max number of POST parameters `0 -no limit`
6. **get_len** - max length of GET parameter, in rewrite mode include name + comma + variable `0 -no limit`
7. **test** - place where is index file localed on domain, use when page is localed in domain dir `is empty -none` when url looks like:. `my-site.pl/framework`
8. **post_secure** - -if different on 0 convert POST values on entities or quotes `0 -none, 1 -quotes, 2 -entities`
9. **file_max_size** - set one uploaded file max size in kb, limit mut be set up `0 - always throw error when upload file`
10. **files_max_size** - set all uploaded files max size in kb, limit mut be set up and bigger than file_max_size `0 - always throw error when upload files`
11. **files_max** - set max number of uploaded files
12. **cookielifetime** - cookie lifetime in seconds `3600 == 1h`

```xml
    <option id="reg_exp_rewrite" value=""/>  
    <option id="reg_exp_classic" value=""/>  
    <option id="global_var_check" value=""/>  
    <option id="max_get" value=""/>  
    <option id="max_post" value=""/>  
    <option id="get_len" value=""/>  
    <option id="test" value=""/>  
    <option id="post_secure" value=""/>  
    <option id="file_max_size" value=""/>  
    <option id="files_max_size" value=""/>  
    <option id="files_max" value=""/>  
    <option id="cookielifetime" value=""/>  
```

### Module/library section
In this part of configuration we set the following information's:

1. **mod_check** - as numeric value `1 or 0` check that module is defined in tree.xml or not
2. **param_sep** - parameter separator for modules, libraries, css and js, parameters will be read as array
3. **rewrite** - as numeric value `1 or 0` inform to use mode rewrite or not
4. **var_rewrite_sep** - if use mode rewrite determinate separator for name and value `/name,value/name2,value`, default: `,`

```xml
    <option id="mod_check" value=""/>  
    <option id="param_sep" value=""/>  
    <option id="rewrite" value=""/>  
    <option id="var_rewrite_sep" value=""/>  
```

### Error section
In this part of configuration we set the following information's:

1. **unthrow** - as numeric value `1 or 0`, if 1 don't catch errors from libraries/modules when throw was occurred
2. **log_all_errors** - set log in log directory on modules errors `0 -none, 1 -all, 2 -only stop errors`
3. **core_procedural_mod_check** - as numeric value `1 or 0` check that module is procedural written or not
4. **errors_log** -  as binary `111` witch errors will be saved on log file `{1} -errors, {2} -warnings, {3} -errors throw from pointer`

```xml
    <option id="unthrow" value=""/>  
    <option id="log_all_errors" value=""/>  
    <option id="core_procedural_mod_check" value=""/>  
    <option id="errors_log" value=""/>  
```

### Language section
In this part of configuration we set the following information's:

1. **one_lang** - as numeric value `1 or 0`, inform to use only one language or support multi language
2. **lang** - default language code `eg. en-GB`, use when other language was not specified or don't detected
3. **lang_support** - as numeric value `1 or 0` check that language support is on or off `when off don't translate language markers`
4. **detect_lang** -  as numeric value `1 or 0` if language wasn't specified, will automatically detect language, or use default language

```xml
    <option id="one_lang" value=""/>  
    <option id="lang" value=""/>  
    <option id="lang_support" value=""/>  
    <option id="detect_lang" value=""/>  
```

####List of languages that are switched on:

**lang_on** - main block for languages, value must be set on 1  
bellow example of switched on languages, is `on="0"` language will be switched off

```xml
    <langs id="lang_on" value="1">  
        <lang id="pl-PL" on="1"/>  
        <lang id="en-GB" on="1"/>  
        <lang id="en-US" on="1"/>  
    </langs>  
```

### Other section
In this part of configuration we set the following information's:

1. **compress** - as numeric value `1 or 0`, inform to compress site content or not `0-none, 9-max`
2. **timezone** - sets default timezone `eg. Europe/Berlin`

```xml
    <option id="compress" value="0"/>
    <option id="timezone" value="Europe/Berlin"/>
```

Configuration of module
--------------
We can configure module for specific usage by two ways. One is base configuration
 stored in module directory and second allows configure module depends on page structure.
 Last one will be used only when module is lunched from specified router.
 
### Main module configuration
We create in `/BLUE_FRAMEWORK/modules/module_name/elements/` directory
 **config.xml** file and in `/BLUE_FRAMEWORK/modules/module_name/elements/dtd/`
  **config.dtd** doctype definition `the same as in cfg/dtd for main configuration`  
In `congig.xml` we must create the same structure as in main configuration file

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE root SYSTEM "dtd/config.dtd">
<root>
    <option id="config_option_1" value="value_1"/>
    <option id="config_option_2" value="value_2"/>
    <option id="config_option_3" value="value_3"/>
</root>
```

### Configuration for specific route
In `tree.xml` or other tree file in section `mod` we define special parameter `param`
 with values as string, separated by defined in `<option id="param_sep" value=""/>`
 separator.

```xml
<mod on="1" param="param_1::param_2::param_3">module_name</mod>
```

Parameters will be exploded to array as `parameter id => parameter value`..
As some configuration can be used lunching module from different file, what is
 described in **tree.md** and **module.md** documentation
 
Usage of configuration in module
--------------
### Main module configuration
To get configuration for module we can use `$this->loadModuleOptions()` method
 inside of module file that is extend **module_class**, or use `option_class` to
 load module `option_class::load('module_name');`(in that way we can load
 configuration for other module).  
Both of methods will return array of `configuration id => configuration value`.

We can also use `option_class::show('module_name');` method to get single option
 value for specified module.

### Router configuration
Parameters from router configuration are stored in `$this->params` as array of
 `configuration id => configuration value`.
 
If we use module that doesn't extend **module_class** we can ged that array as 
second parameter given in `__construct` method.

### System configuration
To get system configuration we use `core_class::options('option_name');` to get 
specific option value or `core_class::options();` to get all core options.

Configuration of library
--------------
Basically we can't configure modules on the way that we can make it with modules.  
And is strongly recommended to don't do it, because library are common for all modules,
 and they can be lunched with specific configuration (if they has some) by module.
