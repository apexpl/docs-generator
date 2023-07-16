<?php
declare(strict_types = 1);

namespace Apex\Docs\Lib;

use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * I/O
 */
class Io
{


    /**
     * Create blank dir
     */
    public function createBlankDir(string $dir_name):void
    {

        // Remove dir, if exists
        if (is_dir($dir_name)) { 
            $this->removeDir($dir_name);
        }
        mkdir($dir_name, 0755, true);
    }

    /**
     * Parse directory
     */
    public function parseDir(string $rootdir, bool $return_dirs = false):array
    {
 
        list($search_dirs, $results) = [[''], []];

        // Go through directories
        while ($search_dirs) { 
            $dir = array_shift($search_dirs);

            // Add director, if needed
            if ($return_dirs === true && !empty($dir)) { 
                $results[] = $dir; 
            }

            // Open, and search directory
            if (!$handle = opendir("$rootdir/$dir")) { 
            throw new \Exception("Unable to open directory, $rootdir/$dir");
        }
            while (false !== ($file = readdir($handle))) { 
                if ($file == '.' || $file == '..') { continue; }

                // Parse file / directory
                if (is_dir("$rootdir/$dir/$file")) { 
                    if (empty($dir)) { $search_dirs[] = $file; }
                    else { $search_dirs[] = "$dir/$file"; }
                } else { 
                    if (empty($dir)) { $results[] = $file; }
                    else { $results[] = "$dir/$file"; }
                }
            }
            closedir($handle);
        }

        // Return
        return $results;
    }

    /**
     * Remove directory
     */
    public function removeDir(string $dirname):void
    { 
        // Check dir exists
        if (!is_dir($dirname)) {
            return;
        }

        // Go through, and delete all files
        $files = $this->parseDir($dirname, true);
        foreach ($files as $file) { 
            $this->removeFile("$dirname/$file", true);
    }

        // Remove directory
        if (is_link($dirname)) { 
            unlink($dirname);
        } elseif (is_dir($dirname)) { 
            rmdir($dirname);
        }
    }

    /**
     * Remove file
     */
    public function removeFile(string $file, bool $remove_empty_dir = false):void
    {

        // Check file exists
        if (!file_exists($file)) { 
            return;
        }

    if (is_dir($file)) { 
            $this->removeDir($file);
        } else { 
            unlink($file);
        }

        // Remove empty dir, fi needed
        if ($remove_empty_dir === true && count(scandir(dirname($file))) < 3) { 
            if (is_link(dirname($file))) { 
                unlink(dirname($file));
            } else { 
                rmdir(dirname($file));
            }
        }
    }

    /**
     8 Create zip archive
     */
    public function createZipArchive(string $source_dir, string $dest = ''):string
    {

        // Get temp file, if needed
        if ($dest == '') { 
            $dest = sys_get_temp_dir() . '/apex-' . uniqid() . '.zip';
        }

        // Get files from source
        $files = $this->parseDir($source_dir);

        // Create zip file
        $zip = new ZipArchive();
        if (!$zip->open($dest, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new \Exception("Unable to create zip archive at, $dest");
        }

        // Add all files
        foreach ($files as $file) { 
            $zip->addFile("$source_dir/$file", $file);
        }
        $zip->close();

        // Return
        return $dest;
    }

}

