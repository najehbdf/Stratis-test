jQuery(document).ready(function($) {
    $('#my-article-form').submit(function(e) {
        e.preventDefault(); // Prevent form submission
        var formData = $(this).serialize();
        $.ajax({
            url: myArticleAjax.ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: formData + '&action=my_article_process_form', // Include the custom AJAX action
            success: function(response) {
                $('#my-article-response').html(response);
                $('#my-article-form')[0].reset();
            }
        });
    });
});
