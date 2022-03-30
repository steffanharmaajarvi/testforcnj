<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Models\Housing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Request;

ini_set('max_execution_time', 0);

class MainController extends BaseAPIController
{

    /**
     * Uploads the CSV and parses it
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function upload(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv|max:2048',
        ], [
            'file.required' => 'File upload is required',
            'file.mimes' => 'Allowed file types: csc',
            'file.max' => 'Maximum file size is 2GB'
        ]);

        if ($validator->fails()) {
            return $this->failure([
                'errors' => $validator->messages()->all()
            ]);
        }

        $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');

        $rows = array_map('str_getcsv', file(Storage::disk('public')->path($filePath)));

        $saveToDatabase = $request->input('saveToDatabase');

        if ($saveToDatabase !== 'true') return $this->success();

        $allDates = array_column(Housing::all('date')->toArray(), 'date');

        array_shift($rows);

        $dataToInsert = [];
        foreach ($rows as $row) {
            if (
                in_array($row[0], $allDates)
                || !$row[0]
            ) {
                continue;
            }

            $dataToInsert[] = [
                'date' => Carbon::parse($row[0]),
                'area' => (isset($row[1])) ? (string) $row[1] : '',
                'average_price' => (isset($row[2])) ? (int) $row[2] : 0,
                'code' => (isset($row[3])) ? (string) $row[3] : '',
                'houses_sold' => (isset($row[4])) ? (int) $row[4] : 0,
                'crimes_count' => (isset($row[5])) ? (int) $row[5] : 0,
                'borough_flag' => (isset($row[6])) ? $row[6] : 0
            ];
        }
        foreach (array_chunk($dataToInsert,1000) as $t)
        {
            DB::table('housings')->insert($t);
        }

        return $this->success();
    }

    /**
     * Get all rows and averages
     *
     * @return JsonResponse
     */
    public function all(): JsonResponse
    {

        $averageOfAllPrices = ceil(Housing::all()->avg('average_price'));
        $totalHousesSold = Housing::all()->sum('houses_sold');
        $numberOfCrimesIn2011 = Housing::all()->sum('crimes_count');

        $allYearsAveragePrice = [];

        Housing::all()->each(function ($item) use (&$allYearsAveragePrice) {
            $year =  Carbon::parse($item['date'])->format('Y');
            if (!isset($allYearsAveragePrice[$year])) {
                $allYearsAveragePrice[$year] = 0;
            } else {
                $allYearsAveragePrice[$year] += $item['average_price'];
            }
        });

        return $this->success([
            'averageAll' => $averageOfAllPrices,
            'totalHousesSold' => $totalHousesSold,
            'numberOfCrimesIn2011' => $numberOfCrimesIn2011,
            'allYearsAveragePrice' => $allYearsAveragePrice
         ]);

    }

}
