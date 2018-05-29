<?php

namespace App\Http\Controllers\Admin;


use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Blog;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Flash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\ServiceProvider;

class BlogController extends Controller
{
    /**
     * show all blog
     * @param $id
     * @return view template
     */
    public function index($id)
    {

        if ($id == 'all')
        {
            $query = DB::table('wiki_save');
            $query->leftJoin('wiki_save_category', 'wiki_save.id', '=', 'wiki_save_category.save_id');
            $query->leftJoin('wiki_category', 'wiki_category.id', '=', 'wiki_save_category.category_id');
            $query->select(
                'wiki_save.date AS date ',
                'wiki_save.user AS user ',
                'wiki_save.title AS title ',
                'wiki_save.description AS description ',
                'wiki_save.keyword AS keyword ',
                DB::raw('group_concat(name SEPARATOR ", ") as category_name ')
            );
            $query->groupby('wiki_save.id');
	        $query->orderBy('wiki_save.id','desc');
	        $wiki_saves =$query->paginate(5);

        }
        else
        {
            $query1 = DB::table('wiki_save');
            $query1->leftJoin('wiki_save_category', 'wiki_save.id', '=', 'wiki_save_category.save_id');
            $query1->leftJoin('wiki_category', 'wiki_category.id', '=', 'wiki_save_category.category_id');
            $query1->where('wiki_save_category.category_id',$id);
            $query1->select(
                'wiki_save.id AS wiki_save_id ',
                'wiki_save.date AS date ',
                'wiki_save.user AS user ',
                'wiki_save.title AS title ',
                'wiki_save.description AS description ',
                'wiki_save.keyword AS keyword '
            );
            $query1->groupby('wiki_save.id');
	        $query1->orderBy('wiki_save.id','desc');

			$query2 = \DB::table(\DB::raw( "( {$query1->toSql()} ) as wiki_save_result" ))
	        ->mergeBindings($query1)
	        ->leftJoin('wiki_save_category', 'wiki_save_result.wiki_save_id', '=', 'wiki_save_category.save_id')
            ->leftJoin('wiki_category', 'wiki_category.id', '=', 'wiki_save_category.category_id')
            ->select(
                'wiki_save_result.date AS date ',
                'wiki_save_result.user AS user ',
                'wiki_save_result.title AS title ',
                'wiki_save_result.description AS description ',
                'wiki_save_result.keyword AS keyword ',
                DB::raw('group_concat(name SEPARATOR ", ") as category_name ')
            )
            ->groupby('wiki_save_result.wiki_save_id')
	        ->orderBy('wiki_save_result.wiki_save_id','desc');
	        $wiki_saves =$query2->paginate(5);

        }

        return view('pages.admin.blog.index',compact('wiki_saves'));
    }
}