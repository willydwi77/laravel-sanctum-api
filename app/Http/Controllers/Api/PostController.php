<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    public function index()
    {
        try {
            $posts = Auth::user()->posts()->latest()->get();

            if ($posts) {
                return $this->sendResponse($posts);
            } else {
                return $this->sendResponse('Belum ada Post yang dibuat');
            }            
        } catch (QueryException $err) {
            return $this->sendError(errorMessages: $err);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required'
            ], [
                'title.required' => 'Silahkan isi judul post!'
            ]);

            if ($validator->fails()) {
                return $this->sendError(errorMessages: $validator->errors());
            }

            $post = new Post();
            $post->title = $request->title;
            $post->body = $request->body;
            
            if ($request->photo != '') {
                // String atau Path
                $strpos = strpos($request->photo, ';');
                $sub = substr($request->photo, 0, $strpos);
                $explode = explode('/', $sub)[1];

                // Properti Image
                $image_name = time() . '.' . $explode;
                $image_size = Image::make($request->photo)->resize(1024, 768);
                $image_path = public_path() . '/upload/';

                // Unlink dan Save Image
                $image_size->save($image_path . $image_name);
                $post->photo = $image_name;
            } else {
                $post->photo = 'image.png';
            }

            if (Auth::user()->posts()->save($post)) {
                return $this->sendResponse(message: 'Berhasil menyimpan Post');
            } else {
                return $this->sendError(errorMessages: 'Gagal menyimpan Post');
            }
        } catch (QueryException $err) {
            return $this->sendError(errorMessages: $err);
        }
    }
    
    public function show($id)
    {
        try {
            $post = Post::find($id);

            if ($post) {
                return $this->sendResponse($post);
            } else {
                return $this->sendError(errorMessages: 'Post yang dimaksud tidak ditemukan');
            }
        } catch (QueryException $err) {
            return $this->sendError(errorMessages: $err);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required'
            ], [
                'title.required' => 'Silahkan isi judul post!'
            ]);

            if ($validator->fails()) {
                return $this->sendError(errorMessages: $validator->errors());
            }

            $post = Auth::user()->posts()->find($id);

            if ($post) {
                $post->title = $request->title;
                $post->body = $request->body;

                if ($request->photo != $post->photo) {
                    // String atau Path
                    $strpos = strpos($request->photo, ';');
                    $sub = substr($request->photo, 0, $strpos);
                    $explode = explode('/', $sub)[1];
                    
                    // Properti Image
                    $image_name = time() . '.' . $explode;
                    $image_size = Image::make($request->photo)->resize(1024, 768);
                    $image_path = public_path() . '/upload/';
                    $image_file = $image_path . $image_name;
                    
                    // Unlink dan Save Image
                    if (file_exists($image_file)) {
                        @unlink($image_file);
                    }

                    $image_size->save($image_file);
                    $post->photo = $image_name;
                } else {
                    $post->photo = 'image.png';
                }

                $update = $post->update();

                if ($update) {
                    return $this->sendResponse($post, 'Post berhasil diperbaharui');
                } else {
                    return $this->sendError(errorMessages: 'Gagal memperbaharui Post');
                }
            } else {
                return $this->sendError(errorMessages: 'Post yang dimaksud tidak ditemukan');
            }
        } catch (QueryException $err) {
            return $this->sendError(errorMessages: $err);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::find($id);

            if ($post) {
                $image_path = public_path() . '/upload/';
                $image_file = $image_path . $post->photo;

                if (file_exists($image_file)) {
                    @unlink($image_file);
                }

                $delete = $post->delete();

                if ($delete) {
                    return $this->sendResponse(message: 'Post berhasil dihapus');
                } else {
                    return $this->sendError(errorMessages: 'Gagal menghapus Post');
                }
            } else {
                return $this->sendError(errorMessages: 'Post yang dimaksud tidak ditemukan');
            }
        } catch (QueryException $err) {
            return $this->sendError(errorMessages: $err);
        }
    }
}
