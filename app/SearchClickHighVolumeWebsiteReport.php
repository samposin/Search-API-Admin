<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SearchClickHighVolumeWebsiteReport extends Model
{

    protected $table = 'search_click_high_volume_website_report';
    protected $appends = array('last_thirty_days_clicks_csv_string');


    protected $fillable=[
        'total_clicks',
        'dl_source',
        'widget',
        'country_code',
        'domain',
        'date'
    ];


	/**
	 *
	 * This function return custom attribute as comma separated string containing past 30 days clicks
	 *
	 * @return string
	 *
	 */
	public function getLastThirtyDaysClicksCsvStringAttribute()
	{

	    $sparkline_str =$this->day29.",".$this->day28.",".$this->day27.",".$this->day26.",".$this->day25.",";
		$sparkline_str.=$this->day24.",".$this->day23.",".$this->day22.",".$this->day21.",".$this->day20.",";
		$sparkline_str.=$this->day19.",".$this->day18.",".$this->day17.",".$this->day16.",".$this->day15.",";
		$sparkline_str.=$this->day14.",".$this->day13.",".$this->day12.",".$this->day11.",".$this->day10.",";
		$sparkline_str.=$this->day9 .",".$this->day8 .",".$this->day7 .",".$this->day6 .",".$this->day5 .",";
		$sparkline_str.=$this->day4 .",".$this->day3 .",".$this->day2 .",".$this->day1 .",".$this->day0;

	    return $sparkline_str;
	}


	/**
	 *
	 * This function selects database fields and custom fields and apply where condition for date range
	 *
	 * @param $query
	 * @param $from_date
	 * @param $to_date
	 *
	 * @return mixed
	 */

	public function scopeSelectWhereDateRangeForAdminGrid($query,$from_date,$to_date)
	{

		return $query->select(
			array(
				'domain','widget','dl_source','country_code',
	            DB::raw('sum(total_clicks) as total_clicks'),
		        DB::raw('SUM(IF(date = "'.$to_date.'"                  , total_clicks, 0)) AS day0'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 1  DAY, total_clicks, 0)) AS day1'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 2  DAY, total_clicks, 0)) AS day2'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 3  DAY, total_clicks, 0)) AS day3'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 4  DAY, total_clicks, 0)) AS day4'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 5  DAY, total_clicks, 0)) AS day5'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 6  DAY, total_clicks, 0)) AS day6'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 7  DAY, total_clicks, 0)) AS day7'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 8  DAY, total_clicks, 0)) AS day8'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 9  DAY, total_clicks, 0)) AS day9'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 10 DAY, total_clicks, 0)) AS day10'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 11 DAY, total_clicks, 0)) AS day11'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 12 DAY, total_clicks, 0)) AS day12'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 13 DAY, total_clicks, 0)) AS day13'),
		        DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 14 DAY, total_clicks, 0)) AS day14'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 15 DAY, total_clicks, 0)) AS day15'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 16 DAY, total_clicks, 0)) AS day16'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 17 DAY, total_clicks, 0)) AS day17'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 18 DAY, total_clicks, 0)) AS day18'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 19 DAY, total_clicks, 0)) AS day19'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 20 DAY, total_clicks, 0)) AS day20'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 21 DAY, total_clicks, 0)) AS day21'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 22 DAY, total_clicks, 0)) AS day22'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 23 DAY, total_clicks, 0)) AS day23'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 24 DAY, total_clicks, 0)) AS day24'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 25 DAY, total_clicks, 0)) AS day25'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 26 DAY, total_clicks, 0)) AS day26'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 27 DAY, total_clicks, 0)) AS day27'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 28 DAY, total_clicks, 0)) AS day28'),
				DB::raw('SUM(IF(date = "'.$to_date.'" - INTERVAL 29 DAY, total_clicks, 0)) AS day29'),
			)
		)
		->where('date','>=',$from_date)
		->where('date','<=',$to_date);

	}
}
