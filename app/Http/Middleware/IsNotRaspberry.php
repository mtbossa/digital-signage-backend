<?php

namespace App\Http\Middleware;

use App\Models\Raspberry;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IsNotRaspberry
{
  /**
   * Handle an incoming request.
   *
   * @param  Request  $request
   * @param  Closure(Request): (Response|RedirectResponse)  $next
   * @return JsonResponse
   */
  public function handle(Request $request, Closure $next)
  {
    if ($request->user() instanceof Raspberry) {
      return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    return $next($request);
  }
}
