<?php

if (!function_exists('getProvinceDataByCode')) {
    function getProvinceDataByCode($code, $depth = 2) {
        static $provinceCache = [];
        $code = intval($code);
        if ($code <= 0) {
            return null;
        }

        if (!array_key_exists($code, $provinceCache)) {
            $apiUrl = "https://provinces.open-api.vn/api/p/{$code}?depth={$depth}";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3
                ]
            ]);

            $response = @file_get_contents($apiUrl, false, $context);

            if ($response === false && function_exists('curl_init')) {
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $response = curl_exec($ch);
                curl_close($ch);
            }

            $provinceCache[$code] = $response ? json_decode($response, true) : null;
        }

        return $provinceCache[$code];
    }
}

if (!function_exists('resolveLocationNamesByCode')) {
    function resolveLocationNamesByCode($provinceCode, $districtCode = null) {
        $provinceName = '';
        $districtName = '';

        $provinceData = getProvinceDataByCode($provinceCode);
        if (!empty($provinceData['name'])) {
            $provinceName = $provinceData['name'];
        }

        if (!empty($districtCode) && !empty($provinceData['districts'])) {
            foreach ($provinceData['districts'] as $district) {
                if (intval($district['code']) === intval($districtCode)) {
                    $districtName = $district['name'];
                    break;
                }
            }
        }

        return [$provinceName, $districtName];
    }
}

if (!function_exists('normalizeVietnameseString')) {
    function normalizeVietnameseString($str) {
        $str = mb_strtolower(trim($str), 'UTF-8');
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'd' => 'đ'
        ];

        foreach ($unicode as $nonUnicode => $pattern) {
            $str = preg_replace("/($pattern)/iu", $nonUnicode, $str);
        }

        $str = preg_replace('/[^a-z0-9\s]/', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }
}

if (!function_exists('addressMatchesLocation')) {
    function addressMatchesLocation($address, $provinceName = '', $districtName = '') {
        if (empty($provinceName)) {
            return true;
        }

        $normalizedAddress = normalizeVietnameseString($address);
        $normalizedProvince = normalizeVietnameseString($provinceName);

        if (strpos($normalizedAddress, $normalizedProvince) === false) {
            return false;
        }

        if (!empty($districtName)) {
            $normalizedDistrict = normalizeVietnameseString($districtName);
            if (strpos($normalizedAddress, $normalizedDistrict) === false) {
                return false;
            }
        }

        return true;
    }
}


