<?php

if (!defined('ABSPATH')) {
    exit;
}

class MMWM_SSL_Checker implements MMWM_SSL_Checker_Interface
{
    /**
     * Check SSL certificate status
     *
     * @param string $url The URL to check
     * @return array SSL status information
     */
    public function check_ssl_status($url)
    {
        $parsed_url = parse_url($url);
        if (!$parsed_url || !isset($parsed_url['host'])) {
            return [
                'is_active' => false,
                'error' => 'Invalid URL: Format URL tidak valid atau tidak lengkap',
                'expiry_date' => null,
                'days_until_expiry' => null,
                'issuer' => null,
                'error_code' => 'invalid_url',
                'error_details' => 'URL tidak dapat diproses karena format yang tidak valid. Pastikan URL dimulai dengan http:// atau https://',
                'recommendation' => 'Periksa dan perbaiki format URL'
            ];
        }

        $host = $parsed_url['host'];
        $port = isset($parsed_url['port']) ? $parsed_url['port'] : 443;

        // Skip SSL check for non-HTTPS URLs
        if (isset($parsed_url['scheme']) && $parsed_url['scheme'] !== 'https') {
            return [
                'is_active' => false,
                'error' => 'URL is not HTTPS: Pemeriksaan SSL hanya untuk URL HTTPS',
                'expiry_date' => null,
                'days_until_expiry' => null,
                'issuer' => null,
                'error_code' => 'not_https',
                'error_details' => 'Pemeriksaan sertifikat SSL hanya dapat dilakukan pada URL yang menggunakan protokol HTTPS',
                'recommendation' => 'Gunakan URL dengan protokol HTTPS untuk memeriksa status SSL'
            ];
        }

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $stream = @stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$stream) {
                return [
                    'is_active' => false,
                    'error' => "Connection failed: {$errstr}",
                    'expiry_date' => null,
                    'days_until_expiry' => null,
                    'issuer' => null,
                    'error_code' => 'connection_failed',
                    'error_details' => "Tidak dapat terhubung ke server untuk memeriksa sertifikat SSL. Error: {$errstr}",
                    'recommendation' => 'Periksa apakah server aktif dan dapat diakses. Pastikan tidak ada firewall yang memblokir koneksi'
                ];
            }

            $params = stream_context_get_params($stream);
            $cert = $params['options']['ssl']['peer_certificate'];

            if (!$cert) {
                fclose($stream);
                return [
                    'is_active' => false,
                    'error' => 'No SSL certificate found: Sertifikat SSL tidak ditemukan',
                    'expiry_date' => null,
                    'days_until_expiry' => null,
                    'issuer' => null,
                    'error_code' => 'no_certificate',
                    'error_details' => 'Server tidak mengembalikan sertifikat SSL yang valid saat diminta',
                    'recommendation' => 'Periksa konfigurasi SSL pada server dan pastikan sertifikat terpasang dengan benar'
                ];
            }

            $cert_info = openssl_x509_parse($cert);
            fclose($stream);

            if (!$cert_info) {
                return [
                    'is_active' => false,
                    'error' => 'Failed to parse SSL certificate: Gagal memproses sertifikat SSL',
                    'expiry_date' => null,
                    'days_until_expiry' => null,
                    'issuer' => null,
                    'error_code' => 'parse_failed',
                    'error_details' => 'Sertifikat SSL ditemukan tetapi tidak dapat diproses atau formatnya tidak valid',
                    'recommendation' => 'Periksa apakah sertifikat SSL valid dan sesuai standar. Mungkin perlu diperbarui atau diganti'
                ];
            }

            $expiry_timestamp = $cert_info['validTo_time_t'];
            $current_timestamp = time();
            $days_until_expiry = ceil(($expiry_timestamp - $current_timestamp) / 86400);

            $issuer = isset($cert_info['issuer']['CN']) ? $cert_info['issuer']['CN'] : (isset($cert_info['issuer']['O']) ? $cert_info['issuer']['O'] : 'Unknown');

            // Tentukan status SSL berdasarkan hari yang tersisa
            $is_expired = $days_until_expiry < 0;
            $is_expiring_soon = $days_until_expiry <= 10 && $days_until_expiry > 0;
            $is_expiring_warning = $days_until_expiry <= 30 && $days_until_expiry > 10;
            
            $status_message = null;
            $status_code = 'valid';
            $recommendation = null;
            
            if ($is_expired) {
                $status_message = 'Sertifikat SSL telah kedaluwarsa';
                $status_code = 'expired';
                $recommendation = 'Segera perbarui sertifikat SSL Anda untuk menghindari peringatan keamanan pada browser pengunjung';
            } elseif ($is_expiring_soon) {
                $status_message = 'Sertifikat SSL akan segera kedaluwarsa dalam ' . $days_until_expiry . ' hari';
                $status_code = 'expiring_soon';
                $recommendation = 'Perbarui sertifikat SSL Anda dalam ' . $days_until_expiry . ' hari untuk menghindari gangguan layanan';
            } elseif ($is_expiring_warning) {
                $status_message = 'Sertifikat SSL akan kedaluwarsa dalam ' . $days_until_expiry . ' hari';
                $status_code = 'expiring_warning';
                $recommendation = 'Rencanakan pembaruan sertifikat SSL Anda dalam waktu dekat';
            }
            
            return [
                'is_active' => true,
                'error' => null,
                'expiry_date' => date('Y-m-d H:i:s', $expiry_timestamp),
                'expiry_timestamp' => $expiry_timestamp,
                'days_until_expiry' => $days_until_expiry,
                'issuer' => $issuer,
                'is_expired' => $is_expired,
                'is_expiring_soon' => $is_expiring_soon,
                'is_expiring_warning' => $is_expiring_warning,
                'status_message' => $status_message,
                'status_code' => $status_code,
                'recommendation' => $recommendation
            ];
        } catch (Exception $e) {
            return [
                'is_active' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'expiry_date' => null,
                'days_until_expiry' => null,
                'issuer' => null,
                'error_code' => 'exception',
                'error_details' => 'Terjadi kesalahan saat memeriksa sertifikat SSL: ' . $e->getMessage(),
                'recommendation' => 'Coba lagi nanti atau periksa konfigurasi server Anda'
            ];
        }
    }

    /**
     * Get SSL expiration date
     *
     * @param string $url The URL to check
     * @return array SSL expiration information
     */
    public function get_ssl_expiration($url)
    {
        $ssl_status = $this->check_ssl_status($url);

        return [
            'expiry_date' => $ssl_status['expiry_date'],
            'days_until_expiry' => $ssl_status['days_until_expiry'],
            'is_expired' => isset($ssl_status['is_expired']) ? $ssl_status['is_expired'] : false,
            'is_expiring_soon' => isset($ssl_status['is_expiring_soon']) ? $ssl_status['is_expiring_soon'] : false
        ];
    }

    /**
     * Check if SSL will expire soon
     *
     * @param string $url The URL to check
     * @param int $days Number of days before expiration to warn
     * @return bool True if SSL expires within specified days
     */
    public function is_ssl_expiring_soon($url, $days = 10)
    {
        $ssl_status = $this->check_ssl_status($url);

        if (!$ssl_status['is_active'] || $ssl_status['days_until_expiry'] === null) {
            return false;
        }

        return $ssl_status['days_until_expiry'] <= $days && $ssl_status['days_until_expiry'] > 0;
    }
}
