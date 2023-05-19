<?php
/*
Plugin Name: Stratis-Plugin
Description:A custom plugin form with title and text associated to a shortcode [my_article_form] to create draft articles.
Version: 1.0.0
Author: Bedhief Najeh
*/

// Enqueue necessary scripts
function my_article_plugin_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('my-article-plugin-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0', true);
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js');
    // Pass the AJAX URL to the JavaScript file
    wp_localize_script('my-article-plugin-script', 'myArticleAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'my_article_plugin_scripts');

// Enqueue necessary styles
function custom_form_enqueue_styles() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
    wp_enqueue_style('custom-form-plugin-styles', plugins_url('assets/css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'custom_form_enqueue_styles');

// Register the shortcode for the form
function my_article_plugin_form_shortcode() {
    ob_start();
    ?>
    <form id="my-article-form" method="POST" class="bg-light px-5 py-5 rounded">
    <div id="my-article-response"></div>
    <p class="fs-2 fw-bold text-center">Shortcode Form</p>
        <label for="title">Titre:</label>
        <input type="text" class="form-control" name="title" id="title" required><br>
        <label for="text" class="mt-1">Texte:</label>
        <textarea class="form-control" name="text" id="text" required></textarea><br>
        <button type="submit" value="Submit" class="btn btn-success stratis-form-submit-button mt-2 text-capitalize">Soumettre</button>
    </form>  
    <?php
    return ob_get_clean();
}
add_shortcode('my_article_form', 'my_article_plugin_form_shortcode');

// Process form submission
function my_article_plugin_process_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve title and text field values
        $title = sanitize_text_field($_POST['title']);
        $text = sanitize_textarea_field($_POST['text']);
         // Check if an article with the same title already exists
         $existing_post = get_page_by_title($title, OBJECT, 'post');
         if ($existing_post) {
             echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
             <span class="fs-4">Un article avec le même titre existe déjà. Veuillez choisir un titre différent.</span>
   <button type="button" class="btn-close fw-10 w-10 h-10" data-bs-dismiss="alert" aria-label="Close"></button>
 </div>';
         } else {
             // Create a new unpublished article(draft)
             $new_post = array(
                 'post_title' => $title,
                 'post_content' => $text,
                 'post_status' => 'draft',
                 'post_type' => 'post'
             );
             $post_id = wp_insert_post($new_post);
             
            if ($post_id) {
                // Send an email to the administrator
                $admin_email = get_option('admin_email');
                $subject = 'Nouveau message créé depuis le formulaire';
                $message = 'Titre : ' . $title . '\n\nTexte : ' . $text;
                $result=wp_mail($admin_email, $subject, $message);
                if ($result){
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <span class="fs-4">Votre article a été créé avec succès et un email a été envoyé !</span>
          <button type="button" class="btn-close fw-10 w-10 h-10" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
                }
                else{
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <span class="fs-4">Votre article a été créé avec succès mais mail non envoyé !</span>
          <button type="button" class="btn-close fw-10 w-10 h-10" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
                }
                
            }    
            else {
                wp_send_json_error('Une erreur s\'est produite lors de la création de l\'article.');
            } 
         }

        exit; // Stop further processing
    }
}
add_action('wp_ajax_my_article_process_form', 'my_article_plugin_process_form');
add_action('wp_ajax_nopriv_my_article_process_form', 'my_article_plugin_process_form');