<?php
// Clear PHP opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!<br>";
} else {
    echo "OPcache is not enabled.<br>";
}

// Also clear any other caches
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache cleared!<br>";
}

echo "<br>Please restart your web server (Apache/Nginx) to ensure all caches are cleared.";
echo "<br><br><a href='create_campaign.php'>Go to Create Campaign</a>";
?>

