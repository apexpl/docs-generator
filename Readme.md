
# Apex Docs Generator

Automatically generates static developer documentation for either, a single PHP class or all classes within a project directory.  Theme based allowing results to be easily styled any way you wish, and comes with support for basic HTML, markdown and Syrus formats.

Some example of generated documentation:

Class: [https://apexpl.io/docs/classes/svc/convert/](https://apexpl.io/docs/classes/svc/convert/)

Method: [https://apexpl.io/docs/classes/svc/convert/case](https://apexpl.io/docs/classes/svc/convert/case)

## Installation

Install via Composer with:

> `composer require apex/docs-generator`

## Table of Contents

You will only ever need two methods within the the `Apex\Docs\DocsGenerator` class:

* [generateClass()](https://github.com/apexpl/docs-generator/blob/master/docs/generateclass.md)
* [generateDirectory()](https://github.com/apexpl/docs-generator/blob/master/docs/generatedirectory.md)


## Usage

**Generate Docs for Single Class**

~~~php
use Apex\Docs\docsGenerator;

/ Set some variables
$class_name = "App\\MyPackage\\Controllers\\OrderController";
$dest_dir = "/path/to/docs/order_controller";
$base_uri = "/docs/";

// Generate single class
$generator = new DocsGenerator();
$generator->generateClass($class_name, $dest_dir, $base_uri, 'html');
~~~

The last parameter in the `generateClass()` function is the theme to use.  All themes can be found within /themes/ of the installation directory, and by default supports three themes - *html, markdown, syrus*

The above will create a blank directory at /path/to/docs/order_controller, an index.html file inside that lists all methods within the class, each of which link to another method specific page.

** Generate Documentation for Directory of PHP Classes**

~~~php
use Apex\Docs\docsGenerator;

/ Set some variables
$source_dir = "/path/to/my_package";
$dest_dir = "/path/to/docs/";
$base_uri = "/docs/";
$base_namespace = "App\\MyPackage\\";

// Generate directory
$generator = new DocsGenerator();
$generator->generateDirectory($source_dir, $dest_dir, $base_uri, $base_namespace, 'html');
~~~

This will go through all files and directories within `$source_dir`, and generate a new documentation directory for every PHP class found.  I will also generate the necessary index pages within each root nameaspace that contains PHP casses, giving an overview of all classes within that namespace.


## Syrus Integration

There is build-in support for [Syrus](https://github.com/apexpl/syrus/) if you so desire.  If you wish to use this integration and are using Syrus outside of Apex, to enable the integration open the /config/container.php Syrus file and look for the item:

> `syrus.tag_namespaces`

Within this array, add the entry:

> `Apex\\Docs\\SyrusTags`

That's it.  When generating documentation simply change the `$theme` paramter from "html" to "syrus", and it wll generate the documenation formatted for Syrus.


## Apex

Brought to you by Apex Framework at [https://apexpl.io/](https://apexpl.io/).

