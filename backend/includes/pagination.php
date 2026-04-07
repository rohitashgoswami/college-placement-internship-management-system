<?php

function get_current_page_number($default = 1)
{
    $page = isset($_GET['page']) ? (int) $_GET['page'] : $default;

    return ($page > 0) ? $page : $default;
}

function get_pagination_data($total_items, $per_page, $current_page)
{
    $total_items = max(0, (int) $total_items);
    $per_page = max(1, (int) $per_page);
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $current_page = min(max(1, (int) $current_page), $total_pages);

    return array(
        'total_items' => $total_items,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => ($current_page - 1) * $per_page
    );
}

function build_pagination_url($page)
{
    $params = $_GET;
    $params['page'] = $page;

    return '?' . http_build_query($params);
}

function render_pagination($pagination)
{
    if ($pagination['total_pages'] <= 1) {
        return;
    }

    echo '<div class="mt-6 flex flex-col gap-3 border-t pt-4 text-sm text-gray-600 md:flex-row md:items-center md:justify-between">';
    echo '<p>Page ' . $pagination['current_page'] . ' of ' . $pagination['total_pages'] . '</p>';
    echo '<div class="flex flex-wrap gap-2">';

    if ($pagination['current_page'] <= 1) {
        echo '<span class="rounded border border-gray-200 px-3 py-2 text-gray-400">Previous</span>';
    } else {
        echo '<a class="rounded border border-gray-300 px-3 py-2 hover:bg-gray-50" href="' . build_pagination_url($pagination['current_page'] - 1) . '">Previous</a>';
    }

    for ($page = 1; $page <= $pagination['total_pages']; $page++) {
        if ($page == $pagination['current_page']) {
            echo '<span class="rounded bg-blue-600 px-3 py-2 font-semibold text-white">' . $page . '</span>';
        } else {
            echo '<a class="rounded border border-gray-300 px-3 py-2 hover:bg-gray-50" href="' . build_pagination_url($page) . '">' . $page . '</a>';
        }
    }

    if ($pagination['current_page'] >= $pagination['total_pages']) {
        echo '<span class="rounded border border-gray-200 px-3 py-2 text-gray-400">Next</span>';
    } else {
        echo '<a class="rounded border border-gray-300 px-3 py-2 hover:bg-gray-50" href="' . build_pagination_url($pagination['current_page'] + 1) . '">Next</a>';
    }

    echo '</div>';
    echo '</div>';
}
?>
