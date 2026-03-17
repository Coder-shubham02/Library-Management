<?php
// config/helpers.php
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . htmlspecialchars(http_build_query($params));
}
?>