<?php

namespace App\Http\Middleware;

use Closure;

class PhpIniIncreaseTimeMemory
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		ini_set('max_execution_time', 3600); //60 min
        ini_set('memory_limit','1G');
        set_time_limit(0);

        // before middleware work

        $response=$next($request);

		// after  middleware work

        return $response;
    }
}
