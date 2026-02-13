<?php
namespace EventorIntegration;

/**
 * Proxies Eventor organisation logotype images and caches them locally
 * to work around CSP same-origin restrictions on the Eventor server.
 */
class ImageProxy {
    const CACHE_TTL_DAYS = 7;
    const ALLOWED_TYPES = ['LargeIcon', 'smallIcon'];

    /**
     * Get the proxy URL for an organisation logotype.
     *
     * @param int|string $organisation_id Organisation ID (positive integer).
     * @param string     $type            'LargeIcon' or 'smallIcon'.
     * @return string Proxy URL to use in img src.
     */
    public static function get_proxy_url($organisation_id, $type = 'LargeIcon') {
        $org_id = absint($organisation_id);
        if ($org_id < 1) {
            return '';
        }
        $type = self::sanitize_type($type);
        return add_query_arg(
            [
                'action' => 'eventor_image_proxy',
                'org_id' => $org_id,
                'type'   => $type,
            ],
            admin_url('admin-ajax.php')
        );
    }

    /**
     * Handle the proxy request: serve from cache or fetch from Eventor and cache.
     * Call from wp_ajax_eventor_image_proxy and wp_ajax_nopriv_eventor_image_proxy.
     */
    public static function serve_image() {
        $org_id = isset($_GET['org_id']) ? absint($_GET['org_id']) : 0;
        $type = isset($_GET['type']) ? self::sanitize_type(sanitize_text_field(wp_unslash($_GET['type']))) : 'LargeIcon';

        if ($org_id < 1) {
            status_header(400);
            exit;
        }

        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error'])) {
            status_header(500);
            exit;
        }

        $cache_dir = $upload_dir['basedir'] . '/eventor-logos';
        $cache_base = $cache_dir . '/' . $org_id . '_' . $type;

        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
            if (is_dir($cache_dir)) {
                $index = $cache_dir . '/index.php';
                if (!file_exists($index)) {
                    file_put_contents($index, "<?php\n// Silence is golden.\n");
                }
            }
        }

        $cache_max_age = self::CACHE_TTL_DAYS * DAY_IN_SECONDS;
        $cache_file = null;
        $candidates = glob($cache_base . '.*');
        if (!empty($candidates)) {
            $best = $candidates[0];
            foreach ($candidates as $path) {
                if (filemtime($path) > filemtime($best)) {
                    $best = $path;
                }
            }
            if (is_file($best) && (time() - filemtime($best)) < $cache_max_age) {
                $cache_file = $best;
            }
        }
        $serve_from_cache = $cache_file !== null;

        if (!$serve_from_cache) {
            $origin_base = defined('EVENTOR_LOGO_ORIGIN_BASE') ? EVENTOR_LOGO_ORIGIN_BASE : 'https://eventornorge.blob.core.windows.net/organisationlogos';
            $remote_url = $origin_base . '/' . $org_id . '/' . $type . '.png';
            $response = wp_remote_get($remote_url, [
                'timeout' => 15,
                'redirection' => 2,
            ]);

            if (is_wp_error($response)) {
                $fallback = glob($cache_base . '.*');
                if (!empty($fallback) && is_file($fallback[0])) {
                    $cache_file = $fallback[0];
                    $serve_from_cache = true;
                } else {
                    status_header(502);
                    exit;
                }
            } else {
                $code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                $content_type = wp_remote_retrieve_header($response, 'content-type');

                if ($code === 200 && !empty($body)) {
                    $ext = 'png';
                    if (preg_match('#image/(jpe?g|gif|webp|png)#i', $content_type, $m)) {
                        $ext = strtolower($m[1]);
                        if ($ext === 'jpeg') {
                            $ext = 'jpg';
                        }
                    }
                    $cache_file_with_ext = $cache_dir . '/' . $org_id . '_' . $type . '.' . $ext;
                    if (file_put_contents($cache_file_with_ext, $body) !== false) {
                        foreach (glob($cache_base . '.*') as $old) {
                            if (str_replace('\\', '/', $old) !== $cache_file_with_ext) {
                                @unlink($old);
                            }
                        }
                        $cache_file = $cache_file_with_ext;
                        $serve_from_cache = true;
                    }
                } else {
                    $fallback = glob($cache_base . '.*');
                    if (!empty($fallback) && is_file($fallback[0])) {
                        $cache_file = $fallback[0];
                        $serve_from_cache = true;
                    } else {
                        status_header($code >= 400 ? $code : 502);
                        exit;
                    }
                }
            }
        }

        if ($serve_from_cache && $cache_file !== null && is_file($cache_file)) {
            $mime = wp_check_filetype($cache_file, null)['type'];
            if (!$mime) {
                $mime = 'image/png';
            }
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=' . $cache_max_age);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_max_age) . ' GMT');
            readfile($cache_file);
            exit;
        }

        status_header(404);
        exit;
    }

    /**
     * @param string $type
     * @return string
     */
    private static function sanitize_type($type) {
        $type = trim($type);
        return in_array($type, self::ALLOWED_TYPES, true) ? $type : 'LargeIcon';
    }
}
