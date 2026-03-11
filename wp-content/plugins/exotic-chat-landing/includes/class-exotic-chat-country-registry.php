<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Exotic_Chat_Country_Registry
{
    /**
     * @return array<string, int>
     */
    public static function default_department_map(): array
    {
        return [
            'KE' => 1,
            'GH' => 2,
            'ZA' => 3,
            'NG' => 4,
            'TZ' => 5,
            'CI' => 6,
            'SN' => 7,
            'ET' => 8,
            'ZM' => 9,
            'BJ' => 10,
            'TG' => 11,
            'SS' => 12,
            'UG' => 13,
            'RW' => 14,
            'CD' => 15,
            'AO' => 16,
            'MZ' => 17,
            'BW' => 18,
            'NA' => 19,
            'MW' => 20,
            'ZW' => 21,
            'EG' => 22,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolve_from_host(string $host, array $country_map, int $default_department_id): array
    {
        $country_code = '';
        $department_id = max(0, $default_department_id);

        $entry = self::find_country_map_entry($host, $country_map);
        if ($entry !== null) {
            if (isset($entry['country_code']) && is_string($entry['country_code'])) {
                $country_code = strtoupper(substr(trim($entry['country_code']), 0, 3));
            }
            if (isset($entry['department_id'])) {
                $department_id = max(0, (int) $entry['department_id']);
            }
        }

        if ($country_code === '') {
            $country_code = self::detect_country_code_from_host($host);
        }

        if ($department_id < 1 && $country_code !== '') {
            $department_id = self::default_department_for_country($country_code);
        }

        return [
            'country_code' => $country_code,
            'department_id' => max(0, $department_id),
        ];
    }

    public static function detect_country_code_from_host(string $host): string
    {
        $tokenized_host = self::tokenize($host);
        if ($tokenized_host === '') {
            return '';
        }

        foreach (self::country_aliases() as $country_code => $aliases) {
            foreach ($aliases as $alias) {
                if ($alias !== '' && strpos($tokenized_host, self::tokenize($alias)) !== false) {
                    return $country_code;
                }
            }
        }

        return '';
    }

    public static function default_department_for_country(string $country_code): int
    {
        $country_code = strtoupper(trim($country_code));
        $departments = self::default_department_map();
        return isset($departments[$country_code]) ? (int) $departments[$country_code] : 0;
    }

    /**
     * @param array<string, mixed> $country_map
     * @return array<string, mixed>|null
     */
    private static function find_country_map_entry(string $host, array $country_map): ?array
    {
        if ($host === '' || empty($country_map)) {
            return null;
        }

        $host = self::normalize_host($host);
        if ($host === '') {
            return null;
        }

        $candidates = [
            $host,
            preg_replace('/^www\./', '', $host),
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }

            if (isset($country_map[$candidate]) && is_array($country_map[$candidate])) {
                return $country_map[$candidate];
            }
        }

        foreach ($country_map as $pattern => $entry) {
            if (!is_string($pattern) || !is_array($entry)) {
                continue;
            }

            $pattern = self::normalize_host($pattern);
            if (strpos($pattern, '*.') !== 0) {
                continue;
            }

            $suffix = substr($pattern, 1);
            if (!is_string($suffix) || $suffix === '' || $suffix === '.') {
                continue;
            }

            if (substr($host, -strlen($suffix)) === $suffix || $host === ltrim($suffix, '.')) {
                return $entry;
            }
        }

        return null;
    }

    private static function normalize_host(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('/:[0-9]+$/', '', $host);
        return is_string($host) ? $host : '';
    }

    private static function tokenize(string $value): string
    {
        $value = strtolower(remove_accents($value));
        $value = preg_replace('/[^a-z0-9]+/', '', $value);
        return is_string($value) ? $value : '';
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function country_aliases(): array
    {
        return [
            'KE' => ['kenya'],
            'GH' => ['ghana'],
            'ZA' => ['southafrica', 'south-africa', 'rsa'],
            'NG' => ['nigeria'],
            'TZ' => ['tanzania'],
            'CI' => ['cotedivoire', 'cote-divoire', 'ivorycoast', 'ivory-coast'],
            'SN' => ['senegal'],
            'ET' => ['ethiopia'],
            'ZM' => ['zambia'],
            'BJ' => ['benin'],
            'TG' => ['togo'],
            'SS' => ['southsudan', 'south-sudan'],
            'UG' => ['uganda'],
            'RW' => ['rwanda'],
            'CD' => ['drc', 'congodrc', 'drcongo', 'democraticrepublicofcongo'],
            'AO' => ['angola'],
            'MZ' => ['mozambique'],
            'BW' => ['botswana'],
            'NA' => ['namibia'],
            'MW' => ['malawi'],
            'ZW' => ['zimbabwe'],
            'EG' => ['egypt'],
        ];
    }
}
