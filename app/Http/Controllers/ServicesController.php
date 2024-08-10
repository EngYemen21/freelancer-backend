<?php

namespace App\Http\Controllers;

use Exception;
use Pusher\Pusher;
use App\Models\User;
use App\Models\Category;
use App\Models\Services;
// use Illuminate\Support\Facades\Request;
use App\Events\EventService;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreServicesRequest;
use App\Http\Requests\UpdateServicesRequest;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewNotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function filterService(Request $request)
    {
        $selectedCategories = $request->input('selectedCategories', []);
        $searchTerm = $request->input('searchTerm', '');
        $query = Services::query();
        if ($selectedCategories) {
            $query->whereIn('category', $selectedCategories);
        }
        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }
        $filteredServices = $query->get();
        return response()->json($filteredServices);
    }
    public function index()
    {
        $services = Services::with('users')->get();

        return response()->json($services);
    }
    public function getServicesPending()
    {
        $services = Services::where('status', 'pending')->get();

        return response()->json($services);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServicesRequest $request)
    {
        $validatedData = $request->validated();
        try {
            $service = new Services();
            $service->title = $validatedData['title'];
            $service->description = $validatedData['description'];
            $service->price = $validatedData['price'];
            $service->delivery_time = $validatedData['delivery_time'];
            $service->category_id = $validatedData['category_id'];

            $service->status = 'pending';
            $user = auth()->user();
            $service->user_id = Auth::user()->id;

            // Handle image upload
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $filename = time() . '_' . $image->getClientOriginalName();
                $imageUrl = $image->move('Image_Services', $filename);
                $service->image = $imageUrl;
            }

            $service->save();
            foreach ($validatedData['skills'] as $skill) {
                $service->skills()->create(['name' => $skill]);
            }
            return response()->json(['message' => 'Service created successfully!', 'data' => $service], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $service = Services::with('category')->findOrFail($id);
            $categories = Category::all();

            return response()->json([
                'service' => $service,
                'calculateOverallScore' => $service->calculateOverallScore(),
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch service'], 500);
        }
    }
    public function search(Request $request, $name)
    {
        $services = Services::with('users')->where('title', 'like', "%{$name}%")->get();

        return response()->json($services);
    }

    public function getUserServices($user_id)
    {

        try {
            $services = Services::where('user_id', $user_id)->get();

            return response()->json($services);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch services'], 500);
        }
    }

    public function sellerServicesWithRatings($id)
    {
        $services = Services::where('user_id', $id)
            ->with(['ratings.user', 'ratings.comments'])
            ->get();

        return response()->json($services);
    }
    public function filterServices(Request $request)
    {
        $categoryId = $request->input('categoryIds');

        if (!$categoryId) {
            return Services::with(['category', 'users'])->get()->select('id', 'title', 'image', 'description'); // Return all services if no category is selected
        }

        $categoryIds = explode(',', $categoryId);

        $services = Services::with(['category', 'users'])
            ->whereHas('category', function ($query) use ($categoryIds) {
                $query->whereIn('name', $categoryIds); // Filter by category names (multiple)
            })->get();


        return $services->select('id', 'title', 'price', 'image', 'description');
    }
    public function filterServicesCategory(Request $request, $categoryId)
    {


        $services = Services::where('category_id', $categoryId)
            ->with(['category', 'users'])
            ->get();



        return response()->json(['services' => $services]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServicesRequest $request, $id)
    {
        try {

            $validatedData = $request->validated();

            $service = Services::findOrFail($id);

            if ($request->hasFile('file')) {

                $image = $request->file('file');
                $filename = time() . '_' . $image->getClientOriginalName();
                $imageUrl =  $image->move('Image_Services', $filename);
                $service->image = $imageUrl;
            } else {
                throw new Exception('Error uploading image. Please ensure a valid image is selected.', 422); // 422: Unprocessable Entity
            }

            // Update service fields with validated data
            $service->update($validatedData);

            // Return success response with updated service data
            return response()->json(['message' => 'Service updated successfully!', 'data' => $service], 200);
        } catch (Exception $e) {
            // Handle potential errors (e.g., validation errors, database errors)
            return response()->json(['errors' => $e->getMessage()], $e->getCode());
        }
    }

    public function approve(Request $request, $id)
    {
        $service = Services::findOrFail($id);
        $service->status = 'approved';
        $service->save();
        return response()->json(['message' => 'تمت الموافقة على الخدمة بنجاح']);
    }


    public function reject(Request $request, $id)
    {
        $service = Services::findOrFail($id);
        $service->status = 'refund';
        $service->save();

        return response()->json(['message' => 'تم رفض الخدمة بنجاح']);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Services $services, int $id)
    {
        $serviceToDelete = $services->findOrFail($id);

        try {
            $serviceToDelete->delete();

            return response()->json([
                'message' => 'Service deleted successfully.',
                'status' => 200,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Service not found.',
                'status' => 404,
            ], 404);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Error deleting service.',
                'status' => 500,
            ], 500);
        }
    }
    public function getServiceRatingsComments($serviceId)
    {
        $service = Services::with('ratings.comments', 'ratings.user')->where('user_id', $serviceId);
        return response()->json($service);
    }
}
