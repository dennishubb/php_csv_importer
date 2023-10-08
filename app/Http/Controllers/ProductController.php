<?php

namespace App\Http\Controllers;
use Illuminate\Http\Response;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Import;
use App\Jobs\ImportCSV;

use Illuminate\Support\Facades\Log;

class ProductController extends Controller{

    public function importCSV(Request $request)
    {
        $path = $request->file('file')->store('imports');
        $relativePath = Storage::path($path);

        $import = new Import;
        $import->original_filename =  $request->file('file')->getClientOriginalName();
        $import->stored_filename = $path;
        $import->filesize = Storage::size($path);
        $import->type = Storage::mimeType($path);
        $import->save();

        Log::debug($import->original_filename);
        $data['path'] = $path;
        $data['importId'] = $import->id;
        $data['original_filename'] = $import->original_filename;
        dispatch((new ImportCSV($data)));

        return Controller::response(200, 'success', '');
    }

    public function getImport(Request $request){
        $statusMap = [
            '1' => 'completed',
            '2' => 'pending',
            '3' => 'processing',
            '4' => 'failed'
        ];

        $imports = Import::select('created_at', 'original_filename', 'status')->orderByDesc('id')->get()->toArray();

        foreach($imports as $key => $value){
            $imports[$key]['status'] = $statusMap[$value['status']];
        }

        return Controller::response(200, '', $imports);
    }
}

?>