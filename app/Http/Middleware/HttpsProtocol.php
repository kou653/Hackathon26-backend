<?php
namespace App\Http\Middleware;

use Closure;

class HttpsProtocol {

    public function handle($request, Closure $next)
    {
        $url = $request->url();

        if (env("APP_ENV") === "production") {

            // Vérifie d'abord si la clé existe
            $proto = $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? null;

            if ($proto === "http") {
                // Redirige vers HTTPS
                $secure_url = preg_replace("/^http:/i", "https:", $url);
                return redirect($secure_url);
            }
        }

        return $next($request);
    }
}
?>