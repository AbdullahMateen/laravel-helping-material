<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Commands;

use Illuminate\Foundation\Console\EnumMakeCommand;

class LhmMakeEnumCommand extends EnumMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:lhm-enum';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('string') || $this->option('int')) {
            return $this->resolveStubPath('/stubs/lhm/Enums/enum.backed.stub');
        }

        return $this->resolveStubPath('/stubs/lhm/Enums/enum.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }
}
