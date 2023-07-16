
# DocsGenerator::generateDirectory 

### Usage

> void DocsGenerator::generateDirectory(string $source_dir, string $dest_dir, string $base_namespace, [ string $base_uri = '/docs/' ], [ string $theme = 'html' ])

### Description

> Generate documentation for all PHP classes within a directory.

### Parameters

Parameter | Required | Type | Description
------------- |------------- |------------- |------------- 
source_dir | Yes | string | Directory to generate documentation for.
dest_dir | Yes | string | Destination directory to save documentation files.
base_namespace | Yes | string | The namespace that corresponds to the source directory.
base_uri | No | string | The base URI corresponding to the destination directory, used to correctly link to other pages.
theme | No | string | Theme to use, sub-directory of /themes/ directory.  Supported values are -- html, markdown, syrus.

### Return
> void 
### See Also

* [extractClassName{}])extractclassname)
* [generateClass{}])generateclass)


