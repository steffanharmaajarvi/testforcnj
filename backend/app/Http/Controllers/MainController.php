<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Models\Housing;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;

class MainController extends BaseAPIController
{

    /**
     * Uploads the CSV and parses it
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function upload(FileUploadRequest $request): JsonResponse
    {

        if (!$request->validated()) {
            return $this->failure([
                'errors' => $request->messages()
            ]);
        }

        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');

        $rows = array_map('str_getcsv', file($filePath));

        foreach ($rows as $row) {
            if (
                Housing::where('date', Carbon::parse($row[0]))->first()
            ) {
                return $this->failure([
                    'errors' => 'Date already exists'
                ]);
            }

            $housing = new Housing();
            $housing->date = Carbon::parse($row[0]);
            $housing->area = $row[1];
            $housing->average_price = $row[2];
            $housing->code = $row[3];
            $housing->houses_sold = $row[4];
            $housing->crimes_count = $row[5];
            $housing->borough_flag = $row[6];

            $housing->save();
        }

        return $this->success();
    }

    public function all(): JsonResponse
    {

        $averageOfAllPrices = Housing::all()->avg('average_price');
        $totalHousesSold = Housing::all()->sum('houses_sold');
        $numberOfCrimesIn2011 = Housing::all()->sum('crimes_count');

        $allYearsAveragePrice = Housing::all()->get('average_price');

        return $this->success([
            'averageAll' => $averageOfAllPrices,
            'totalHousesSold' => $totalHousesSold,
            'numberOfCrimesIn2011' => $numberOfCrimesIn2011,
            'allYearsAveragePrice' => $allYearsAveragePrice
         ]);

    }

}
