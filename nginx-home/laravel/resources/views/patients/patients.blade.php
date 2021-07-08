<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Patient List') }}
        </h2>
    </x-slot>

<!-- This is supplemental component for CSS and JS for the RIS migration pages -->
<!-- https://datatables.net/ -->
<x-myjs/>
<div class="container mt-5">
    <table class="table table-bordered yajra-datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>DOB</th>
                <th>Sex</th>
                <th>MRN</th>
                <th>Phone 1</th>
                <th>Phone 2</th>
                <th>e-mail 1</th>
                <th>e-mail 2</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script type="text/javascript">

  $(function () {
    
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('patients/datatable') }}",
            type: 'POST'
        },
        
        columns: [
            {data: 'id', name: 'id'},
            {data: 'last', name: 'last'},
            {data: 'first', name: 'first'},
            {data: 'birth_date', name: 'birth_date'},
            {data: 'sex', name: 'sex'},
            {data: 'mrn', name: 'mrn'},
            {data: 'mobile_phone', name: 'mobile_phone'},
            {data: 'alt_mobile_phone', name: 'alt_mobile_phone'},
            {data: 'email', name: 'email'},
            {data: 'alt_email', name: 'alt_email'},
            {
                data: 'action', 
                name: 'action', 
                orderable: true, 
                searchable: true
            },
        ],
        "lengthMenu": [ 2,5,10, 25, 50, 75, 100 ]
    });
    
  });
</script>


</x-app-layout>

<script>


$('body').on('click', '.place_patient_order, .modifyorder', function(e) {

	e.preventDefault();
    e.stopImmediatePropagation();
	PlaceModifyOrder($(this));

});

function PlaceModifyOrder(clicked, callback = false) {

    let wrapper = clicked.closest('div');
    let extradata;
    let accession = wrapper.data("accession"); // only applies for order worklist and an existing order.
    let mrn = wrapper.data("mrn");
    let ordertype = wrapper.data("ordertype");  // from the listpatients view form or from the orders tables, the element clicked.
    extradata = {"mrn": mrn, "accession": accession, "ordertype": ordertype};

    $.ajax({
    	url: '/orders/orderform',
    	type: 'GET',
    	dataType: 'html',
    	data: extradata,
    	complete: function(xhr, textStatus) {
    	},
    	success: function(data, textStatus, xhr) {


    	    	$('#modalDiv').html(data);
    	    	$('#modalDiv').show();
    	    	$("html, body").animate({ scrollTop: 0 }, "slow");
    	    	$("body").css("overflow", "hidden");

    	    	$('#closeorderoverlay').on('click', function() {  // could just trigger a click on Show Orders ?

//      	    	    if (accession != undefined) {
//      	    	    replaceOrderRow(accession);
//      	    	    }
//      	    	    if($("#orderswrapper").length == 0) { // on the patients page
//
//     	    	    row.find(".showorders").trigger("click");
//     	    	    getOrderCount(row);
//     	    	    }
                    $('#modalDiv').html("");
                    $("body").css("overflow", "auto");
                    $('#modalDiv').hide();
                    $("html, body").animate({scrollTop: 0}, 500);


                });

                $('#modalDiv input').focusout(function(e){  // dynamically updates jquery validate
                    $(this).valid();
                });

                attachDateTimePicker();
                attachSumoSelect('#modalDiv');
                submitorderhandler();  // attaches the submitorderhandler, below.

//                 if ($("#calendaryear").length != 0) {  // load the calendar appointments for the month and year, if the calendar is open.
//                     loadappointments($("#calendaryear").data("year"), $("#calendarmonth").data("month"), "01", "");
//                 }
                $('#scheduled_procedure_step_start_date').on ("change", function() {

                	// if the calendar is open then scroll the calendar to the date and view.
                	if ($("#calmainwrapper").length == 1) {

                	date = $('#scheduled_procedure_step_start_date').val();
					if (isValidDate(date) && typeof scrollCalendar == 'function') {
                	dateobject = splitDate(date);
                    scrollCalendar(dateobject.year, dateobject.month, dateobject.day, "" ,"");  // view is grabbed from session, callback is empty
                    //showCalDay(date);
                    }
                    }
                });

                if (typeof callback === "function") {
                	completeOrderProcess();
                }
    	}
    });
}

</script>
