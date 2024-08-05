<?php

namespace FastModaDev\QrImages\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

#[AsCommand(name: 'fastmoda:gift-cards-install')]
class InstallCommand extends Command implements PromptsForMissingInput
{

  use InstallsApiStack;
  //use InstallsInertiaStacks;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'fastmoda:gift-cards-install {stack : The development stack that should be installed (blade,livewire,livewire-functional,react,vue,api)}
    {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Instalar los controladores y recursos de notifications';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info("<bg=yellow;fg=black> JOHANN RAMÍREZ:: </> : 77777!");
    
    if ($this->argument('stack') === 'vue')
    {
        return $this->installInertiaVueStack();
    } 
    elseif ($this->argument('stack') === 'react')
    {
        return $this->installInertiaReactStack();
    }
    elseif ($this->argument('stack') === 'api')
    {
        return $this->installApiStack();
    }

    $this->components->error('Pila no válida. Las pilas admitidas son [blade], [livewire], [livewire-functional], [react], [vue], and [api].');

    return 1;
  }

 

  

  
  /**
   * Determine if the given Composer package is installed.
   *
   * @param  string  $package
   * @return bool
   */
  protected function hasComposerPackage($package)
  {
    $packages = json_decode(file_get_contents(base_path('composer.json')), true);

    return array_key_exists($package, $packages['require'] ?? [])
        || array_key_exists($package, $packages['require-dev'] ?? []);
  }

  /**
   * Installs the given Composer Packages into the application.
   *
   * @param  array  $packages
   * @param  bool  $asDev
   * @return bool
   */
  protected function requireComposerPackages(array $packages, $asDev = false)
  {
    $composer = $this->option('composer');

    if ($composer !== 'global') {
        $command = ['php', $composer, 'require'];
    }

    $command = array_merge(
        $command ?? ['composer', 'require'],
        $packages,
        $asDev ? ['--dev'] : [],
    );

    return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
    ->setTimeout(null)
    ->run(function ($type, $output) {
        $this->output->write($output);
    }) === 0;
  }

  /**
   * Removes the given Composer Packages from the application.
   *
   * @param  array  $packages
   * @param  bool  $asDev
   * @return bool
   */
  protected function removeComposerPackages(array $packages, $asDev = false)
  {
      $composer = $this->option('composer');

      if ($composer !== 'global') {
          $command = ['php', $composer, 'remove'];
      }

      $command = array_merge(
          $command ?? ['composer', 'remove'],
          $packages,
          $asDev ? ['--dev'] : [],
      );

      return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
          ->setTimeout(null)
          ->run(function ($type, $output) {
              $this->output->write($output);
          }) === 0;
  }

  /**
   * Update the "package.json" file.
   *
   * @param  callable  $callback
   * @param  bool  $dev
   * @return void
   */
  protected static function updateNodePackages(callable $callback, $dev = true)
  {
      if (! file_exists(base_path('package.json')))
      {
          return;
      }

      $configurationKey = $dev ? 'devDependencies' : 'dependencies';

      $packages = json_decode(file_get_contents(base_path('package.json')), true);

      $packages[$configurationKey] = $callback(
          array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
          $configurationKey
      );

      ksort($packages[$configurationKey]);

      file_put_contents(
          base_path('package.json'),
          json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
      );
  }

  /**
   * Delete the "node_modules" directory and remove the associated lock files.
   *
   * @return void
   */
  protected static function flushNodeModules()
  {
      tap(new Filesystem, function ($files) {
          $files->deleteDirectory(base_path('node_modules'));

          $files->delete(base_path('yarn.lock'));
          $files->delete(base_path('package-lock.json'));
      });
  }


  /**
   * Replace a given string within a given file.
   *
   * @param  string  $search
   * @param  string  $replace
   * @param  string  $path
   * @return void
   */
  protected function replaceInFile($search, $replace, $path)
  {
    file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
  }

  /**
   * Get the path to the appropriate PHP binary.
   *
   * @return string
   */
  protected function phpBinary()
  {
    return (new PhpExecutableFinder())->find(false) ?: 'php';
  }

  /**
   * Run the given commands.
   *
   * @param  array  $commands
   * @return void
   */
  protected function runCommands($commands)
  {
    $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

    if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
        try {
            $process->setTty(true);
        } catch (RuntimeException $e) {
            $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
        }
    }

    $process->run(function ($type, $line) {
        $this->output->write('    '.$line);
    });
  }

  /**
   * Prompt for missing input arguments using the returned questions.
   *
   * @return array
   */
  protected function promptForMissingArgumentsUsing()
  {
    return [
      'stack' => fn () => select(
        label: '¿Qué Marco te gustaría instalar?',
        options: [
          'vue' => 'Vue Inertia',
          'api' => 'API solamente',
        ],
        scroll:2,
      ),
    ];
  }

  /**
   * Interact further with the user if they were prompted for missing arguments.
   *
   * @param  \Symfony\Component\Console\Input\InputInterface  $input
   * @param  \Symfony\Component\Console\Output\OutputInterface  $output
   * @return void
   */
  protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
  {
    $stack = $input->getArgument('stack');

    if (in_array($stack, ['react', 'vue']))
    {
      collect(multiselect(
        label: 'Would you like any optional features?',
        options: [
          'dark' => 'Dark mode',
          'ssr' => 'Inertia SSR',
          'typescript' => 'TypeScript',
        ]
      ))->each(fn ($option) => $input->setOption($option, true));
    }
    elseif (in_array($stack, ['blade', 'livewire', 'livewire-functional']))
    {
      $input->setOption('dark', confirm(
        label: 'Would you like dark mode support?',
        default: false
      ));
    }
      
  }




}
