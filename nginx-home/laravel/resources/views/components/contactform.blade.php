   <style nonce="{{ csp_nonce() }}">
   #contactinfo {
	width: 400px;
	margin: 20px auto;
	color:black;
	}
	#contactinfo label {
	    color:white;
	}
	</style>

   <div class="container mt-5">

        <!-- Success message -->
        @if(Session::has('success'))
            <div class="alert alert-success">
                {{Session::get('success')}}
            </div>
        @endif

        <form id = "contactform" method="post" action="/sendmail">

            <!-- CROSS Site Request Forgery Protection -->
            @csrf

            <div>
                <x-jet-label value="Email" />
                <x-jet-input class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div>
                <x-jet-label value="Name" />
                <x-jet-input class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            </div>

            <div>
                <x-jet-label value="Phone" />
                <x-jet-input class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autofocus />
            </div>

            <div>
                <x-jet-label value="Subject" />
                <x-jet-input class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required autofocus />
            </div>

             <div>
                <x-jet-label value="Message" />
                <x-jet-input class="block mt-1 w-full" type="text" name="message" :value="old('message')" required autofocus />
            </div>

            <input type="submit" name="send" value="Submit" class="btn btn-dark btn-block">
        </form>
    </div>
