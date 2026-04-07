<?php

function render_student_page_start($title)
{
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script>';
    echo '</head>';
    echo '<body class="bg-slate-100 flex">';
}

function render_student_flash()
{
    $flash = get_flash_message();

    if (!$flash) {
        return;
    }

    $styles = array(
        'success' => 'border-green-200 bg-green-50 text-green-700',
        'error' => 'border-red-200 bg-red-50 text-red-700',
        'info' => 'border-blue-200 bg-blue-50 text-blue-700'
    );

    $style = isset($styles[$flash['type']]) ? $styles[$flash['type']] : $styles['info'];

    echo '<div class="mb-6 rounded-xl border px-4 py-3 text-sm ' . $style . '">';
    echo htmlspecialchars($flash['message']);
    echo '</div>';
}
?>
