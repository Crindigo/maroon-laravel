<?php

use Illuminate\Foundation\Application;
use Illuminate\Console\Command;

class CompileLessCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'maroon:less:compile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compiles less files';

    /**
     * Illuminate application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new Lessy command instance.
     *
     * @param Application $app
     * @return \CompileLessCommand
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->app = $app;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $less = new Maroon\LessCompiler($this->app);
        $this->line("\n<comment>Maroon Less Compiler</comment> <info>Compiling files...</info>");
        $less->compileLessFiles( true );
        $this->info("Done\n");
    }
}
