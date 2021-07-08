<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Rules\Password;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use App\Models\Referrers\ReferringPhysician;
use App\Models\Patients\Patients;
use Illuminate\Support\Facades\Log;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
    
//   '_token' => '1KLIhLY4NEokKcihsCPPG58z3lVFaqJImOZ3yKg8',
//   'name' => 'Admin',
//   'email' => '',
//   'password' => '',
//   'password_confirmation' => '',
//   'user_type' => 'register_provider',
//   'terms' => 'on',

        // Modified SDS
        
        if ($input['terms'] != "on") {
        
            throw ValidationException::withMessages([
                    0 => "You Did Not agree to the Terms and Policies"
            ]);
            exit(); 
        }

        if ($input['user_type'] == "register_provider") {
        
            $referrer = ReferringPhysician::where('email', $input['email'])->first();
            // This has to be unqique given the constraints on the DB.
            if (!$referrer) {
                throw ValidationException::withMessages([
                        0 => "You are not registered as a provider with the practice under that e-mail."
                ]);
                exit();
            }

            Validator::make($input, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password, 'confirmed'],
            ])->validate();
        
            $input['fname'] = $referrer->fname;
            $input['lname'] = $referrer->lname;
            $input['mname'] = $referrer->mname;
            $input['doctor_id'] = $referrer->identifier;

            return DB::transaction(function () use ($input)  {
        
                return tap(User::create([
                    'name' => $input['name'],
                    'fname' =>  $input['fname'],
                    'lname' =>  $input['lname'],
                    'mname' =>  $input['mname'],
                    'doctor_id' => $input['doctor_id'],
                    'user_roles' => json_encode([2]),
                    'email' => $input['email'],
                    'password' => Hash::make($input['password']),
                ]), function (User $user) {
                    $user->givePermissionTo('provider_data');
                    $this->createTeam($user);
                });
            });
        }
        
        else if ($input['user_type'] == "register_patient") {
        
            $patient = Patients::where('email', $input['email'])->first();
            
            // This has to be unqique given the constraints on the DB.
            if (!$patient) {
            
                throw ValidationException::withMessages([
                        0 => "You are not registered as a patient with the practice under that e-mail."
                ]);
                exit();
            }

            Validator::make($input, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password, 'confirmed'],
            ])->validate();
        
            $input['fname'] = $patient->first;
            $input['lname'] = $patient->last;
            $input['mname'] = $patient->mname;
            $input['patientid'] = $patient->mrn;

            return DB::transaction(function () use ($input)  {
        
                return tap(User::create([
                    'name' => $input['name'],
                    'fname' =>  $input['fname'],
                    'lname' =>  $input['lname'],
                    'mname' =>  $input['mname'],
                    'patientid' => $input['patientid'],
                    'user_roles' => json_encode([1]),
                    'email' => $input['email'],
                    'password' => Hash::make($input['password']),
                ]), function (User $user) {
                    $user->givePermissionTo('patient_data');
                    $this->createTeam($user);
                });
            });
        }
        else if ($input['user_type'] == "register_patient") {
        
            throw ValidationException::withMessages([
                    0 => "User type not specified"
            ]);
            exit();
        }
    }

    /**
     * Create a personal team for the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createTeam(User $user)
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
