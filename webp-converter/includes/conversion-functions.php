<?php

// Получение списка файлов
function webp_converter_list_files($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'])) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

// Конвертация изображения в WebP с помощью cwebp
function webp_converter_convert($relative_path) {
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . $relative_path;
    $plugin_dir = plugin_dir_path(__FILE__);
    $cwebp_path = $plugin_dir . '../cwebp'; // путь к утилите cwebp в директории плагина

    if (!file_exists($file_path)) {
        echo '<div class="notice notice-error"><p>Файл не найден: ' . esc_html($relative_path) . '</p></div>';
        return;
    }

    // Изменение размера изображения до 1500 пикселей по ширине
    $image = wp_get_image_editor($file_path);
    if (!is_wp_error($image)) {
        $image->resize(1500, null);
        $resized_file_path = $upload_dir['basedir'] . '/resized_' . basename($file_path);
        $image->save($resized_file_path);

        // Создаем временное имя файла
        $temp_file_path = $upload_dir['basedir'] . '/temp_image_' . uniqid() . '.' . pathinfo($resized_file_path, PATHINFO_EXTENSION);
        copy($resized_file_path, $temp_file_path);

        $dest_file = $upload_dir['basedir'] . str_replace(['.jpg', '.jpeg', '.png'], '.webp', $relative_path);
        $temp_dest_file = $upload_dir['basedir'] . '/temp_image_' . uniqid() . '.webp';

        // Команда для конвертации в WebP с экранированием путей
        $command = escapeshellcmd("$cwebp_path -q 80 ") . escapeshellarg($temp_file_path) . " -o " . escapeshellarg($temp_dest_file);

        // Выполнение команды
        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var !== 0) {
            echo '<div class="notice notice-error"><p>Не удалось сохранить изображение в формате WebP: ' . esc_html($relative_path) . '</p>';
            echo '<p>Команда: ' . esc_html($command) . '</p>';
            echo '<p>Вывод: ' . implode('<br>', array_map('esc_html', $output)) . '</p>';
            echo '<p>Код возврата: ' . esc_html($return_var) . '</p></div>';

            // Удаляем временные файлы
            unlink($temp_file_path);
            unlink($resized_file_path);
            if (file_exists($temp_dest_file)) {
                unlink($temp_dest_file);
            }
            return;
        }

        // Перемещаем временный webp файл на место назначения
        rename($temp_dest_file, $dest_file);

        // Удаляем временные файлы
        unlink($temp_file_path);
        unlink($resized_file_path);

        echo '<div class="notice notice-success"><p>Изображение успешно конвертировано в WebP: ' . esc_html($dest_file) . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Ошибка при изменении размера изображения: ' . esc_html($relative_path) . '</p></div>';
    }
}
