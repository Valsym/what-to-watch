<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UserRequest;
use App\Http\Responses\Success;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->success([]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        return $this->success(Auth::user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserRequest $request
     * @param $id
     * @return Success
     */
    public function update(UserRequest $request): Success
    {
        $params = $request->safe()->except('avatar');
        $user = Auth::user();
        $path = false;

        if($request->hasFile('avatar')) {
            $oldFile = $user->avatar;
            $result = $request->file('avatar')->store('avatars', 'public');
            $path = $result ? $request->file('avatar')->hashName() : false;
            $params['avatar'] = $path;
        }

//        $user = $user->update([
//            'name' => $request->name,
//            'email' => $request->email,
//            'file' => $request->file,
//        ]);
        $user->update($params);

        // Безопасное удаление старого файла
        if($path && !empty($oldFile)) {
            Storage::disk('public')->delete($oldFile);
        }

//        return $this->success($user, 201);
//        return $this->success(Auth::user()->makeVisible('email'));
        return $this->success($user->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->success([]);
    }
}
