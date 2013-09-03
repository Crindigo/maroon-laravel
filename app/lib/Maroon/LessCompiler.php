<?php

namespace Maroon;

use Illuminate\Foundation\Application;
use lessc;

/**
 * Based on Zizaco/Lessy with custom additions by Steven Harris.
 * Passes over .less files that begin with an underscore, those are understood to be include-only.
 *
 * @package Maroon
 */
class LessCompiler
{
    /**
     * Less compiler
     *
     * @var lessc
     */
    public $lessc;

    /**
     * Laravel application
     *
     * @var Application
     */
    public $_app;

    /**
     * Create a new LessCompiler instance.
     *
     * @param  Application $app
     */
    public function __construct(Application $app)
    {
        $this->_app = $app;
        $this->lessc = new lessc;
    }

    public function compileTree($origin, $destination)
    {
        $this->_app['config']->set('maroon.less.input_dir', $origin);
        $this->_app['config']->set('maroon.less.output_dir', $destination);

        $this->compileLessFiles(true);
    }

    /**
     * Compiles the less files
     *
     * @param  bool  $verbose
     * @return void
     */
    public function compileLessFiles($verbose = false)
    {
        $root        = $this->_app['path'] . '/';
        $origin      = $this->_app['config']->get('maroon.less.input_dir');
        $destination = $this->_app['config']->get('maroon.less.output_dir');

        if ( empty($origin) ) {
            $origin = 'less';
        }

        if ( empty($destination) ) {
            $destination = '../public/assets/css';
        }

        if ( $verbose ) {
            print_r('LESS files: <app>/' . $origin . "\n");
            print_r('Output to:  <app>/' . $destination . "\n\n");
        }

        $origin      = $root . $origin;
        $destination = $root . $destination;

        if ( !is_dir($destination) ) {
            mkdir($destination, 0775, true);
        }

        $this->compileRecursive($origin . '/', $destination . '/', '', $verbose);
    }

    /**
     * Recursive file compilation
     *
     * @param  string  $origin
     * @param  string  $destination
     * @param  string  $offset
     * @param  bool  $verbose
     * @return array
     */
    protected function compileRecursive($origin, $destination, $offset = '', $verbose = false)
    {
        $tree = array();

        if ( !is_dir($origin . $offset) ) {
            return $tree;
        }

        $dir = scandir($origin . $offset);

        foreach ( $dir as $filename ) {
            if ( is_dir($origin . $offset . $filename) && $filename != '.' && $filename != '..') {
                if ( !file_exists($destination . $offset . $filename) ) {
                    mkdir($destination . $offset . $filename);
                }

                // Recursive call
                $tree[$filename] = $this->compileRecursive(
                    $origin, $destination, $offset . $filename . '/', $verbose);
            } else if ( is_file($origin . $offset . $filename) ) {
                if ( substr($filename, -5) == '.less' || substr($filename, -4) == '.css' ) {
                    // skip files with leading underscore
                    if ( $filename[0] === '_' ) {
                        continue;
                    }
                    $tree[] = $filename;

                    if ( $verbose ) {
                        print_r($offset . $filename . "\n");
                    }

                    // Compile file
                    $this->lessc->checkedCompile(
                        $origin . $offset . $filename,
                        $destination . $offset . substr($filename, 0, strrpos($filename, '.', -1)) . '.css'
                    );
                } else {
                    $in  = $origin . $offset . $filename;
                    $out = $destination . $offset . $filename;

                    if ( $verbose ) {
                        print_r($offset . $filename . "\n");
                    }

                    // Copy any assets that the css/less may use
                    if ( !is_file($out) || filemtime($in) > filemtime($out) ) {
                        copy($in, $out);
                    }
                }
            }
        }

        return $tree;
    }
}