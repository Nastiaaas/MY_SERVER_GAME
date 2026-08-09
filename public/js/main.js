$(document).ready(function() {
    $('#showRegister').click(function(e) {
        e.preventDefault();
        $('#login').hide();
        $('#register').show();
    });

    $('#showLogin').click(function(e) {
        e.preventDefault();
        $('#register').hide();
        $('#login').show();
    });
});
