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

$(document).ready(function() {
    // Перехватываем отправку формы логина
    $('form[action="login.php"]').on('submit', function(e) {
        e.preventDefault(); // Останавливаем стандартный переход на другую страницу

        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: $(this).serialize(), // Собираем логин и пароль из полей
            success: function(response) {
                if(response.status === 'success') {
                    // Перезагружаем текущую страницу, чтобы показать блок профиля
                    window.location.reload();
                } else {
                    alert('Ошибка: ' + response.message);
                }
            }
        });
    });

    // То же самое для формы регистрации
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
                    alert('Ошибка: ' + response.message);
                }
            }
        });
    });
});