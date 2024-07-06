<?php

// Добавление страницы в меню админки
function webp_converter_menu() {
    add_menu_page('WebP Converter', 'WebP Converter', 'manage_options', 'webp-converter', 'webp_converter_page');
}

// Отображение страницы плагина
function webp_converter_page() {
    ?>
    <div class="wrap md-container">
        <h1 class="md-heading">WebP Converter</h1>
        <?php
        if (isset($_POST['convert'])) {
            $files = isset($_POST['files']) ? $_POST['files'] : [];
            foreach ($files as $file) {
                webp_converter_convert($file);
            }
        }
        ?>
        <form method="post" action="" class="md-form">
            <div class="md-card">
                <h2 class="md-card-title">Выберите файлы для конвертации:</h2>
                <div class="md-card-content">
                    <?php webp_converter_display_uploads(); ?>
                </div>
            </div>
            <div class="md-form-actions">
                <input type="submit" name="convert" id="convert" class="md-button md-button-primary" value="Конвертировать в WebP">
            </div>
        </form>
    </div>
    <?php
}

// Отображение структуры папки uploads
function webp_converter_display_uploads() {
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'];
    $files = webp_converter_list_files($dir);
    $base_url = $upload_dir['baseurl'];

    echo '<ul class="md-file-list">';
    foreach ($files as $file) {
        $relative_path = str_replace($dir, '', $file);
        $webp_file = $upload_dir['basedir'] . str_replace(['.jpg', '.jpeg', '.png'], '.webp', $relative_path);
        $webp_url = $base_url . str_replace(['.jpg', '.jpeg', '.png'], '.webp', $relative_path);
        $converted_class = file_exists($webp_file) ? 'md-file-converted' : '';
        $webp_link = file_exists($webp_file) ? ' | <a href="' . esc_url($webp_url) . '" target="_blank">WebP</a>' : '';
        $delete_button = file_exists($webp_file) ? ' | <a href="#" class="md-link md-delete-webp" data-file="' . esc_attr(str_replace($upload_dir['basedir'], '', $webp_file)) . '">Удалить WEBP версию</a>' : '';
        echo '<li class="md-file-item ' . esc_attr($converted_class) . '"><label><input type="checkbox" name="files[]" value="' . esc_attr($relative_path) . '"> ' . esc_html($relative_path) . '</label>' . $webp_link . $delete_button . '</li>';
    }
    echo '</ul>';
}
