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
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Many libraries are are Via Laravel Mix in /resources/js/app.js && /resources/sass/app.scss -->
    
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
        <link rel="stylesheet" href="{{ asset('css/my.css') }}">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.24/b-1.7.0/cr-1.5.3/date-1.0.2/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.css"/>
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/bower/jquery-ui/themes/dark-hive/jquery-ui.min.css" type="text/css">

        @livewireStyles
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.2.1/dist/alpine.js" defer></script>

        <script src="{{ asset('js/app.js') }}"></script>

        <script src="/bower/moment/min/moment.min.js"></script>
        <script src="/bower/moment-timezone/builds/moment-timezone-with-data-1970-2030.min.js"></script>

        <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.24/b-1.7.0/cr-1.5.3/date-1.0.2/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    
        <script>
    
        $(function() {
    
            $(document).ajaxComplete(function( event, request, settings ) {

                $("body").removeClass("loading");
                console.log("Request");
                console.log(request);
                console.log("settings");
                console.log(settings);
                console.log("event");
                console.log(event);
            });
    
            $.ajaxSetup({

                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                beforeSend: function(xhr) {
                    $("body").addClass("loading");
                    // xhr.setRequestHeader('custom-header', 'some value');
                },
            });
        });
        </script>
        
    </head>
    <body class="font-sans antialiased">
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
                                <?php echo Auth::user()->getPermissionNames(); ?>
                            </div>
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
                                        <!-- Placeholder -->

                                        <x-jet-dropdown-link href="/patients/patients">
                                            {{ __('List Patients') }}
                                        </x-jet-dropdown-link>

    <!--
                                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                            <x-jet-dropdown-link href="/user/api-tokens">
                                                {{ __('API Tokens') }}
                                            </x-jet-dropdown-link>
                                        @endif
     -->

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
                            @if (Auth::user()->getPermissionNames()->contains('admin_data'))
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/devtool" :active="request()->routeIs('devtool')">
                                    {{ __('Dev / Admin Tool') }}
                                </x-jet-nav-link>
                            </div>
                            @endif
                    
                            @if (Auth::user()->getPermissionNames()->contains('reader_data'))
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/readers" :active="request()->routeIs('readers')">
                                    {{ __('Readers') }}
                                </x-jet-nav-link>
                            </div>
                            @endif
                            @if (Auth::user()->getPermissionNames()->contains('provider_data'))
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/referrers_studies" :active="request()->routeIs('referrers_studies')">
                                    {{ __('Referring Provider\'s Study List') }}
                                </x-jet-nav-link>
                            </div>
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/referrers_profile" :active="request()->routeIs('referrers_profile')">
                                    {{ __('Provider Profile') }}
                                </x-jet-nav-link>
                            </div>
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/referrers_placeorder" :active="request()->routeIs('referrers_placeorder')">
                                    {{ __('Order Request') }}
                                </x-jet-nav-link>
                            </div>
                            @endif
                            @if (Auth::user()->getPermissionNames()->contains('patient_data'))
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/patientportal/studies" :active="request()->routeIs('patientportal/studies')">
                                    {{ __('Patient Study List') }}
                                </x-jet-nav-link>
                            </div>
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link href="/patientportal/profile" :active="request()->routeIs('patientportal/profile')">
                                    {{ __('Patient Profile') }}
                                </x-jet-nav-link>
                            </div>
                            @endif
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-jet-nav-link target="_blank" href="ohifstudylist" :active="request()->routeIs('ohifstudylist')">
                                    {{ __('OHIF DEMO / ') }}
                                </x-jet-nav-link>
                            </div>

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

                                        <!-- Team Settings -->
                                        <x-jet-dropdown-link href="/teams/{{ Auth::user()->currentTeam->id }}">
                                            {{ __('Team Settings') }}
                                        </x-jet-dropdown-link>

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

                                <!-- Team Settings -->
                                <x-jet-responsive-nav-link href="/teams/{{ Auth::user()->currentTeam->id }}" :active="request()->routeIs('teams.show')">
                                    {{ __('Team Settings') }}
                                </x-jet-responsive-nav-link>

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
         <x-appfooter/>
        @stack('modals')

        @livewireScripts
 <x-modal id="vertically-centered" title="Vertically centered" :centered="true">
        <x-slot name="body"></x-slot>
        <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </x-slot>
    </x-modal>
    </body>
    
<?php
  $modal_message = session('modal_message', false);
  if ($modal_message !== false) {
  ?>

    <script type="text/javascript">
    $(window).on('load', function() {
        $('#vertically-centered .modal-body').html('<?php echo $modal_message ?>');
        $('#vertically-centered').modal('show');
    });
    </script>

    <?php
    }
    ?>
    <script>
    
        $(".privacy-policy, .terms-of-service").on("click", function(e) {
        e.preventDefault();
        
            $.ajax({

            type: "GET",
            url: $(this).attr("href"),
            dataType: "html",
            data: {},

        })
        .done(function(data, textStatus, jqXHR) {
            $('#vertically-centered .modal-body').html(data);
            $('#vertically-centered').modal('show');
        });
        });
        </script>
</html>