<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class IndexController extends Controller
{
    public function Index() {
        return view('Frontend.index');
    }

    public function UserLogout() {
        Auth::logout();
        return Redirect()->route('login');
    }

    public function UserProfile() {
        $id = Auth::user()->id;
        $user = User::find($id);
        return view('frontend.profile.user_profile',compact('user'));
    }

    public function UserProfileStore(Request $request) {
        $data = User::find(Auth::user()->id);
        //dd($data);
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;


        //dd($request->file('profile_photo_path'));

        if($request->file('profile_photo_path')) {
            $file = $request-> file('profile_photo_path');
            @unlink(public_path('upload/user_images/'.$data->profile_photo_path));
            $filename = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/user_images'),$filename);
            $data['profile_photo_path'] = $filename;
        }
        $data->save();

        $notification = [
            'message' => "User Profile updated successfully",
            'alert-type' => 'sucess'
        ];

        return redirect()->route('dashboard')->with($notification);
    } // end method

    public function UserChangePassword() {
        $id = Auth::user()->id;
        $user = User::find($id);
        return view('frontend.profile.change_password',compact('user'));
    }

    public function UserPasswordUpdate(Request $request) {

        $validatedData = $request->validate([
            'oldpassword' => 'required',
            'password' => 'required|confirmed',
        ]);

        $hashedPassword = Auth::user()->password;
        if (Hash::check($request->oldpassword,$hashedPassword)) {
            $admin = User::find(Auth::id());
            $admin->password = Hash::make($request->password);
            $admin->save();
            Auth::logout();
            return redirect()->route('user.logout');
        }else{
            return redirect()->back();
        }

    }

}
