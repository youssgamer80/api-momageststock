<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class RoleAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$roles){
    
        try {
        //Recuperation du token passer dans les entetes de l'URL        
        $token = JWTAuth::parseToken();

        //TOn essaye de connecter un utilisateur      
        $user = $token->authenticate();

        } catch (TokenExpiredException $e) {

        //Lancer une exception en cas de token expiré       
        return $this->unauthorized("Votre Token a expiré,Veuillez vous reconnecter svp.");

        }catch (TokenInvalidException $e) {

        //Lancer une exception en cas de token invalide
        return $this->unauthorized("Votre token n'est pas valide, Veuillez vous reconnecter svp");

    }catch (JWTException $e) {

        //Lancer une exception si le token n'est pas présent dasn l'entete de la requete
        return $this->unauthorized('Veuillez ajouter votre token de connexion avotre requete svp');
    }

    //On vérifie si l'utilisateur a reuissi a se connecter puis s'il a le role souhaiter
    //on lui donne acces a la ressource demande
    //dans le cas contraire on lance une exception

    if ($user && ($user->role=='admin' || $user->role=='user')) {

        return $next($request);
    }

    return $this->unauthorized();
}

//cette fonction permet de renvoyer un message d'erreur si l'utilisateur n'a pas le droit d'acces a la ressource
private function unauthorized($message = null){
    return response()->json([
        'message' => $message ? $message : "Vous n'etes pas autoriser a acceder a cette ressource",
        'success' => false
    ], 401);
}
}