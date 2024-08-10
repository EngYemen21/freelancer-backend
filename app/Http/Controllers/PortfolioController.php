<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {


        $portfolio = Portfolio::get();

        return response()->json($portfolio);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        if (is_null($request)) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            // 'thumbnail' => 'max:2048',
            'image' => 'required|image|max:2048',
            'link' => 'url',
        ]);

        $user = auth()->user();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $imagePath =  $image->move('Portfolioimages', $filename);
        } else {
            throw new Exception('Error uploading image. Please ensure a valid image is selected.', 422); // 422: Unprocessable Entity
        }
        $portfolio = Portfolio::create([
            'title' => $validatedData['title'],
            'user_id' => $user->id,
            'description' => $validatedData['description'],
            'thumbnail' => $imagePath,
            'image' => $imagePath,
            'url' => $validatedData['link'],
        ]);


        return response()->json($portfolio, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $portfolio = Portfolio::where('user_id', $id)->get();
        return response()->json($portfolio);
        if (!$portfolio) {
            return response()->json(['error' => 'Portfolio not found'], 404);
        }
        return response()->json($portfolio, 200);
    }


    public function showDetails(string $id)
    {
        $portfolio = Portfolio::find($id);
        if (!$portfolio) {
            return response()->json(['error' => 'Portfolio not found'], 404);
        }
        return response()->json($portfolio, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        // dd($request->input());
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable',
            'image' => 'nullable',
            'link' => 'url',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $imagePath =  $image->move('Portfolioimages', $filename);
        } else {
            throw new Exception('Error uploading image. Please ensure a valid image is selected.', 422); // 422: Unprocessable Entity
        }


        $record = Portfolio::findOrFail($id);

        if (!$record) {
            return response()->json(['errors' => 'Portfolio not found'], 404);
        }





        try {
            $record->title = $validatedData['title'];
            $record->description = $validatedData['description'];

            $record->thumbnail = $imagePath;
            $record->image = $imagePath;
            $record->url = $validatedData['link'];
            $record->save();
            // return response()->json(['message'=>"تم تحديث بيانات العمل بنجاح"], 200);

            return response()->json([
                'message' => 'Portfolio deleted successfully.',
                'status' => 200,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Portfolio not found.',
                'status' => 404,
            ], 404);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Error deleting Portfolio.',
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $deletePortfolio = Portfolio::findOrFail($id);


        try {
            $deletePortfolio->delete();

            return response()->json([
                'message' => 'Portfolio deleted successfully.',
                'status' => 200,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Portfolio not found.',
                'status' => 404,
            ], 404);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Error deleting Portfolio.',
                'status' => 500,
            ], 500);
        }
    }
}
