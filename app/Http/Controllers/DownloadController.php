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
		$result = $filter->all();	 
		$filename = 'CSV-'.date('YmdHis').'.csv';
		Storage::disk('local')->put('files/'.$filename, $this->getCSV($result));
		echo $filename.' successfully generated!';
	}

	function getCSV($data, $delimiter = ',', $enclosure = '"') 
	{
		$contents = '';
		$handle = fopen('php://temp', 'r+');
		foreach ($data as $line) {
			unset($line['#'], $line['ORDER_NO'], $line['CUSTOMER'], $line['STATUS'], $line['SKU'], $line['DISCOUNT'], $line['SUBTOTAL']);
			$line['MONTH'] = Carbon::createFromFormat('Y-m-d H:i:s', $line['ORDER_CREATED_AT'])->format('F');
			$line['YEAR'] = Carbon::createFromFormat('Y-m-d H:i:s', $line['ORDER_CREATED_AT'])->format('Y');
			unset($line['ORDER_CREATED_AT']);
			fputcsv($handle, $line, $delimiter, $enclosure);
		}
		rewind($handle);
		while (!feof($handle)) {
			$contents .= fread($handle, 8192);
		}
		fclose($handle);
		return $contents;
	}

}
