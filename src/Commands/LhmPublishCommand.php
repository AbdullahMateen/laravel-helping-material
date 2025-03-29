<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LhmPublishCommand extends Command
{
    private Filesystem $filesystem;
    private string     $prefix;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lhm:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the laravel helping material files';

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->basepath   = base_path('vendor\abdullah-mateen\laravel-helping-material');
        $this->prefix     = "$this->basepath\src\Commands";
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $options = [
            'All',
            'Config         => Copy enum files',
            'Enums          => Copy enum files',
            'Exceptions     => Copy exceptions files',
            'Helpers        => Copy helper files',
            // 'Interfaces     => Copy interface files',
            'Middlewares    => Override middleware',
            'Migrations     => Copy migrations',
            'Models         => Override model files',
            'Resources      => Copy resources',
            // 'Rules          => Override rule files',
            'Services       => Override service files',
            'Traits         => Override trait files',
        ];

        $values = array_unique($this->choice(
            "Which files would you like to publish? You can select multiples using comma (,) e.g. 1,2,3",
            $options,
            null,
            null,
            true
        ));

        $values     = array_map(fn ($value) => trim(explode('=>', $value)[0]), $values);
        $publishAll = in_array('All', $values, true);
        if ($publishAll) {
            $options = array_map(fn ($option) => trim(explode('=>', $option)[0]), $options);
            array_shift($options);
            $values = $options;
        }

        foreach ($values as $value) {
            $this->warn("Publishing $value");
            $path = $this->{"publish$value"}();
            $this->info("$value successfully published to '$path'");
        }

        return Command::SUCCESS;
    }

    public function publishConfig()
    {
        $this->filesystem->ensureDirectoryExists(base_path('config'));
        $this->filesystem->copy("$this->basepath/src/lhm.php", base_path('config/lhm.php'));
        return base_path('app/Enums');
    }

    public function publishEnums()
    {
        $this->filesystem->ensureDirectoryExists(base_path('app/Enums/Media'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Enums/Publish/Media/MediaDiskEnum.stub", base_path('app/Enums/Media/MediaDiskEnum.php'));

        $this->filesystem->ensureDirectoryExists(base_path('app/Enums/User'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Enums/Publish/User/RoleEnum.stub", base_path('app/Enums/User/RoleEnum.php'));

        return base_path('app/Enums');
    }

    public function publishExceptions()
    {
        $this->filesystem->ensureDirectoryExists(base_path('app/Exceptions'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Exceptions/ApiResponseExceptionHandler.stub", base_path('app/Exceptions/ApiResponseExceptionHandler.php'));

        return base_path('app/Exceptions');
    }

    public function publishHelpers()
    {
        $this->filesystem->ensureDirectoryExists(base_path('app/Helpers'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Helpers/custom.stub", base_path('app/Helpers/custom.php'));

        return base_path('app/Helpers');
    }

    public function publishInterfaces()
    {
        // $this->filesystem->ensureDirectoryExists(base_path('app/Interfaces'));
        // $this->filesystem->copy("$this->prefix/stubs/Interfaces/ColorsInterface.stub", base_path('app/Interfaces/ColorsInterface.php'));
        // return base_path('app/Interfaces');
    }

    public function publishMiddlewares()
    {
        $this->filesystem->ensureDirectoryExists(base_path('app/Http/Middleware'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Middleware/AuthorizationMiddleware.stub", base_path('app/Http/Middleware/AuthorizationMiddleware.php'));

        return base_path('app/Http/Middleware');
    }

    public function publishMigrations()
    {
        $this->filesystem->ensureDirectoryExists(base_path('database/migrations'));
        $this->filesystem->copyDirectory("$this->basepath/src/migrations/", base_path('database/migrations/'));
        return base_path('database/migrations');
    }

    public function publishModels()
    {
        $this->filesystem->ensureDirectoryExists(base_path('app/Models'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Models/ExtendedModel.stub", base_path('app/Models/ExtendedModel.php'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Models/Media.stub", base_path('app/Models/Media.php'));

        return base_path('app/Models');
    }

    public function publishResources()
    {
        $this->filesystem->ensureDirectoryExists(base_path('resources/sass/'));
        $this->filesystem->copyDirectory("$this->basepath/src/resources/sass/", base_path('resources/sass/'));

        return base_path('resources/sass');
    }

    public function publishRules()
    {
        // $this->filesystem->ensureDirectoryExists(base_path('app/Rules'));
        // $this->filesystem->copy("$this->prefix/stubs/Rules/Throttle.stub", base_path('app/Rules/Throttle.php'));
        // return base_path('app/Rules');
    }

    public function publishServices()
    {
        $this->filesystem->ensureDirectoryExists(base_path('app/Services/Media'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Services/Media/MediaService.stub", base_path('app/Services/Media/MediaService.php'));

        return base_path('app/Services');
    }

    public function publishTraits()
    {
        $this->filesystem->ensureDirectoryExists(base_path('app/Traits/General/Model'));
        $this->filesystem->copy("$this->prefix/stubs/lhm/Traits/General/Model/UserNotificationsTrait.stub", base_path('app/Traits/General/Model/UserNotificationsTrait.php'));

        return base_path('app/Traits');
    }
}
