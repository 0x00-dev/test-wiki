$(document).ready(()=>{
    $('a').on('click', function (e) {
        if ($(this).data('q') === true) {
            e.preventDefault();
            let url = $(this).attr('href');
            if (confirm('Удалить страницу?')) {
                window.location = url;
            }
        }
    });
});