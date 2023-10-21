<?php
// htaccess rule RewriteRule ^sitemap.xml$ think-client-service/scripts/filters_sitemap.php [NC,L]
require $_SERVER["DOCUMENT_ROOT"].'/think-client-service/config.php';
$SITE_MAIN_SITEMAP = 'https://'.$_SERVER['SERVER_NAME'].'/index.php?route=extension/feed/google_sitemap';
$sitemap_filters = unserialize(file_get_contents(THINK_CLIENT_DATA_PATH.'/index_filters.php'));
$sitemap_filters = array_values($sitemap_filters);
$URLS_PER_FILE = 45000;
$urls_count = count($sitemap_filters);
$sitemap_filters_files_count = ceil($urls_count/$URLS_PER_FILE);

$indexes_contents = '<?xml version="1.0" encoding="UTF-8"?>
    <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$indexes_contents .= '
    <sitemap>
        <loc>'.$SITE_MAIN_SITEMAP.'</loc>
    </sitemap>
';
$url_number = 0;
for ($i = 1; $i <= $sitemap_filters_files_count ; $i++) { 
    $indexes_contents .= '
        <sitemap>
            <loc>https://'.$_SERVER['SERVER_NAME'].'/think-client-service/data/sitemap_filters_xml/'.$_SERVER['SERVER_NAME'].'-sitemap-filters-'.$i.'.xml</loc>
        </sitemap>
    ';
    $sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
    $url_number_trigger = 1;    
    while ($url_number_trigger < $URLS_PER_FILE){
        $sitemap_content .= '
            <url>
                <loc>https://'.$_SERVER['SERVER_NAME'].htmlspecialchars($sitemap_filters[$url_number]).'</loc>
            </url>
        ';
        $url_number_trigger++;
        $url_number++;
        if ($url_number == $urls_count){
            break;
        }
    }
    $sitemap_content .= '</urlset>';
    
    file_put_contents(THINK_CLIENT_DATA_PATH.'/sitemap_filters_xml/'.$_SERVER['SERVER_NAME'].'-sitemap-filters-'.$i.'.xml', $sitemap_content);
}
$indexes_contents .= '
    </sitemapindex>
';
header("Content-type: text/xml; charset=utf-8");
echo $indexes_contents;