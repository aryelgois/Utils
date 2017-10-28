<?php
/**
 * This Software is part of aryelgois\Utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\Utils;

use Composer\Script\Event;

/**
 * Configure public vendors easily
 *
 * Useful when a package dependency has front-end files which should be under
 * base_dir/public instead of base_dir/vendor
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 */
class PublicVendor
{
    /**
     * Callback for Composer's post-install-cmd
     *
     * It reads a configuration file at base_dir/config/public_vendor.json
     * with the following structure:
     *     {
     *         "vendors": {
     *             vendor_alias: path to vendor directory under base_dir
     *             ...
     *         },
     *         "map": [
     *             {
     *                 "source": path to directory under base_dir/vendor
     *                 "vendor": vendor_alias to be used
     *                 "destiny": path to symbolic link under vendor
     *             }
     *             ...
     *         ]
     *     }
     *
     * Then, it creates symbolic links from source to destiny
     *
     * Additionally, it writes a .gitignore with all vendors listed
     *
     * @param Event $event Composer Script Event
     *
     * @throws RuntimeException If configuration file is not found
     * @throws RuntimeException If configuration file could not be loaded
     * @throws RuntimeException If a map uses an undefined vendor
     */
    public static function postInstall(Event $event)
    {
        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');
        $base_dir = dirname($vendor_dir);
        $config_file = $base_dir . '/config/public_vendor.json';
        $gitignore_file = $base_dir . '/.gitignore';

        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            if ($config === null) {
                throw new \RuntimeException('Error loading configuration file');
            }

            /*
             * Create symbolic links
             */
            foreach ($config['map'] as $map) {
                if (array_key_exists($map['vendor'], $config['vendors'])) {
                    $destiny = $base_dir . '/'
                             . $config['vendors'][$map['vendor']] . '/'
                             . trim($map['destiny'], '/');
                    $source = $vendor_dir . '/' . trim($map['source'], '/');
                    if (!file_exists(dirname($destiny))) {
                        mkdir(dirname($destiny), 0755, true);
                    }
                    if (!file_exists($destiny)) {
                        symlink($source, $destiny);
                    }
                } else {
                    throw new \RuntimeException('Undefined vendor');
                }
            }

            /*
             * Update gitignore
             */
            $gitignore = (file_exists($gitignore_file))
                ? file($gitignore_file, FILE_IGNORE_NEW_LINES)
                : ['/vendor/'];
            foreach ($config['vendors'] as $vendor_path) {
                $ignore_path = '/' . trim($vendor_path, '/') . '/';
                if (!in_array($ignore_path, $gitignore)) {
                    $gitignore[] = $ignore_path;
                }
            }
            $gitignore_handle = fopen($gitignore_file, 'w');
            fwrite($gitignore_handle, implode("\n", $gitignore) . "\n");
            fclose($gitignore_handle);
        } else {
            throw new \RuntimeException('Configuration file not found');
        }
    }
}
