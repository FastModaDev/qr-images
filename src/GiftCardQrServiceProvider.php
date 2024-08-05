<?php

namespace FastModaDev\QrImages;

use Illuminate\Support\ServiceProvider;
use FastModaDev\QrImages\Console\Commands\InstallCommand;
use FastModaDev\QrImages\Console\Commands\GiftCards;

class GiftCardQrServiceProvider extends ServiceProvider
{

  /**
   * Register services.
   */
  public function register(): void
  {
    
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    if ( $this->app->runningInConsole() )
    {
      $this->commands([
        InstallCommand::class,
        GiftCards::class,
      ]);
    }
  }

}
