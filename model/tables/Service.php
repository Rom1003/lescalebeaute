<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'service';
    public $timestamps = false;

    public function categorie()
    {
        return $this->belongsTo('App\Tables\Categorie');
    }

    public function servicePrix()
    {
        return $this->hasMany('App\Tables\ServicePrix');
    }

    public function serviceImage()
    {
        return $this->hasMany('App\Tables\ServiceImage');
    }

    public static function detail($id){
        $query = self::with('categorie')->with('servicePrix')->with('serviceImage.image')->where('id', $id);
        return $query->first();
    }

    public static function pagination($page = 1, $nbParPage = 15, $categorie = '')
    {
        if ($page <= 1) {
            $debut = 0;
            $page = 1;
        } else {
            $debut = ($page - 1) * $nbParPage;
        }

        $query = self::with('categorie');

        if (!empty($categorie)){
            $query->where('categorie_id', $categorie);
        }

        $query = $query->orderBy('libelle');

        $total = $query->get()->count();
        $results = $query->take($nbParPage)->skip($debut)->get()->toArray();

        $tableau = '';
        foreach ($results as $row){
            //menu2.description|length > 100 ? menu2.description|slice(0, 100) ~ '...' : menu2.description
            $tableau .= '
            <tr>
                <td>'.$row['libelle'].'</td>
                <td>'.(strlen($row['description']) > 100 ? substr($row['description'], 0, 100).'...' : $row['description']).'</td>
                <td>'.$row['categorie']['libelle'].'</td>
                <td>
                    <a href="'.getRouteUrl('service_detail', ['id' => $row['id'], 'libelle' => toAscii($row['libelle'])]).'" data-trigger-class data-tooltip title="Voir" target="_blank"><i class="fas fa-search"></i></a>
                    <a href="'.getRouteUrl('admin_service_edit', ['id' => $row['id']]).'" data-trigger-class data-tooltip title="Modifier" target="_blank"><i class="fas fa-edit"></i></a>
                </td>
            </tr>';
        }


        $pagination = generatePagination($page, $total, $nbParPage);

        return array('tableau' => $tableau, 'pagination' => $pagination);
    }

}