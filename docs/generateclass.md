
# DocsGenerator::generateClass 

### Usage

> void DocsGenerator::generateClass(string $class_name, string $dest_dir, [ string $base_uri = '/docs/' ], [ string $theme = 'html' ])

### Description

> Generate documentation for a single class and all its methods.

### Parameters

Parameter | Required | Type | Description
------------- |------------- |------------- |------------- 

class_name | Yes | string | Fully qualified class name to generate docs for.
dest_dir | Yes | string | Destination directory to save documenation files.
base_uri | No | string | The base URI corresponding to the destination directory, used to correctly link to other pages.
theme | No | string | Theme to use, sub-directory of /themes/ directory.  Supported values are -- html, markdown, syrus.

### Return
> void 
### See Also

* [extractClassName{}])extractclassname)
* [generateDirectory{}])generatedirectory)


