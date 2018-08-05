<?php

namespace App;

use FPDF;

class My_PDF extends FPDF
{

    protected $imagepath;

    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);

        $config = new Config();

        //Modification du chemin des fonts
        $this->fontpath = $config->getGlobal("FILE_ROOT") . "model/pdf/font/";
        //Modification du chemin des images
        $this->imagepath = $config->getGlobal("FILE_ROOT") . "src/img/";

        $this->loadFonts();
    }

    public function loadFonts()
    {
        $this->AddFont("GreatVibes", "", "GreatVibes.php");
        $this->AddFont("LatoLight", "", "LatoLight.php");
        $this->AddFont("Lato", "", "Lato.php");
    }

    public function Image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $link = '')
    {
        parent::Image($this->imagepath . $file, $x, $y, $w, $h, $type, $link);
    }

    public function hexToRgb($hex, $alpha = false) {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
        if ( $alpha ) {
            $rgb['a'] = $alpha;
        }
        return $rgb;
    }



    var $col=0;

    function SetCol($col)
    {
        //Set position on top of a column
        $this->col=$col;
        $this->SetLeftMargin(10+$col*40);
        $this->SetY(25);
    }

    function AcceptPageBreak()
    {
        //Go to the next column
        $this->SetCol($this->col+1);
        return false;
    }

    function DumpFont($FontName)
    {
        $this->AddPage();
        //Title
        $this->SetFont('Arial','',16);
        $this->Cell(0,6,$FontName,0,1,'C');
        //Print all characters in columns
        $this->SetCol(0);
        for($i=32;$i<=255;$i++)
        {
            $this->SetFont('Arial','',14);
            $this->Cell(12,5.5,"$i : ");
            $this->SetFont($FontName);
            $this->Cell(0,5.5,chr($i),0,1);
        }
        $this->SetCol(0);
    }

}