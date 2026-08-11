$(document).ready(function() {
    $('#showRegister').click(function(e) {
        e.preventDefault();
        $('#login').addClass('hidden');
        $('#registration').removeClass('hidden');
    });

    $('#showLogin').click(function(e) {
        e.preventDefault();
        $('#registration').addClass('hidden');
        $('#login').removeClass('hidden');
    });
});

$(document).ready(function() {
    $('form[action="login.php"]').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    window.location.reload();
                } else {
                    alert('error: ' + response.message);
                }
            }
        });
    });
    $('form[action="register.php"]').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'register.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    window.location.reload();
                } else {
                    alert('error: ' + response.message);
                }
            }
        });
    });
});