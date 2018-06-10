<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $table = 'categorie';
    public $timestamps = false;

    public function image()
    {
        return $this->belongsTo('App\Tables\Image', 'image_id');
    }
    public function slideImage()
    {
        return $this->belongsTo('App\Tables\Image', 'slide_image_id');
    }
    public function service()
    {
        return $this->hasMany('App\Tables\Service');
    }

    /**
     * Récupération des catégories liées à une catégorie en particulier
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function listeCategorie($id){
        return self::with('image')->where('categorie_id', $id)->get();
    }

    /**
     * Récupération des catégories selon leur arborescence
     * @param bool $services - true pour récupérer les articles liés aux catégories
     * @return array
     */
    public static function getMenu($services = true, $showNotEditable = true)
    {

        try {

            if ($showNotEditable === false){
                $req = self::where('editable', 1);
            } else {
                $req = self::whereIn('editable', [0, 1]);
            }

            if ($services === true){
                $req = $req->with(['service' => function ($q) {
//                    $q->take(5);
                }]);
            }

            $categories = $req->orderBy('niveau')->orderBy('categorie_id')->orderBy('ordre')->get()->toArray();
//            var_dump($req->orderBy('niveau')->orderBy('categorie_id')->orderBy('ordre')->get()->tosql());

        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }


//        var_dump($categories);

        //Création du tableau pour le menu
        $menu = array();
        $sous_menu = array();
        foreach ($categories as $category) {
            switch ($category['niveau']) {
                case 0:
                    $menu[$category['id']] = $category;
                    break;
                case 1:
                    $sous_menu[$category['id']] = $category;
                    break;
            }
        }
        foreach ($sous_menu as $sm) {
            $menu[$sm['categorie_id']]['sub'][$sm['ordre']] = $sm;
        }

        return $menu;
    }

    public static function getImageSlide($categorie){
        if (is_string($categorie) || is_integer($categorie)){
            $id = $categorie;
        } elseif (!empty($categorie->slide_image_id)) {
            $id = $categorie->slide_image_id;
        } else {
            return null;
        }

        $image = Image::find($id);
        if (!empty($image)){
            return $image->path.$image->filename;
        }
        return false;
    }




}