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

    public static function paginationOLD($nbParPage = 15)
    {
        $paginator = self::paginate($nbParPage);
        $paginator->withPath('test/test');
        $elements = $paginator;

        var_dump($paginator->links());

        $pagine = '';


        if ($paginator->hasPages()) {
            $pagine .= '
        <ul class="pagination" role="navigation">';
            if ($paginator->onFirstPage()) {
                $pagine .= '
            <li class="page-item disabled" aria-disabled="true" aria-label="Précédent">
                <span class="page-link" aria-hidden="true" >&lsaquo;</span >
            </li>';
            } else {
                $pagine .= '
            <li class="page-item">
                <a class="page-link" href = "' . $paginator->previousPageUrl() . '" rel = "prev" aria-label = "Précédent" >&lsaquo;</a>
            </li>';
            }
            foreach ($elements as $element) {
                if (is_string($element)) {
                    $pagine .= '
            <li class="page-item disabled" aria-disabled="true"><span class="page-link">' . $element . '</span></li>';
                }

                if (is_array($element)) {
                    foreach ($element as $page => $url) {
                        if ($page == $paginator->currentPage()) {
                            $pagine .= '
            <li class="page-item active" aria-current="page"><span class="page-link">' . $page . '</span></li>';
                        } else {
                            $pagine .= '
            <li class="page-item"><a class="page-link" href="' . $url . '">'.$page.'</a></li>';
                        }
                    }
                }
            }

            if ($paginator->hasMorePages()) {
                $pagine .= '
            <li class="page-item">
                <a class="page-link" href="' . $paginator->nextPageUrl() . '" rel="next" aria-label="Suivant">&rsaquo;</a>
            </li>';
            } else {
                $pagine .= '
            <li class="page-item disabled" aria-disabled="true" aria-label="Suivant">
                <span class="page-link" aria-hidden="true">&rsaquo;</span>
            </li>';
            }
            $pagine .= '
        </ul>';
        }


        return $pagine;
    }

    public static function pagination($page = 1, $nbParPage = 15)
    {
        if ($page == 1){
            $debut = 0;
        } else {
            $debut = ($page-1) * $nbParPage;
        }

        $query = self::orderBy('libelle');

        $total = $query->get()->count();
        $results = $query->take($nbParPage)->skip($debut)->get()->toArray();

//        if ()

        $pagination = '';


    }

}