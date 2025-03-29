<?php
header('Content-Type: application/json');

// Function to get the IPv4 address of the Wi-Fi adapter
function getWiFiIPv4Address() {
    $output = [];
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Run ipconfig on Windows
        exec('ipconfig', $output);
        $wifiBlock = false;
        foreach ($output as $line) {
            // Look for the Wi-Fi section
            if (strpos($line, 'Wireless LAN adapter Wi-Fi') !== false) {
                $wifiBlock = true;
            } elseif ($wifiBlock && strpos($line, 'IPv4 Address') !== false) {
                $parts = explode(':', $line);
                return trim($parts[1]);
            }
        }
    } else {
        // For Linux/MacOS
        exec('ifconfig', $output);
        $wifiFound = false;
        foreach ($output as $line) {
            if (strpos($line, 'wlan') !== false || strpos($line, 'Wi-Fi') !== false) {
                $wifiFound = true;
            } elseif ($wifiFound && strpos($line, 'inet ') !== false) {
                $parts = preg_split('/\s+/', $line);
                return $parts[1];
            }
        }
    }
    return 'No IPv4 address detected';
}

// Get and return the Wi-Fi IPv4 address
echo json_encode([
    'ip_address' => getWiFiIPv4Address()
]);
?>
