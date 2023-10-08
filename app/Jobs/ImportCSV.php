<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use App\Models\Import;
use App\Models\Product;
use Throwable;

use Illuminate\Support\Facades\Log;

class ImportCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $path;
    protected $importId;
    protected $originalFilename;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->path = $data['path'];
        $this->importId =  $data['importId'];
        $this->originalFilename =  $data['original_filename'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // check if the same file is currently processing
        $this->checkDuplicate();

        $import = Import::find($this->importId);
        $import->queue_start_at = date("Y-m-d H:i:s");
        $import->status = 3; //processing
        $import->save();

        $fileRelativePath = Storage::path($this->path);

        $row_count = 0;
        $this->batchInsert($fileRelativePath, $row_count); //the task seems suited to use insert, but i would consider using load infile for better performance.
        //$this->loadInfile($fileRelativePath);

        $import->updated_at = date("Y-m-d H:i:s");
        $import->queue_end_at = date("Y-m-d H:i:s");
        $import->status = 1; //completed
        $import->row_count = $row_count;
        $import->save();

        Storage::delete($this->path);
    }

    public function failed(Throwable $exception): void
    {
        Log::debug('failed = '.$exception->getMessage());
        $import = Import::find($this->importId);
        $import->updated_at = date("Y-m-d H:i:s");
        $import->queue_end_at = date("Y-m-d H:i:s");
        $import->status = 4;
        $import->save();
    }

    private function checkDuplicate(){
        //have to think of a better solution if there's 2 or more duplicate, as this will not guarantee running in FIFO order.
        $currentlyProcessing = Import::where(['original_filename' => $this->originalFilename, 'status' => '3'])->first();
        if(isset($currentlyProcessing)){
            sleep(5);
            checkDuplicate();
        }
    }

    private function batchInsert($fileRelativePath, &$row_count){
        $header = null;
        $data = array();
        $row_count = 0;
        $regex = "/[^(\\x20-\\x7F\\n)]+/u";
        if (($handle = fopen($fileRelativePath, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 2048, ',')) !== false)
            {
                foreach($row as $index => $value){
                    $row[$index] = preg_replace($regex, '', $value);
                }
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);

                $row_count++;

                //data clean up to prevent memory exhaustion from bloated array
                //also prevents reaching mysql placeholder limit of 65535
                if($row_count % 1500 === 0){
                    Product::upsert($data, ['UNIQUE_KEY']);
                    $data = NULL; //instantly free memory
                }
            }
            fclose($handle);

            if(count($data) > 0){
                Product::upsert($data, ['UNIQUE_KEY']);
            }
        }
    }

    private function loadInfile($fileRelativePath){
        DB::statement("LOAD DATA LOCAL INFILE '$fileRelativePath'
        REPLACE INTO TABLE products 
        CHARACTER SET UTF8 
        FIELDS TERMINATED BY ','
        ENCLOSED BY '\"' LINES TERMINATED BY '\n'
        (UNIQUE_KEY, PRODUCT_TITLE, PRODUCT_DESCRIPTION, `STYLE#`, AVAILABLE_SIZES, BRAND_LOGO_IMAGE, THUMBNAIL_IMAGE, COLOR_SWATCH_IMAGE, PRODUCT_IMAGE, SPEC_SHEET, PRICE_TEXT, SUGGESTED_PRICE, CATEGORY_NAME, SUBCATEGORY_NAME, COLOR_NAME, COLOR_SQUARE_IMAGE, COLOR_PRODUCT_IMAGE, COLOR_PRODUCT_IMAGE_THUMBNAIL, SIZE, QTY, PIECE_WEIGHT, PIECE_PRICE, DOZENS_PRICE, CASE_PRICE, PRICE_GROUP, CASE_SIZE, INVENTORY_KEY, SIZE_INDEX, SANMAR_MAINFRAME_COLOR, MILL, PRODUCT_STATUS, COMPANION_STYLES, MSRP, MAP_PRICING, FRONT_MODEL_IMAGE_URL, BACK_MODEL_IMAGE, FRONT_FLAT_IMAGE, BACK_FLAT_IMAGE, PRODUCT_MEASUREMENTS, PMS_COLOR, GTIN);");
    }
}
