<?php
namespace App\pdf;

use App\Config;
use App\My_PDF;
use App\Tables\Epilation;

class EpilationPDF extends My_PDF {

    public static function listeTarifs($donnees){
        $pdf = new self();

        $colorEpilation = $pdf->hexToRgb("C16B90");

        $pdf->AddPage();
        //Affichage du logo
        $y = 10;
        $pdf->Image("logo_full_HD.png", 55, $y, 100);

        $y = 60;
        foreach ($donnees as $type=>$epilations){
            if (empty($epilations))continue;
            $pdf->SetTextColor(0);
            $pdf->SetFont('GreatVibes','',30);

            $pdf->SetXY(0, $y);
            $pdf->Cell(210,10, "Epilation ".strtolower(Epilation::TYPES[$type]), '', '', 'C');

            $y += 20;
            foreach ($epilations as $epilation){
                $x = 30;
                $pdf->SetFont('Lato','',13);
                //Libellé
                $pdf->SetTextColor($colorEpilation['r'], $colorEpilation['g'], $colorEpilation['b']);
                $pdf->SetXY($x, $y);
                $pdf->Cell(110,8, utf8_decode(mb_strtoupper($epilation->libelle)), 0, '', 'L');
                $x += 110;

                $pdf->SetFont('LatoLight','',13);
                //Pointillés
                $pdf->SetTextColor(0);
                $pdf->SetXY($x, $y);
                $pdf->Cell(25,8, ".........................", 0, '', 'L');
                $x += 25;

                //Billet
                $pdf->Image("billet.png", $x, $y + 2, 5);
                $x += 5;

                //Prix
                $pdf->SetXY($x, $y);
                $pdf->Cell(14,8, str_replace(".", ",", $epilation->prix), 0, '', 'R');
                $x += 14;

                //Euro
                $pdf->Image("euro.png", $x, $y + 1.5, 2.5);


                $y += 8;
            }

            $y += 20;
        }

        $pdf->Output();
    }

}