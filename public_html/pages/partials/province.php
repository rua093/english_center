<?php

if (!function_exists('loadProvinceApiData')) {
    function loadProvinceApiData(string $url): ?array
    {
        $responseBody = null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);

            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_TIMEOUT => 6,
                    CURLOPT_HTTPHEADER => ['Accept: application/json'],
                ]);

                $responseBody = curl_exec($ch);
                $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($responseBody === false || $statusCode < 200 || $statusCode >= 300) {
                    $responseBody = null;
                }
            }
        }

        if ($responseBody === null) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 6,
                    'header' => "Accept: application/json\r\n",
                ],
            ]);

            $responseBody = @file_get_contents($url, false, $context);
        }

        if (!is_string($responseBody) || $responseBody === '') {
            return null;
        }

        $decoded = json_decode($responseBody, true);

        return is_array($decoded) ? $decoded : null;
    }
}

if (!function_exists('formatProvinceDivisionType')) {
    function formatProvinceDivisionType(string $divisionType): string
    {
        return match ($divisionType) {
            'phường' => 'Phường',
            'xã' => 'Xã',
            'đặc khu' => 'Đặc khu',
            default => ucfirst($divisionType),
        };
    }
}

if (!function_exists('normalizeProvinceApiPayload')) {
    function normalizeProvinceApiPayload(array $provinceList): array
    {
        $normalizedProvinces = [];

        foreach ($provinceList as $province) {
            if (!is_array($province) || !isset($province['code'], $province['name'])) {
                continue;
            }

            $wardGroups = [];

            foreach (($province['wards'] ?? []) as $ward) {
                if (!is_array($ward) || !isset($ward['code'], $ward['name'])) {
                    continue;
                }

                $divisionType = (string) ($ward['division_type'] ?? '');
                if ($divisionType === '') {
                    continue;
                }

                if (!isset($wardGroups[$divisionType])) {
                    $wardGroups[$divisionType] = [
                        'code' => $divisionType,
                        'name' => formatProvinceDivisionType($divisionType),
                        'wards' => [],
                    ];
                }

                $wardGroups[$divisionType]['wards'][] = [
                    'name' => (string) $ward['name'],
                    'code' => (int) $ward['code'],
                    'division_type' => $divisionType,
                    'codename' => (string) ($ward['codename'] ?? ''),
                    'province_code' => (int) ($ward['province_code'] ?? $province['code']),
                ];
            }

            $normalizedProvinces[] = [
                'name' => (string) $province['name'],
                'code' => (int) $province['code'],
                'division_type' => (string) ($province['division_type'] ?? ''),
                'codename' => (string) ($province['codename'] ?? ''),
                'phone_code' => (int) ($province['phone_code'] ?? 0),
                'districts' => array_values($wardGroups),
            ];
        }

        return ['provinces' => $normalizedProvinces];
    }
}

$provinceApiUrl = 'https://provinces.open-api.vn/api/v2/?depth=2';
$provinceApiData = loadProvinceApiData($provinceApiUrl);

if (is_array($provinceApiData)) {
    return normalizeProvinceApiPayload($provinceApiData);
}

$provinceFile = __DIR__ . '/province.json';

if (!is_file($provinceFile)) {
    return ['provinces' => []];
}

$provinceData = json_decode((string) file_get_contents($provinceFile), true);

return is_array($provinceData) ? $provinceData : ['provinces' => []];
