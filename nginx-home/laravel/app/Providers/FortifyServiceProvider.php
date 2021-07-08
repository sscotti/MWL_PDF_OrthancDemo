<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

// Added SDS, to intercept Authentication and perform additional tasks

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Helpers\ReallySimpleJWT;
use Illuminate\Validation\ValidationException;
// use Illuminate\Support\Facades\Route;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        
        Fortify::authenticateUsing(function (Request $request) {
        
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                if (!isset($_COOKIE['laravel_cookie_consent'])) {

                    Log::info("laravel_cookie_consent not set");
                    throw ValidationException::withMessages([
                        Fortify::username() => "You Have Not consented to the Cookie Policy",
                    ]);
//                     session(['modal_message' => 'You Have Not consented to the Cookie Policy']);
//                     redirect()->route('welcome');

                }
        
                else {
                
                    //  Gets here upon passing Login request
                    
                
                    $token = ReallySimpleJWT::Get_JWT_String("TESTNAME", ["test" => "test"]); // will return a JWT token string
                    $jwt =  ReallySimpleJWT::get_ObjectFromString($token);
                    Log::info("Valid:  " . ReallySimpleJWT::ValidateTokenString($token)?"Valid":"Not Valid");
                    $parsed = ReallySimpleJWT::ParseTokenObject( $jwt );
                    Log::info("Cookie JWT set in Redirect If Authenticated:");
                    Log::info($parsed->getHeader());
                    Log::info($parsed->getPayload());
                
                    Log::info("Laravel Cookie is Set, Setting JWT Cookie");
                    ReallySimpleJWT::Set_JWT_Cookie("LaravelRads", $token);
                    // Old user roles
                    $user_roles = json_decode($user->user_roles);

                    if (in_array(1, $user_roles)) {
                        $user->givePermissionTo('patient_data');
                    }
                    else {
                        $user->revokePermissionTo('patient_data');
                    }
                                        
                    if (in_array(2, $user_roles)) {
                        $user->givePermissionTo('provider_data');
                    }
                    else {
                        $user->revokePermissionTo('provider_data');
                    }
                    
                    if (in_array(3, $user_roles)) {
                        $user->givePermissionTo('reader_data');       
                    }
                    else {
                        $user->revokePermissionTo('reader_data');
                    }
                    
                    if (count(array_intersect([4,5,6], $user_roles)) > 0) {
                        $user->givePermissionTo('staff_data');
                    }
                    else { 
                        $user->revokePermissionTo('staff_data');
                    }
                                      
                    if (count(array_intersect([7,8], $user_roles)) > 0) {
                        $user->givePermissionTo('admin_data');
                    }
                    else {
                        $user->revokePermissionTo('admin_data');
                    }

//                     echo $user->two_factor_secret;

                    return $user;
        
                }
                
            }
        });
    }
}
