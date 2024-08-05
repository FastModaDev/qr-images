<?php

namespace App\Utils\Test;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class QrCodeImg
{

  
  public function generateGiftCard( $value , $folder )
  {

    $code = $this->generateCodeAleatory(10);

    DB::table('management_gift_cards')
    ->insert([

      'value' => $value,
      'code'  => $code,
      'saldo' => $value,

    ]);

    $img = $this->generateWithLogo( $code , $folder );

    return $img;

  }
  public function generateCodeAleatory( $value )
  {

    $code = $this->generateRandomString(10);

    $validate_ = DB::table('management_gift_cards')
    ->where( 'code' , $code )
    ->first();

    $val = false;
    if ( $validate_ )
    {
      $val = true;
    }

    while ( $val )
    {
      $code = $this->generateRandomString(10);
      $validate_ = DB::table('users')
      ->where( 'code' , $code )
      ->first();
      if ( !$validate_ )
      {
        $val = false;
      }
    }

    return $code;

  }
  function generateRandomString( $length = 10 )
  {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++)
    {
      $randomString .= $characters[ rand( 0 , $charactersLength - 1 ) ];
    }
    return $randomString;
  }
  public function generateWithLogo( $alphanumericWord , $folder )
  {
    $writer = new PngWriter();

    // Create QR code
    $qrCode = QrCode::create( $alphanumericWord  )
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
    ->setSize(200)
    ->setMargin(10)
    // ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
    ->setForegroundColor(new Color(0, 0, 0))
    ->setBackgroundColor(new Color(255, 255, 255));

    // Create generic logo
    $logo = Logo::create( __DIR__.'/llp2.png'  )
    ->setResizeToWidth(16)
    ->setPunchoutBackground(true);

    // Create generic label
    // $label_text = 'Lili Pink & Yoi';
    $label_text = 'Fast Moda SAS';
    $label = Label::create( $label_text )
    ->setFont( new NotoSans(11) )
    ->setTextColor( new Color( 0 , 0 , 0 ) );

    // $result = $writer->write( $qrCode , $logo );
    $result = $writer->write( $qrCode , $logo , $label );

    $qrCodeImage = $result->getString();

    $filePath = 'qrcodes/'. $folder .'gift_cardqr_'.$alphanumericWord.'.png';
    Storage::disk('gift_cards')->put( $filePath , $qrCodeImage );


    // Validate the result
    // $writer->validateResult( $result , 'Life is too short to be generating QR codes' );

    return $filePath;

  }


}
