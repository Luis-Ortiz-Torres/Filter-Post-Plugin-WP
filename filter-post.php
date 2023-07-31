<?php
/*
Plugin Name: Filter Posts By Categories
Description: Plugin personalizado para listar posts y filtrar por categorías.
Version: 1.0
Author: Luis Ortiz
*/

// Shortcode para listar posts y filtrar por categorías
function mi_shortcode_listar_posts()
{
    ob_start();
    wp_enqueue_style('mi_filter_post_style', plugin_dir_url(__FILE__) . 'css/filter-post-style.css');
?>
    <div class="fp-lista-posts">

        <form class="contenedor-select" id="mi-filtro-categorias">
            <?php
            // Obtener todas las categorías (incluyendo las que no tienen posts asociados)
            $categories = get_terms(array(
                'taxonomy' => 'category',
                'hide_empty' => false,
            ));

            // IDs de las categorías a excluir (puedes agregar más si es necesario)
            $excluded_categories = array(1, 10, 15, 11, 12, 13, 14 );
            ?>
            <div class="fp-filter">
                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                    <path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z" />
                </svg>
                <select class="fp-select" id="mi-categoria" name="mi-categoria">
                    <?php
                    foreach ($categories as $category) {
                        // Verificar si la categoría actual no está en la lista de excluidas
                        if (!in_array($category->term_id, $excluded_categories)) {
                            $selected = ($category->term_id === 9) ? 'selected' : '';
                            echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </form>
        <div id="mi-posts-container">
            <!-- Aquí se mostrarán los posts filtrados por categorías -->
        </div>
    </div>

    <script>
        (function($) {
            // Función para cargar los posts filtrados por categoría
            function cargarPostsPorCategoria(categoriaId) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    data: {
                        action: 'mi_cargar_posts_por_categoria',
                        categoria_id: categoriaId
                    },
                    success: function(response) {
                        $('#mi-posts-container').html(response);
                    }
                });
            }

            // Cargar los posts de la categoría por defecto al cargar la página
            $(document).ready(function() {
                var categoriaId = $('#mi-categoria').val();
                cargarPostsPorCategoria(categoriaId);
            });

            // Detectar cambios en el dropdown de categorías
            $('#mi-categoria').on('change', function() {
                var categoriaId = $(this).val();
                cargarPostsPorCategoria(categoriaId);
            });
        })(jQuery);
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('mi_listado_posts', 'mi_shortcode_listar_posts');

// Función para cargar posts filtrados por categoría usando AJAX
add_action('wp_ajax_mi_cargar_posts_por_categoria', 'mi_cargar_posts_por_categoria');
add_action('wp_ajax_nopriv_mi_cargar_posts_por_categoria', 'mi_cargar_posts_por_categoria');

function mi_cargar_posts_por_categoria()
{
    $categoria_id = $_POST['categoria_id'];

    // Si se selecciona la opción "Todas las categorías," obtenemos todos los posts
    if ($categoria_id == 0) {
        $args = array('post_type' => 'post', 'posts_per_page' => -1);
    } else {
        // Si se selecciona una categoría específica, obtenemos los posts de esa categoría
        $args = array(
            'post_type' => 'post',
            'cat' => $categoria_id,
            'posts_per_page' => -1
        );
    }

    $posts = get_posts($args);

    if ($posts) {
        foreach ($posts as $post) {
            // Obtenemos la imagen destacada (thumbnail) del post
            // Obtenemos la imagen destacada (full-size) del post
            $image_id = get_post_thumbnail_id($post->ID);
            $image_url = wp_get_attachment_image_src($image_id, 'full')[0];

            echo '<div class="fp-post-container">';
            // Mostramos la imagen destacada (si existe)
            if ($image_url) {
                echo '<div class="fp-image"><img src="' . $image_url . '" alt="' . esc_attr($post->post_title) . '"></div>';
            }
            // Mostramos el título y el extracto del post
            echo '<div class="fp-post-contenido">';
            echo '<h2 class="fp-titulo">' . $post->post_title . '</h2>';
            echo '<p class="fp-resumen">' . $post->post_excerpt . '</p>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No se encontraron Colaboradores en este País.</p>';
    }

    wp_die();
}
