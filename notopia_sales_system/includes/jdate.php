<?php
/**
 * تبدیل تاریخ شمسی ↔ میلادی
 * ذخیره در دیتابیس همیشه میلادی (Y-m-d) است.
 * نمایش و ورود کاربر شمسی (1405/07/27) است.
 */

/**
 * میلادی به شمسی
 * @return array [jy, jm, jd]
 */
function gregorian_to_jalali($gy, $gm, $gd)
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
          + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * intdiv($days, 12053));
    $days %= 12053;
    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv($days - 186, 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return [$jy, $jm, $jd];
}

/**
 * شمسی به میلادی
 * @return array [gy, gm, gd]
 */
function jalali_to_gregorian($jy, $jm, $jd)
{
    $jy -= 979;
    $jm -= 1;
    $jd -= 1;
    $j_day_no = (365 * $jy) + intdiv($jy, 33) * 8 + intdiv(($jy % 33) + 3, 4);
    for ($i = 0; $i < $jm; ++$i) {
        $j_day_no += ($i < 6) ? 31 : 30;
    }
    $j_day_no += $jd;
    $g_day_no = $j_day_no + 79;
    $gy = 1600 + (400 * intdiv($g_day_no, 146097));
    $g_day_no %= 146097;
    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * intdiv($g_day_no, 36524);
        $g_day_no %= 36524;
        if ($g_day_no >= 365) {
            $g_day_no++;
        } else {
            $leap = false;
        }
    }
    $gy += 4 * intdiv($g_day_no, 1461);
    $g_day_no %= 1461;
    if ($g_day_no >= 366) {
        $leap = false;
        $g_day_no--;
        $gy += intdiv($g_day_no, 365);
        $g_day_no %= 365;
    }
    $gd = $g_day_no + 1;
    $sal_a = [0, 31, ($leap ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    for ($gm = 1; $gm <= 12 && $gd > $sal_a[$gm]; $gm++) {
        $gd -= $sal_a[$gm];
    }
    return [$gy, $gm, $gd];
}

/** میلادی Y-m-d → شمسی 1405/07/27 */
function to_jalali($gregorian)
{
    if ($gregorian === null || $gregorian === '') {
        return '';
    }
    $gregorian = trim((string)$gregorian);
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $gregorian, $m)) {
        list($jy, $jm, $jd) = gregorian_to_jalali((int)$m[1], (int)$m[2], (int)$m[3]);
        return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    }
    // اگر از قبل شمسی است
    if (preg_match('/^(\d{3,4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $gregorian, $m)) {
        return sprintf('%04d/%02d/%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
    }
    return $gregorian;
}

/**
 * ورودی کاربر (شمسی یا میلادی) → میلادی Y-m-d برای دیتابیس
 * قبول می‌کند: 1405/07/27 ، 1405-07-27 ، 2026-10-19
 */
function to_gregorian($input)
{
    if ($input === null || $input === '') {
        return null;
    }
    $input = trim((string)$input);
    $input = str_replace(['.', ' ', '‌'], ['/', '', ''], $input);

    // میلادی از قبل
    if (preg_match('/^(19|20)\d{2}-\d{1,2}-\d{1,2}$/', $input)) {
        $dt = date_create($input);
        return $dt ? $dt->format('Y-m-d') : null;
    }

    // شمسی
    if (preg_match('/^(\d{3,4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $input, $m)) {
        $jy = (int)$m[1];
        $jm = (int)$m[2];
        $jd = (int)$m[3];
        // اگر سال شبیه میلادی بود اشتباهی شمسی فرض نکن
        if ($jy >= 1900 && $jy <= 2100) {
            $dt = date_create(sprintf('%04d-%02d-%02d', $jy, $jm, $jd));
            return $dt ? $dt->format('Y-m-d') : null;
        }
        if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }
        list($gy, $gm, $gd) = jalali_to_gregorian($jy, $jm, $jd);
        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }

    return null;
}

/** امروز به شمسی */
function today_jalali()
{
    return to_jalali(date('Y-m-d'));
}
