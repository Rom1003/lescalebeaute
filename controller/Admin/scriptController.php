<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use App\Tables\Gamme;
use App\Tables\Produit;
use App\Tables\Image;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class scriptController
{

    private static function getDirContents($path, $extension = '')
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        $files = array();
        foreach ($rii as $file)
            if (!$file->isDir()) {
                //On ne garde pas les fichiers commencant par "."
                if (substr($file->getFileName(), 0, 1) != '.') {
                    if (!empty($extension) && substr($file->getFileName(), '-' . strlen(trim($extension, '.'))) != $extension) {
                        continue;
                    }
                    $files[] = $file;
                }
            }


        return $files;
    }

    public static function produitsAction()
    {
        $config = new Config();

        $pathInit = $config->getGlobal('FILE_ROOT') . 'scripts/';
        $csvFile = $pathInit . 'produits.csv';
        if (!file_exists($csvFile)) {
            exit($csvFile . ' Existe pas');
        }

        $files = self::getDirContents($pathInit, 'jpg');


        //Lecture csv
        $row = 1;
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                //Lignes
                $row++;
                if ($row === 2) continue;

                $jpg = null;
                $libelle = null;
                $gamme = null;

                for ($c = 0; $c < count($data); $c++) {
                    //Colonnes
                    $val = $data[$c];

                    switch ($c) {
                        //Fichier JPG
                        case 0:
                            $jpg = $val;
                            break;
                        //Libelle
                        case 1:
                            $libelle = $val;
                            break;
                        //Gamme
                        case 2:
                            $gamme = $val;
                            break;
                        default:
                            exit('ERREUR ligne ' . __LINE__);
                    }
                }

                if (empty($jpg) || empty($libelle) || empty($gamme)) exit('Valeurs vides ' . print_r($data));


                //Récupération de l'image
                $fileJPG = false;
                foreach ($files as $file) {
                    if ($file->getFileName() == $jpg . '.jpg') {
                        $fileJPG = $file->getPathName();
                        break;
                    }
                }

                if ($fileJPG === false) {
                    echo "<hr>";
                    var_dump($jpg);
                    echo "<br>Ligne sans image retrouvée<br>";
                    echo '<hr>';
                    continue;
                }

                if (!file_exists($fileJPG)) {
                    exit('Fichier inexistant : ' . $fileJPG);
                }

                //Vérif de la gamme
                $getGamme = Gamme::where('libelle', $gamme)->get();
                if ($getGamme->isEmpty()) {
                    //Création de la gamme
                    $setGamme = new Gamme();
                    $setGamme->libelle = $gamme;
                    if (!$setGamme->save()) {
                        exit('Erreur : ' . __LINE__);
                    }
                    $gamme_id = $setGamme->id;

                } else {
                    $gamme_id = $getGamme[0]->id;
                }

                //IMAGE
                $md5 = md5_file($fileJPG);

                $getImage = Image::where('md5', $md5)->get();
                if ($getImage->isEmpty()) {
                    //Ajout de l'image
                    $setImage = new Image();
                    $setImage->title = $libelle;
                    $setImage->path = 'produit/';
                    $setImage->filename = uniqid().'.jpg';
                    $setImage->md5 = $md5;
                    if (!$setImage->save()) {
                        exit('Erreur : ' . __LINE__);
                    }

                    //Déplacement de l'image
                    if (!rename($fileJPG, $config->getGlobal('FILE_ROOT') . '/src/img/'.$setImage->path.$setImage->filename)){
                        exit("Erreur déplacement fichier : '.$fileJPG.' \n <br> vers \n <br> ".$config->getGlobal('FILE_ROOT') . '/src/img/'.$setImage->path.$setImage->filename);
                    }

                    $image_id = $setImage->id;
                } else {
                    $image_id = $getImage[0]->id;
                }


                //PRODUIT
                $getProduit = Produit::where('libelle', $libelle)->get();
                if ($getProduit->isEmpty()) {
                    //Création du produit
                    $setProduit = new Produit();
                    $setProduit->libelle = $libelle;
                    $setProduit->actif = 1;
                    $setProduit->image_id = $image_id;
                    $setProduit->gamme_id = $gamme_id;

                    if (!$setProduit->save()) {
                        exit('Erreur : ' . __LINE__);
                    }
                } else {
                    echo "\n<br>Produit déjà existant : ".$libelle." \n<br>";
                }

            }
            fclose($handle);
        }
    }
}