<?php

	namespace App\Http\Controllers\Admin;


	use App\Wiki;
	use Carbon\Carbon;
	use Illuminate\Http\Request;

	use App\Http\Requests;
	use App\Http\Controllers\Controller;
	use Illuminate\Support\Facades\DB;
	use Flash;
	use Illuminate\Support\Facades\Redirect;
	use Illuminate\Support\ServiceProvider;

	class WikiController extends Controller
	{
		public function index()
		{

			$wiki_users = DB::table('wiki_user')->get();
			$wiki_categories = DB::table('wiki_category')->get();

			return view('pages.admin.wiki.index', compact('wiki_users', 'wiki_categories'));
		}

		public function category_ajax_show()
		{

			$wiki_categories = DB::table('wiki_category')->get();
			$wiki_categories_array = array();
			foreach ($wiki_categories as $wiki_categorie) {

				$wiki_categories_array[] = $wiki_categorie;

			}
			echo json_encode($wiki_categories_array);
		}

		public function category_ajax_show_left()
		{

			$wiki_categories = DB::table('wiki_category')->get();
			$wiki_categories_array = array();
			foreach ($wiki_categories as $wiki_categorie) {

				$wiki_categories_array[] = $wiki_categorie;

			}
			echo json_encode($wiki_categories_array);
		}


		public function category_ajax_save(Request $request)
		{

			$input = $request->all();

			$data = array('name' => $input['name']);
			$query = DB::table('wiki_category')->insert($data);
			echo $query;

		}


		public function save(Request $request)
		{

			$input = $request->all();
			$categories = $input['category'];

			//$category=implode(',',$input['category']);
			$date = date('Y-m-d');
			$data = array('user' => $input['user'], 'title' => $input['title'], 'description' => $input['discription'], 'keyword' => $input['keyword'], 'date' => $date, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now());
			$query = Wiki::create($data);
			$post_id = $query->id;
			foreach ($categories as $category) {

				$categories_id = DB::table('wiki_category')->select('id')->where('name', $category)->first();

				foreach ($categories_id as $category_id) $data = array('category_id' => $category_id, 'save_id' => $post_id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now());
				DB::table('wiki_save_category')->insert($data);
			}

			echo true;

		}

	}