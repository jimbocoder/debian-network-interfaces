<?php
namespace jimbocoder\DebianNetworkInterfaces;

/**
 * There isn't a ton of "parsing" done here, despite the name.  The main reason this class exists
 * is because I couldn't find anything that would handle the relatively new 'source' and 'source-directory'
 * options in /etc/network/interfaces.
 *
 * So this interprets those, and returns a version of the input buffer where the sourced files are interpolated
 * into the main file.
 */
class Parser
{
    // From `man 5 interfaces`:
    //      Lines starting with `#' are ignored.
    //      Note that end-of-line comments are NOT supported, comments must be on a line of their own.
    // Actually makes the parsing a little simpler:
    protected static $_sourcePattern = '/^\s*source\s+(.+)$/';
    protected static $_sourceDirPattern = '/^\s*source\-directory\s+(.+)$/';

    /*
     * The only public function.  $input -> $output, no side effects, you know the drill.
     */
    public static function parse($input)
    {
        $lines = explode("\n", $input);
    unset($input); // Don't need this anymore, might as well.

        $buf = '';
    foreach($lines as $line) {
        if ( preg_match(static::$_sourcePattern, $line, $match) ) {
            // source lines
                $buf .= static::_handleSourceGlob($match[1]) . "\n";
        } else if ( preg_match(static::$_sourceDirPattern, $line, $match) ) {
            // source-directory lines
                $buf .= static::_handleSourceDirectoryGlob($match[1]) . "\n";
        } else {
            // everything else
                $buf .= $line . "\n";
            }
        }

        return $buf;
    }

    /**
     * Parse each file in a glob
     * @return string The concatenated parsed matched files.
     */
    protected static function _handleSourceGlob($pat)
    {
        $buf = '';
        foreach(glob($pat) as $file) {
            if ( is_readable($file) ) {
                $buf .= static::parse(file_get_contents($file));
            }
        }
        return $buf;
    }

    /**
     * Parse each file in each directory in a glob.
     * @return string The concatenated parsed matched files from the matched directories.
     */
    protected static function _handleSourceDirectoryGlob($pat)
    {
        $buf = '';
        $dirs = glob($pat, GLOB_ONLYDIR);
        foreach($dirs as $dir) {
            if ($dp = opendir($dir) ) {
                while (false !== ($entry = readdir($dp))) {
                    $filepath = realpath("$dir/$entry");
                    // This pattern comes from `man 5 interfaces`
                    if ( is_file($filepath) && preg_match('/^[a-zA-Z0-9_-]+$/', basename($filepath)) ) {
                        $buf .= static::parse(file_get_contents($filepath));
                    }
                }
                closedir($dp);
            }
        }
        return $buf;
    }
}
