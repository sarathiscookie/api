<?php

namespace App\Http\Controllers\Admin;

use App\Country;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KeyInstructionRequest;
use App\Http\Traits\CountryTrait;
use App\KeyInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KeyInstructionController extends Controller
{
    use CountryTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $countries = $this->country();
        return view('admin.keyinstruction', ['countries' => $countries]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Admin\KeyInstructionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KeyInstructionRequest $request)
    {
        try {
            if( !empty($request->key_instruction_container_id) && !empty($request->key_instruction_language) ) {

                $countryCode         = Country::select('code')->find($request->key_instruction_language);

                // Path of directory.
                $path_of_directory   = 'key_instruction/'.(int)$request->key_instruction_container_id.'/'.$countryCode->code;

                // If directory doesn't exists create a new one.
                if( !Storage::exists($path_of_directory) ) {
                    $createDirectory = Storage::makeDirectory($path_of_directory, 0775);
                }

                // Getting and delete files from directory
                $files               = Storage::files($path_of_directory); 
                Storage::delete($files);

                // Storing new file in to folder.
                $path_of_file        = $request->file('key_instruction_file')->store($path_of_directory);

                // Storing data in to database
                $keyInstruction                   = New KeyInstruction;
                $keyInstruction->key_container_id = $request->key_instruction_container_id;
                $keyInstruction->country_id       = $request->key_instruction_language;
                $keyInstruction->instruction_url  = $path_of_file;
                $keyInstruction->save();

                return response()->json(['keyInstructionStatus' => 'success', 'message' => 'Well done! Key instruction created successfully'], 201);
            }
            else {
                return response()->json(['keyInstructionStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
            }
        } 
        catch(\Exception $e) {
            return response()->json(['keyInstructionStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
