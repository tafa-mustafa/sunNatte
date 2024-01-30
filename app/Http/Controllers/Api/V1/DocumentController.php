<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Document;
use App\Models\Materiel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{

    public function all(Request $request){

        $document = Materiel::all();
      
       return response()->json($document);
    }

    public function doc_user(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'nom' => 'required|max:255',
                'user_id' => '',
                'document' => 'required',
            ]);



            if ($request->document && $request->document->isValid()) {

                $file_name = time() . '.' . $request->document->extension();
                $destinationPath = public_path('documents');
                $request->document->move($destinationPath, $file_name);
                $path = "documents/$file_name";
            }

        

            $user = Auth::user()->id;

            // crée une nouvelle instance de la classe docch
            $docch = new Document;
            $docch->nom = $validatedData['nom'];
            $docch->user_id = $user;
            $docch->document = $path;
            $docch->save();

            return response()->json($docch);
        } catch (\Exception $e) {

            return response()->json([

                'statut' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);

        }
    }


    public function materiel(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'nom' => 'required|max:255',
                'image' => 'required',
            ]);



            if ($request->image && $request->image->isValid()) {

                $file_name = time() . '.' . $request->image->extension();
                $destinationPath = public_path('materiels');
                $request->image->move($destinationPath, $file_name);
                $path = "materiels/$file_name";
            }




            // crée une nouvelle instance de la classe docch
            $docch = new Materiel;
            $docch->nom = $validatedData['nom'];
            $docch->image = $path;
            $docch->save();

            return response()->json($docch);
        } catch (\Exception $e) {

            return response()->json([

                'statut' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);

        }
    }
}
