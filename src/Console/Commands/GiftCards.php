<?php

namespace FastModaDev\QrImages\Console\Commands;

use FastModaDev\QrImages\Utils\GiftCard\QrCodeImg;
use DateTime;
use Illuminate\Console\Command;

class GiftCards extends Command
{


  /**
   * The name and signature of the console command.
   *
   * @var string
  */
  protected $signature = 'fastmoda:gift-cards';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Execute the console command.
   */
  public function handle()
  {

    $this->runGenerateCodes( 'production/' );
    
  }

  public function runGenerateCodes( $folder = 'test/' )
  {

    $util_qr = new QrCodeImg();
    $amounts = $this->amounts();

    $val_a = 0;
    $val_b = 0;
    foreach ($amounts as $key )
    {
      $val_a += $key->amount;
      $val_b += $key->value * $key->amount;
    }
    $val_b = number_format( $val_b );
    
    $this->info('EJECUCIÓN::[RUN]([INICIANDO PROCESO]) (CANTIDAD)[' . $val_a . '] --- (VALOR)[' . $val_b . ']' );
    $this->info('  ' );


    $temp = $folder;
    $cantidad = 1;
    $valor = 0;
    foreach ( $amounts as $key )
    {
      

      $val_a = $key->amount;
      $val_b = $key->value * $key->amount;
      $val_b = number_format( $val_b );
      $this->info('   (CANTIDAD)[' . $val_a . '](VALOR)[' . number_format($key->value) . '](TOTAL)[' . $val_b . ']' );
      $folder = $temp . $key->value.'/';

      $i = 0;
      $valor =0;
      $startTime = new DateTime();

      while ( $i < $key->amount )
      {
        
        $code = $util_qr->generateGiftCard( $key->value , $folder );
        $valor += $key->value;
        $i++;

      }

      $endTime = new DateTime();
      $interval = $startTime->diff($endTime);

      $text = $interval->format('%H:%I:%S');

      $this->info('   (CANTIDAD)[' . $i . '](VALOR)[' . number_format( $key->value ) . '](TOTAL)[' .number_format($valor) . ']' );

      $formattedInterval = $interval->format('%H:%I:%S');

      $this->info( '   RUN::[END][TIME] TEST::'.$code. '__ ' . $formattedInterval );
      $this->info( '  ' );
      $this->info( '  ' );
      $this->info( '  ' );

      
    }

      

    $this->info( 'EJECUCIÓN::[RUN][PROCESO TERMINADO] (CANTIDAD)- []' );
    
  }

  public function amounts()
  {
    $amounts[] = (object)[
      "amount" => 2000,
      // "amount" => 200,
      "value" => 10000,
    ];
    $amounts[] = (object)[
      "amount" => 50,
      "value" => 20000,
    ];
    $amounts[] = (object)[
      "amount" => 50,
      "value" => 30000,
    ];
    $amounts[] = (object)[
      "amount" => 50,
      "value" => 40000,
    ];
    $amounts[] = (object)[
      "amount" => 50,
      "value" => 50000,
    ];
    $amounts[] = (object)[
      "amount" => 50,
      "value" => 100000,
    ];
    $amounts[] = (object)[
      "amount" => 50,
      "value" => 200000,
    ];





    // $amounts[] = (object)[
    //   "amount" => 20,
    //   "value" => 10000,
    // ];
    // $amounts[] = (object)[
    //   "amount" => 5,
    //   "value" => 20000,
    // ];
    // $amounts[] = (object)[
    //   "amount" => 5,
    //   "value" => 30000,
    // ];
    // $amounts[] = (object)[
    //   "amount" => 5,
    //   "value" => 40000,
    // ];
    // $amounts[] = (object)[
    //   "amount" => 5,
    //   "value" => 50000,
    // ];
    // $amounts[] = (object)[
    //   "amount" => 5,
    //   "value" => 100000,
    // ];
    // $amounts[] = (object)[
    //   "amount" => 5,
    //   "value" => 200000,
    // ];

    return $amounts;
  }


}
