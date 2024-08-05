<?php

namespace FastModaDev\QrImages\Console\Commands;

use Illuminate\Filesystem\Filesystem;

trait InstallsApiStack
{

    /**
     * Install the API stack.
     *
     * @return int|null
     */
    protected function installApiStack()
    {
        
        $files = new Filesystem;

        // Controllers...
        copy(__DIR__.'/../../../stubs/api/app/Http/Controllers/GiftCardManagementController.php', app_path('Models/GiftCardManagementController.php'));


        // Migrations...
        copy(__DIR__.'/../../../database/migrations/2024_08_03_011639_create_management_gift_cards_table.php', base_path('migrations/2024_08_05_000000_create_management_gift_cards_table.php'));


        // Routes...
        copy(__DIR__.'/../../../../stubs/api/routes/gift_card2.php', base_path('routes/gift_card2.php'));
        $this->installRoute();

        


        $this->components->info('Notificaciones instalada OK.');
    }




    protected function installRoute( )
    {

        $bootstrapApp = file_get_contents(base_path('routes/web.php'));
        $text = PHP_EOL."require __DIR__.'/gift_card2.php';".PHP_EOL;
        file_put_contents( base_path('routes/api.php') , $text, FILE_APPEND );

        $this->info("<bg=yellow;fg=black> RUTA:: </> OK!");

    }

    
}
