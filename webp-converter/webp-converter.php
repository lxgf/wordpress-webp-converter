<?php
/*
Plugin Name: WebP Converter
Description: Конвертация изображений в формат WebP.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Подключаем файлы плагина
require_once plugin_dir_path(__FILE__) . 'includes/conversion-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';

// Добавление страницы в меню админки
add_action('admin_menu', 'webp_converter_menu');

// Подключаем стили и скрипты
add_action('admin_enqueue_scripts', 'webp_converter_enqueue_assets');

function webp_converter_enqueue_assets() {
    wp_enqueue_style('webp-converter-styles', plugin_dir_url(__FILE__) . 'assets/styles.css');
    wp_enqueue_script('webp-converter-scripts', plugin_dir_url(__FILE__) . 'assets/scripts.js', array('jquery'), false, true);
    wp_localize_script('webp-converter-scripts', 'webpConverter', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

// Обработчик AJAX для удаления WebP файлов
add_action('wp_ajax_delete_webp_file', 'webp_converter_delete_webp_file');

function webp_converter_delete_webp_file() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Нет прав для выполнения данного действия.');
        return;
    }

    if (isset($_POST['file'])) {
        $file = sanitize_text_field($_POST['file']);
        $upload_dir = wp_upload_dir();
        $webp_file = $upload_dir['basedir'] . $file;

        if (file_exists($webp_file)) {
            unlink($webp_file);
            wp_send_json_success('WebP файл успешно удален.');
        } else {
            wp_send_json_error('WebP файл не найден.');
        }
    } else {
        wp_send_json_error('Файл не указан.');
    }
}
