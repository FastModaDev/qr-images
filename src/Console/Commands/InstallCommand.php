<?php

namespace FastModaDev\QrImages\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{

  use InstallsApiStack;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'fastmoda:gift-cards-install {stack=blade : The development stack that should be installed (blade,react,vue,api)}
                          {--inertia : Indicate that the Vue Inertia stack should be installed (Deprecated)}
                          {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Install the Breeze controllers and resources';

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
      if ($this->option('inertia') || $this->argument('stack') === 'vue')
      {
        $this->info("<bg=red;fg=black>Proximamente</> en las salas de cine 😉");
        // return $this->installInertiaVueStack();
      }
      elseif ($this->argument('stack') === 'api')
      {
        $this->info("<bg=blue;fg=black>Instalando...</> 😉");
        return $this->installApiStack();
      }
  }

  /**
   * Installs the given Composer Packages into the application.
   *
   * @param  mixed  $packages
   * @return void
   */
  protected function requireComposerPackages($packages)
  {
      $composer = $this->option('composer');

      if ($composer !== 'global') {
          $command = ['php', $composer, 'require'];
      }

      $command = array_merge(
          $command ?? ['composer', 'require'],
          is_array($packages) ? $packages : func_get_args()
      );

      (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
          ->setTimeout(null)
          ->run(function ($type, $output) {
              $this->output->write($output);
          });
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
      if (! file_exists(base_path('package.json'))) {
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

}
