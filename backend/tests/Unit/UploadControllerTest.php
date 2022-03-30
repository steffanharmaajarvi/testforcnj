<?php

namespace Tests\Unit;

use App\Models\Housing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadControllerTest extends TestCase
{
    /**
     * Check if file is uploaded and response is success
     *
     * @return void
     */
    public function testFileUploading()
    {

        Housing::query()->truncate();

        $fileContent = Storage::disk('local')->get('test.csv');
        $file = UploadedFile::fake()->createWithContent('test.csv', $fileContent);

        $response = $this->post('/api/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
    }

    /**
     * Check if rows were added if option SaveToDatabase is enabled
     *
     * @return void
     */
    public function testAddRowsIfOptionEnabled()
    {

        Housing::query()->truncate();

        $fileContent = Storage::disk('local')->get('test.csv');
        $file = UploadedFile::fake()->createWithContent('test.csv', $fileContent);

        $response = $this->post('/api/upload?saveToDatabase=true', [
            'file' => $file,
        ]);

        $jsonResponse = $response->json();

        $response->assertStatus(200);
        $this->assertEmpty($jsonResponse['errors'] ?? null);

        $allRows = Housing::all();

        $this->assertEquals(5, $allRows->count());
        $this->assertSame('1995-01-01', $allRows[0]->date);
        $this->assertSame('city of london', $allRows[0]->area);
        $this->assertEquals(91449, $allRows[0]->average_price);
        $this->assertSame('E09000001', $allRows[0]->code);
        $this->assertEquals(17, $allRows[0]->houses_sold);
        $this->assertEquals(null, $allRows[0]->crimes_count);
        $this->assertEquals(true, $allRows[0]->borough_flag);

    }


    /**
     * Check if rows weren't added if option SaveToDatabase is disabled
     *
     * @return void
     */
    public function testNotAddRowsIfOptionDisabled()
    {

        Housing::query()->truncate();

        $fileContent = Storage::disk('local')->get('test.csv');
        $file = UploadedFile::fake()->createWithContent('test.csv', $fileContent);

        $response = $this->post('/api/upload?saveToDatabase=false', [
            'file' => $file,
        ]);

        $jsonResponse = $response->json();

        $response->assertStatus(200);
        $this->assertEmpty($jsonResponse['errors'] ?? null);

        $allRows = Housing::all();

        $this->assertEquals(0, $allRows->count());
    }
}
