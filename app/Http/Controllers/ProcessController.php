<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
// use JWTAuth;
// use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use DB, Hash, Mail;
use Illuminate\Mail\Message;
use Response;

class ProcessController extends Controller
{
	public function seoName($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

    public function store_file(Request $request){
    	$validator = Validator::make($request->all(), [
            'username' => 'required|max:50',
            'email' => 'required|email|max:50',
            'image' => 'required|image'
        ]);

        if ($validator->fails()) {
			return Response::json(['code' => '401', 'status' => false, 'message' => $validator->errors()->all()], 401);
		}

		$username = $request->username;
		$email = $request->email;
		$image = $request->image;

		$file_extension = $request->image['file_extension'];
        $file_tmp = $request->image['file_tmp'];
        $file_name = $request->image['file_name'];
        $new_file_name = $this->seoName($request->image).'_'.uniqid().'.'.$file_extension;
        $file_tmp->move(base_path('public\images\stored'), $new_file_name);

        try{
	        DB::insert("INSERT INTO tb_data_file
	        	SET username = '$username',
	        		email = '$email',
	        		file_image = '$new_file_name'");

	        return Response::json(['code' => '200', 'status' => true,
				'message' => 'Berhasil Simpan File'
            ], 200);

        } catch(\Illuminate\Database\QueryException $e){ 
		  	dd($e->getMessage()); 
		  	
		  	return Response::json(['code' => '401', 'status' => true,
				'message' => 'Terjadi kesalahan saat simpan. Periksa kembali'
            ], 200);
		}
    }
}
