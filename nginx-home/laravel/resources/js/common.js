function showmodal (title, body) {

    $("#myModal .modal-body").html(body);
    $("#myModal .modal-header").html(title);
    $('#myModal').modal('show');

}

function showMessage(title, message) {

    showmodal(title, message);

}
//  These have to be set to make them available in Global Scope.
window.showmodal = showmodal;
window.showMessage = showMessage;