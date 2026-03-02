<?php

namespace App\Http\Controllers\User\UserManagement;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\UserUpdateProfileRequest;
use App\Http\Requests\User\UserUpdateProfilePictureRequest;
use App\Http\Requests\User\UserUpdatePhotosRequest;
use App\Http\Requests\User\UserSetPrimaryPhotoRequest;
use App\Http\Requests\User\UserUpdatePersonalInfoRequest;
use App\Http\Requests\User\UserUpdateBusinessDetailRequest;
use App\Models\Zilmoney\PersonalInfo;
use App\Models\Zilmoney\BusinessDetail;

class UserController extends Controller
{
    // Show own profile
    public function profile(Request $request)
    {
        $user = $request->user(); // Authenticated user
        return new UserResource($user);
    }

    // Update own profile
    public function update(UserUpdateProfileRequest $request)
    {
        $user = $request->user();

        $user->fill($request->only([
            'name',
            'email',
            'notes'
        ]));

        $user->save();

        return response()->json([
            'data' => new UserResource($user),
            'Message' => 'Profile updated successfully',
            'isError' => false,
            'status_code' => 200
        ]);
    }

    public function updateProfilePicture(UserUpdateProfilePictureRequest $request)
    {
        $user = $request->user();

        // Unset previous primary photo
        $user->photos()->where('is_primary', true)->update(['is_primary' => false]);

        // Create new photo and set as primary
        $photo = $user->photos()->create([
            'path' => $request->photo_url,
            'is_primary' => true,
        ]);

        // Refresh the user to update the appended attribute
        $user->refresh();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'profile_picture' => $user->profile_picture
        ]);
    }

    public function updatePhotos(UserUpdatePhotosRequest $request)
    {
        $user = $request->user();

        $photosCreated = [];

        foreach ($request->photos as $photoUrl) {
            $photo = $user->photos()->create([
                'path' => $photoUrl,
                'is_primary' => false, // primary photo remains unchanged
            ]);

            $photosCreated[] = $photo;
        }

        return response()->json([
            'message' => 'Photos added successfully',
            'data' => $photosCreated,
        ]);
    }

    public function setPrimaryPhoto(UserSetPrimaryPhotoRequest $request)
    {
        $user = $request->user();

        $photoId = $request->photo_id;

        // Check if this photo belongs to the user
        $photo = $user->photos()->where('id', $photoId)->first();

        if (!$photo) {
            return response()->json([
                'message' => 'Photo not found or does not belong to you',
                'isError' => true,
                'status_code' => 404
            ], 404);
        }

        // Unset previous primary photo
        $user->photos()->where('is_primary', true)->update(['is_primary' => false]);

        // Set selected photo as primary
        $photo->is_primary = true;
        $photo->save();

        // Refresh user to update appended attribute
        $user->refresh();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'profile_picture' => $user->profile_picture,
            'data' => $photo
        ]);
    }

    public function updatePersonalInfo(UserUpdatePersonalInfoRequest $request)
    {
        $user = $request->user();

        // Update user model core fields if provided
        if ($request->hasAny(['first_name', 'last_name', 'display_name'])) {
            $user->update($request->only(['first_name', 'last_name', 'display_name']));
        }

        $personalInfo = $user->personalInfo()->updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return response()->json([
            'message' => 'Personal information updated successfully',
            'data' => $personalInfo
        ]);
    }

    public function updateBusinessDetail(UserUpdateBusinessDetailRequest $request)
    {
        $user = $request->user();
        
        $data = $request->validated()['business_details'];
        
        // Map frontend key to DB column
        if (isset($data['percentage_owner_ship'])) {
            $data['percentage_ownership'] = $data['percentage_owner_ship'];
            unset($data['percentage_owner_ship']);
        }
        
        // Ensure addresses are cast properly if sent as JSON string
        foreach(['physical_address', 'legal_registered_address'] as $addressField) {
            if (isset($data[$addressField]) && is_string($data[$addressField])) {
                $decoded = json_decode($data[$addressField], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$addressField] = $decoded;
                }
            }
        }

        $businessDetail = $user->businessDetails()->updateOrCreate(
            ['user_id' => $user->id],
            \Illuminate\Support\Arr::except($data, ['controllers']) // Exclude controllers from main model update
        );

        // Handle controllers associated with the business detail
        if (isset($data['controllers']) && is_array($data['controllers'])) {
            // Delete existing controllers to replace them
            $businessDetail->relatedControllers()->delete();
            
            foreach ($data['controllers'] as $controllerData) {
                // Map percentage ownership for the controller as well
                if (isset($controllerData['percentage_owner_ship'])) {
                    $controllerData['percentage_ownership'] = $controllerData['percentage_owner_ship'];
                    unset($controllerData['percentage_owner_ship']);
                }
                
                $businessDetail->relatedControllers()->create($controllerData);
            }
        }

        return response()->json([
            'message' => 'Business details updated successfully',
            // Return fresh instance with controllers loaded
            'data' => $businessDetail->load('relatedControllers')
        ]);
    }
}
