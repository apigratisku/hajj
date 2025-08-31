<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Time Helper - Konversi format waktu
 */

/**
 * Konversi waktu dari format 24 jam ke format 12 jam AM/PM
 * @param string $time24 Waktu dalam format 24 jam (HH:MM atau HH:MM:SS)
 * @return string Waktu dalam format 12 jam AM/PM
 */
function format_12_hour($time24) {
    if (empty($time24)) {
        return '';
    }
    
    // Parse waktu
    $time = date('g:i A', strtotime($time24));
    return $time;
}

/**
 * Konversi waktu dari format 24 jam ke format 12 jam AM/PM dengan detik
 * @param string $time24 Waktu dalam format 24 jam (HH:MM:SS)
 * @return string Waktu dalam format 12 jam AM/PM dengan detik
 */
function format_12_hour_with_seconds($time24) {
    if (empty($time24)) {
        return '';
    }
    
    // Parse waktu dengan detik
    $time = date('g:i:s A', strtotime($time24));
    return $time;
}

/**
 * Konversi waktu dari format 24 jam ke format 12 jam AM/PM untuk Indonesia
 * @param string $time24 Waktu dalam format 24 jam (HH:MM atau HH:MM:SS)
 * @return string Waktu dalam format 12 jam AM/PM dalam bahasa Indonesia
 */
function format_12_hour_indonesia($time24) {
    if (empty($time24)) {
        return '';
    }
    
    // Parse waktu
    $hour = (int)date('G', strtotime($time24));
    $minute = date('i', strtotime($time24));
    $period = ($hour >= 12) ? 'PM' : 'AM';
    
    // Konversi ke format 12 jam
    $hour12 = ($hour == 0) ? 12 : (($hour > 12) ? $hour - 12 : $hour);
    
    return $hour12 . ':' . $minute . ' ' . $period;
}

/**
 * Konversi waktu dari format 24 jam ke format 12 jam AM/PM dengan detik untuk Indonesia
 * @param string $time24 Waktu dalam format 24 jam (HH:MM:SS)
 * @return string Waktu dalam format 12 jam AM/PM dengan detik dalam bahasa Indonesia
 */
function format_12_hour_with_seconds_indonesia($time24) {
    if (empty($time24)) {
        return '';
    }
    
    // Parse waktu dengan detik
    $hour = (int)date('G', strtotime($time24));
    $minute = date('i', strtotime($time24));
    $second = date('s', strtotime($time24));
    $period = ($hour >= 12) ? 'PM' : 'AM';
    
    // Konversi ke format 12 jam
    $hour12 = ($hour == 0) ? 12 : (($hour > 12) ? $hour - 12 : $hour);
    
    return $hour12 . ':' . $minute . ':' . $second . ' ' . $period;
}
