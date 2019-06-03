<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use Carbon\Carbon; 

class DownloadController extends Controller
{
	public function getIndex()
	{
		$json = Storage::disk('local')->get('orders-20190602.json');
		$collection = json_decode($json, true);
		$orders = collect($collection['orders']);
		$filter = $orders->where('STATUS', 'delivered')->where('SUBTOTAL', '>', 20);
		$groups = $filter->groupBy('AGENT')->map(function ($agent) {
			return $agent->groupBy('PRODUCT')->map(function ($product) {
				return $product->groupBy(function ($ym) {
					return Carbon::createFromFormat('Y-m-d H:i:s', $ym['ORDER_CREATED_AT'])->format('Y-m');
				})->map(function ($year_month) {
					return $year_month->sum('TOTAL_AWARD_POINT');
				});
			});
		});
		$result = $groups->all();
		$filename = 'CSV-'.date('YmdHis').'.csv';
		Storage::disk('local')->put('files/'.$filename, $this->getCSV($result));
		echo $filename.' successfully generated!';
	}

	function getCSV($data, $delimiter = ',', $enclosure = '"') 
	{
		$contents = '';
		$handle = fopen('php://temp', 'r+');
		$header = array('Agent', 'Product', 'Year', 'Month', 'Total Award Point');
		fputcsv($handle, $header);
		$line = [];
		foreach ($data as $name => $value) {
			$line['name'] = $name;
			if (!empty($value)) {
				foreach ($value as $item => $year_month) {
					$line['product'] = $item;
					if (!empty($year_month)) {
						foreach ($year_month as $ym => $total) {
							$line['year'] = Carbon::createFromFormat('Y-m', $ym)->format('Y');
							$line['month'] = Carbon::createFromFormat('Y-m', $ym)->format('F');
							$line['total'] = $total;
							fputcsv($handle, $line, $delimiter, $enclosure);
						}
					}
				}
			}
		}
		rewind($handle);
		while (!feof($handle)) {
			$contents .= fread($handle, 8192);
		}
		fclose($handle);
		return $contents;
	}

}
