<?php
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="none" />
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Many libraries are are Via Laravel Mix in /resources/js/app.js && /resources/sass/app.scss -->
        <link rel="icon" href="data:;base64,iVBORw0KGgo=">
        <link rel="stylesheet" href="/bower/jquery-ui/themes/dark-hive/jquery-ui.min.css" type="text/css">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
        <link rel="stylesheet" href="{{ asset('css/my.css') }}">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.24/b-1.7.0/cr-1.5.3/date-1.0.2/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.css"/>
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/bower/jquery-timepicker-jt/jquery.timepicker.min.css" type="text/css">
        <?php $nonce = ["nonce" => csp_nonce()] ?>
        @livewireStyles($nonce)
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
        <script src="/bower/jquery/dist/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.0.1/jquery-migrate.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
        <script src="/bower/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="/bower/jquery-ui/jquery-ui.min.js"></script>
        <script src="/bower/jquery-timepicker-jt/jquery.timepicker.min.js"></script>
        <script src="/bower/moment/min/moment.min.js"></script>
        <script src="/bower/moment-timezone/builds/moment-timezone-with-data-1970-2030.min.js"></script>
        <script src="/bower/jquery-validation/dist/jquery.validate.min.js"></script>
        <script src="/bower/jquery-validation/dist/additional-methods.min.js"></script>
        <script src="/bower/sumoselect/jquery.sumoselect.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.24/b-1.7.0/cr-1.5.3/date-1.0.2/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
        <script src="{{ asset('js/app.js') }}"></script>
        <script nonce= "{{ csp_nonce() }}">
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                beforeSend: function(xhr) {
                    $("body").addClass("loading");
                    // xhr.setRequestHeader('custom-header', 'some value');
                },
            });
        </script>
        
    </head>
    <body class="font-sans antialiased">
        <div class="spinner_overlay"></div>
        <div id= "modalDiv"></div>
        <div class="min-h-screen bg-gray-100">
            <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex-shrink-0 flex items-center">
                                <a href="/">
                                    <x-jet-application-mark class="block h-9 w-auto" />
                                    <img style = "height:40px;" src="{{ asset('images/mylogo.png') }}">
                                </a>
                            </div>
                            
                                                        
                            @if (Auth::user()->getPermissionNames()->contains('admin_data'))
                            
                            
                            <!-- Admin Dropdown -->
                            <div class="hidden sm:flex sm:items-center sm:ml-6 <?php echo (request()->routeIs('admin/dashboard') || request()->routeIs('devtool')) ?'border-b-2 border-indigo-400':'' ?>">
                                <x-jet-dropdown align="left" width="48">
                                    <x-slot name="trigger">
                                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                            {{ __('ADMIN') }}
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <!-- Back Panel -->

                                        <x-jet-dropdown-link href="/admin/dashboard">
                                            {{ __('Back Panel') }}
                                        </x-jet-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Dev Tool -->
                                        <x-jet-dropdown-link href="/devtool">
                                            {{ __('Dev Tool') }}
                                        </x-jet-dropdown-link>
                                    </x-slot>
                                </x-jet-dropdown>
                            </div>
                            @endif
                            
                            @if (Auth::user()->getPermissionNames()->contains('staff_data'))
                            <!-- RIS Dropdown -->
                            <div class="hidden sm:flex sm:items-center sm:ml-6">
                                <x-jet-dropdown align="left" width="48">
                                    <x-slot name="trigger">
                                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                            {{ __('RIS') }}
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <!-- List Patients -->

                                        <x-jet-dropdown-link href="/patients/patients">
                                            {{ __('List Patients') }}
                                        </x-jet-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Placeholder2 -->
                                        <x-jet-dropdown-link href="/user/profile">
                                            {{ __('Placeholder2') }}
                                        </x-jet-dropdown-link>


                                    </x-slot>
                                </x-jet-dropdown>
                            </div>
                             @endif
                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/dashboard" :active="request()->routeIs('dashboard')">
                                    {{ __('Dashboard') }}
                                </x-jet-nav-link>
                            </div>
                            
                    
                            @if (Auth::user()->getPermissionNames()->contains('reader_data'))
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/readers_studies" :active="request()->routeIs('readers_studies')">
                                    {{ __('Readers') }}
                                </x-jet-nav-link>
                            </div>
                            @endif
                            
                            
                            @if (Auth::user()->getPermissionNames()->contains('provider_data'))
                            <!-- Provider Dropdown -->
                            <div class="hidden sm:flex sm:items-center sm:ml-6 <?php echo (request()->routeIs('referrers_studies') || request()->routeIs('shared_studies') || request()->routeIs('referrers_profile') || request()->routeIs('referrers_placeorder') ) ?'border-b-2 border-indigo-400':'' ?>">
                                <x-jet-dropdown align="left" width="48">
                                    <x-slot name="trigger">
                                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out" >
                                            {{ __('Providers') }}
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <!-- Study List -->

                                        <x-jet-dropdown-link href="/referrers_studies" :class="request()->routeIs('referrers_studies')?'activemenu':''">
                                            {{ __('Study List') }}
                                        </x-jet-dropdown-link>
                                        
                                        <x-jet-dropdown-link href="/shared_studies" :class="request()->routeIs('shared_studies')?'activemenu':''">
                                            {{ __('Shared Studies') }}
                                        </x-jet-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Profile -->
                                        <x-jet-dropdown-link href="/referrers_profile" :class="request()->routeIs('referrers_profile')?'activemenu':''">
                                            {{ __('Profile') }}
                                        </x-jet-dropdown-link>
                                        
                                        <div class="border-t border-gray-100"></div>

                                        <!-- Orders & Requests -->
                                        <x-jet-dropdown-link href="/referrers_placeorder" :class="request()->routeIs('referrers_placeorder')?'activemenu':''">
                                            {{ __('Orders & Requests') }}
                                        </x-jet-dropdown-link>

                                    </x-slot>
                                </x-jet-dropdown>
                            </div>
                            @endif
                            
                            @if (Auth::user()->getPermissionNames()->contains('patient_data'))
                            
                            <!-- Patient Dropdown -->
                            <div class="hidden sm:flex sm:items-center sm:ml-6 <?php echo (request()->routeIs('patientportal/studies') || request()->routeIs('patientportal/profile')) ?'border-b-2 border-indigo-400':'' ?>">
                                <x-jet-dropdown align="left" width="48">
                                    <x-slot name="trigger">
                                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out" >
                                            {{ __('Patients') }}
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <!-- Study List -->

                                        <x-jet-dropdown-link href="/patientportal/studies" :class="request()->routeIs('patientportal/studies')?'activemenu':''">
                                            {{ __('Study List') }}
                                        </x-jet-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Profile -->
                                        <x-jet-dropdown-link href="/patientportal/profile" :class="request()->routeIs('patientportal/profile')?'activemenu':''">
                                            {{ __('Profile') }}
                                        </x-jet-dropdown-link>
                                        
                                        <div class="border-t border-gray-100"></div>

                                        <!-- Orders -->
                                        <x-jet-dropdown-link href="/patientportal/orders" :class="request()->routeIs('patientportal/orders')?'activemenu':''">
                                            {{ __('Orders') }}
                                        </x-jet-dropdown-link>
                                        
                                       <!-- Documents -->
                                        <x-jet-dropdown-link href="/patientportal/documents" :class="request()->routeIs('patientportal/documents')?'activemenu':''">
                                            {{ __('Documents') }}
                                        </x-jet-dropdown-link>

                                    </x-slot>
                                </x-jet-dropdown>
                            </div>
                            @endif

                    </div>

                        <!-- Settings Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6" style ="min-width:48px;">
                            <x-jet-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <!-- Account Management -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Manage Account') }}
                                    </div>

                                    <x-jet-dropdown-link href="/user/profile">
                                        {{ __('Profile') }}
                                    </x-jet-dropdown-link>

                                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                        <x-jet-dropdown-link href="/user/api-tokens">
                                            {{ __('API Tokens') }}
                                        </x-jet-dropdown-link>
                                    @endif

                                    <div class="border-t border-gray-100"></div>

                                    <!-- Team Management -->
                                    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            {{ __('Manage Team') }}
                                        </div>
                                        @if (isset(Auth::user()->currentTeam))
                                        <!-- Team Settings -->
                                        <x-jet-dropdown-link href="/teams/{{ Auth::user()->currentTeam->id }}">
                                            {{ __('Team Settings') }}
                                        </x-jet-dropdown-link>
                                        @endif
                                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                            <x-jet-dropdown-link href="/teams/create">
                                                {{ __('Create New Team') }}
                                            </x-jet-dropdown-link>
                                        @endcan

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Team Switcher -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            {{ __('Switch Teams') }}
                                        </div>

                                        @foreach (Auth::user()->allTeams() as $team)
                                            <x-jet-switchable-team :team="$team" />
                                        @endforeach

                                        <div class="border-t border-gray-100"></div>
                                    @endif

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-jet-dropdown-link href="{{ route('logout') }}"
                                                            onclick="event.preventDefault();
                                                                     this.closest('form').submit();">
                                            {{ __('Logout') }}
                                        </x-jet-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-jet-dropdown>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <x-jet-responsive-nav-link href="/dashboard" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-jet-responsive-nav-link>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div class="flex-shrink-0">
                                <img class="h-10 w-10 rounded-full" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            </div>

                            <div class="ml-3">
                                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <!-- Account Management -->
                            <x-jet-responsive-nav-link href="/user/profile" :active="request()->routeIs('profile.show')">
                                {{ __('Profile') }}
                            </x-jet-responsive-nav-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-jet-responsive-nav-link href="/user/api-tokens" :active="request()->routeIs('api-tokens.index')">
                                    {{ __('API Tokens') }}
                                </x-jet-responsive-nav-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-jet-responsive-nav-link href="{{ route('logout') }}"
                                                onclick="event.preventDefault();
                                                         this.closest('form').submit();">
                                    {{ __('Logout') }}
                                </x-jet-responsive-nav-link>
                            </form>

                            <!-- Team Management -->
                            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                                <div class="border-t border-gray-200"></div>

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Manage Team') }}
                                </div>
                                @if (isset(Auth::user()->currentTeam))
                                <!-- Team Settings -->
                                <x-jet-responsive-nav-link href="/teams/{{ Auth::user()->currentTeam->id }}" :active="request()->routeIs('teams.show')">
                                    {{ __('Team Settings') }}
                                </x-jet-responsive-nav-link>
                                @endif
                                <x-jet-responsive-nav-link href="/teams/create" :active="request()->routeIs('teams.create')">
                                    {{ __('Create New Team') }}
                                </x-jet-responsive-nav-link>

                                <div class="border-t border-gray-200"></div>

                                <!-- Team Switcher -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Switch Teams') }}
                                </div>

                                @foreach (Auth::user()->allTeams() as $team)
                                    <x-jet-switchable-team :team="$team" component="jet-responsive-nav-link" />
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <x-AppFooter />
        @stack('modals')
        @livewireScripts($nonce)
        
<!-- The Modal -->
<div class="modal fade hide" id="myModal" data-keyboard="true" data-backdrop="true" tabindex='-1'>

    <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body"></div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="uibuttonsmallred" data-dismiss="modal">Close</button>
      </div>

    </div>
    </div>
</div>
<?php
$modal_message = session('modal_message', false);
if ($modal_message !== false) {
?>
    <script nonce= "{{ csp_nonce() }}">
    $(window).on('load', function() {
        $('#myModal .modal-body').html('<?php echo $modal_message ?>');
        $('#myModal').modal('show');
    });
    </script>
<?php
}
Session::forget('modal_message'); 
?>

    </body>
</html>