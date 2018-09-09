<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $table = 'produit';
    public $timestamps = false;

    public function image()
    {
        return $this->belongsTo('App\Tables\Image');
    }

    public function gamme()
    {
        return $this->belongsTo('App\Tables\Gamme');
    }

    public static function pagination($typeHtml, $page = 1, $nbParPage = 15, $recherche = array())
    {
        if ($page <= 1) {
            $debut = 0;
            $page = 1;
        } else {
            $debut = ($page - 1) * $nbParPage;
        }

        $query = self::with('image')->with('gamme');

        if (!empty($recherche)){
            if (isset($recherche['gamme_id']) && !empty($recherche['gamme_id'])){
                $query->where('gamme_id', $recherche['gamme_id']);
            }
            if (isset($recherche['libelle']) && !empty($recherche['libelle'])){
                $query->where('libelle', 'like', '%'.$recherche['libelle'].'%');
            }
            if (isset($recherche['actif']) && in_array($recherche['actif'], array(0, 1))){
                $query->where('actif', $recherche['actif']);
            }
        }

        $query = $query->orderBy('libelle');

        $total = $query->get()->count();
        $results = $query->take($nbParPage)->skip($debut)->get()->toArray();

        if ($typeHtml == 'admin'){
            $tableau = '';
            foreach ($results as $row){

                $tableau .= '
            <tr data-id="'.$row['id'].'">
                <td>'.$row['libelle'].'</td>
                <td>'.$row['gamme']['libelle'].'</td>
                <!--<td>'.number_format($row['tarif'], '2', ',', ' ').' €</td>-->
                <td><img src="'.imagePath($row['image']['path'].$row['image']['filename']).'" alt="'.$row['image']['title'].'"></td>
                <td>
                    <a href="'.getRouteUrl('admin_produit_edit', ['id' => $row['id']]).'" data-trigger-class data-tooltip title="Modifier" target="_blank"><i class="fas fa-edit"></i></a>
                    <span data-etat="'.($row['actif'] == '1' ? 0 : 1).'" data-trigger-class data-tooltip title="'.($row['actif'] == '1' ? 'Désactiver' : 'Activer').'" class="cursor-p etat_produit"><i class="fas '.($row['actif'] == '1' ? 'fa-eye' : 'fa-eye-slash').'"></i></span>
                </td>
            </tr>';
            }
        } elseif ($typeHtml == 'base'){
            $tableau = '';
            foreach ($results as $row){
                //menu2.description|length > 100 ? menu2.description|slice(0, 100) ~ '...' : menu2.description
                $tableau .= '
            <div class="cell small-6 medium-6 large-3 bloc-article">
                <div class="image-article">
                    <span class="image-helper"></span>
                    <img src="'.imagePath($row['image']['path'].$row['image']['filename']).'" alt="'.$row['image']['title'].'">
                </div>
                <p class="libelle-article">'.$row['libelle'].'</p>
                <p class="marque-article">'.$row['gamme']['libelle'].'</p>
            </div>';
            }
        }



        $pagination = generatePagination($page, $total, $nbParPage);

        return array('tableau' => $tableau, 'pagination' => $pagination);
    }


}