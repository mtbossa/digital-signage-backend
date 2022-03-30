<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    return Post::all();
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  Request  $request
   * @return Response
   */
  public function store(Request $request)
  {
    $request->validate([
      'description' => ['required', 'string', 'max:100'],
      'start_date' => [
        'nullable', 'date_format:Y-m-d', 'required_with:end_date'
      ],
      'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date', 'required_with:start_date'],
      'start_time' => ['required', 'date_format:H:i:s'],
      'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],
    ]);
    return Post::create($request->all());
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show(Post $post)
  {
    return $post;
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  Request  $request
   * @param  int  $id
   * @return Response
   */
  public function update(Request $request, Post $post)
  {
    $post->update($request->all());

    return $post;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy(Post $post)
  {
    return $post->delete();
  }
}
