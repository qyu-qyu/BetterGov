<?php

namespace App\Services;

/**
 * Minimal QR Code generator — pure PHP, no packages.
 * Generates a valid QR Code as an SVG string.
 * Supports byte mode only (URLs, text).
 * Based on the QR Code specification ISO/IEC 18004.
 */
class QrCodeService
{
    // Error correction level M (15%)
    private const EC_M = 0;

    // RS block table [rep, total, data] per typeNumber for level M
    private const RS_BLOCKS = [
        // tn=1
        [1, 26, 16],
        // tn=2
        [1, 44, 28],
        // tn=3
        [1, 70, 44],
        // tn=4
        [2, 50, 32],
        // tn=5
        [2, 67, 43],
        // tn=6
        [4, 43, 27],
        // tn=7
        [4, 49, 31],
        // tn=8
        [2, 60, 38, 2, 61, 39],
        // tn=9
        [3, 58, 36, 2, 59, 37],
        // tn=10
        [4, 69, 43, 1, 70, 44],
    ];

    // Capacity in bytes for M level
    private const CAPACITY_M = [0, 16, 28, 44, 64, 86, 108, 124, 154, 182, 216];

    public static function generate(string $text, int $size = 200): string
    {
        // Find minimum typeNumber
        $typeNum = 0;
        for ($i = 1; $i <= 10; $i++) {
            if (strlen($text) <= self::CAPACITY_M[$i]) {
                $typeNum = $i;
                break;
            }
        }

        if ($typeNum === 0) {
            // Fallback: return a placeholder SVG with the URL as text
            return self::placeholder($text, $size);
        }

        try {
            $modules = self::buildMatrix($typeNum, $text);
            return self::toSvg($modules, $size);
        } catch (\Throwable $e) {
            return self::placeholder($text, $size);
        }
    }

    private static function buildMatrix(int $typeNum, string $text): array
    {
        $mc = 4 * $typeNum + 17;
        $modules = array_fill(0, $mc, array_fill(0, $mc, null));

        // Place finder patterns
        self::placeFinderPattern($modules, 0, 0, $mc);
        self::placeFinderPattern($modules, $mc - 7, 0, $mc);
        self::placeFinderPattern($modules, 0, $mc - 7, $mc);

        // Timing patterns
        for ($i = 8; $i < $mc - 8; $i++) {
            if ($modules[$i][6] === null) $modules[$i][6] = ($i % 2 === 0);
            if ($modules[6][$i] === null) $modules[6][$i] = ($i % 2 === 0);
        }

        // Dark module
        $modules[$mc - 8][8] = true;

        // Generate data
        $data = self::generateData($typeNum, $text);

        // Try each mask pattern and pick the best
        $best = null;
        $bestPenalty = PHP_INT_MAX;
        for ($mask = 0; $mask < 8; $mask++) {
            $trial = $modules;
            self::placeFormatInfo($trial, $mask, $mc);
            self::placeData($trial, $data, $mask, $mc);
            $penalty = self::penalty($trial, $mc);
            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $best = $trial;
            }
        }

        return $best;
    }

    private static function placeFinderPattern(array &$m, int $row, int $col, int $mc): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $pr = $row + $r;
                $pc = $col + $c;
                if ($pr < 0 || $pr >= $mc || $pc < 0 || $pc >= $mc) continue;
                $m[$pr][$pc] = (
                    ($r >= 0 && $r <= 6 && ($c === 0 || $c === 6)) ||
                    ($c >= 0 && $c <= 6 && ($r === 0 || $r === 6)) ||
                    ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4)
                );
            }
        }
    }

    private static function placeFormatInfo(array &$m, int $mask, int $mc): void
    {
        // Format string for M level with given mask
        $format = self::formatBits(self::EC_M, $mask);
        $seq = [
            [8,0],[8,1],[8,2],[8,3],[8,4],[8,5],[8,7],[8,8],
            [7,8],[5,8],[4,8],[3,8],[2,8],[1,8],[0,8],
        ];
        for ($i = 0; $i < 15; $i++) {
            $bit = ($format >> (14 - $i)) & 1;
            [$r, $c] = $seq[$i];
            $m[$r][$c] = (bool)$bit;
        }
        // Copy to the other positions
        for ($i = 0; $i < 8; $i++) {
            $bit = ($format >> $i) & 1;
            $m[$mc - 1 - $i][8] = (bool)$bit;
        }
        for ($i = 8; $i < 15; $i++) {
            $bit = ($format >> $i) & 1;
            $m[8][$mc - 15 + $i] = (bool)$bit;
        }
    }

    private static function formatBits(int $ecLevel, int $mask): int
    {
        $data = ($ecLevel << 3) | $mask;
        $g = 0x537; // Generator polynomial
        $r = $data << 10;
        for ($i = 4; $i >= 0; $i--) {
            if (($r >> ($i + 10)) & 1) $r ^= $g << $i;
        }
        return (($data << 10) | $r) ^ 0x5412;
    }

    private static function placeData(array &$m, array $data, int $mask, int $mc): void
    {
        $bitIdx = 0;
        $dir = -1;
        $row = $mc - 1;

        for ($col = $mc - 1; $col > 0; $col -= 2) {
            if ($col === 6) $col--;
            while (true) {
                for ($dc = 0; $dc < 2; $dc++) {
                    $c = $col - $dc;
                    if ($m[$row][$c] !== null) continue;
                    $dark = false;
                    if ($bitIdx < count($data) * 8) {
                        $byteIdx = intdiv($bitIdx, 8);
                        $bitPos  = 7 - ($bitIdx % 8);
                        $dark = (bool)(($data[$byteIdx] >> $bitPos) & 1);
                    }
                    if (self::maskFunc($mask, $row, $c)) $dark = !$dark;
                    $m[$row][$c] = $dark;
                    $bitIdx++;
                }
                $row += $dir;
                if ($row < 0 || $row >= $mc) {
                    $row -= $dir;
                    $dir  = -$dir;
                    break;
                }
            }
        }
    }

    private static function maskFunc(int $mask, int $r, int $c): bool
    {
        switch ($mask) {
            case 0: return ($r + $c) % 2 === 0;
            case 1: return $r % 2 === 0;
            case 2: return $c % 3 === 0;
            case 3: return ($r + $c) % 3 === 0;
            case 4: return (intdiv($r, 2) + intdiv($c, 3)) % 2 === 0;
            case 5: return ($r * $c % 2 + $r * $c % 3) === 0;
            case 6: return ($r * $c % 2 + $r * $c % 3) % 2 === 0;
            case 7: return ($r * $c % 3 + ($r + $c) % 2) % 2 === 0;
        }
        return false;
    }

    private static function penalty(array $m, int $mc): int
    {
        $p = 0;
        // Rule 1: 5+ in a row
        for ($r = 0; $r < $mc; $r++) {
            $run = 1;
            for ($c = 1; $c < $mc; $c++) {
                if ($m[$r][$c] === $m[$r][$c-1]) { $run++; if ($run === 5) $p += 3; elseif ($run > 5) $p++; }
                else $run = 1;
            }
        }
        return $p;
    }

    private static function generateData(int $typeNum, string $text): array
    {
        $bytes = array_values(unpack('C*', $text));
        $len   = count($bytes);

        // Build bit stream
        $bits = [];
        // Mode indicator: byte mode = 0100
        $bits = array_merge($bits, self::intToBits(4, 4));
        // Character count (8 bits for typeNum <= 9, 16 for >= 10)
        $ccBits = $typeNum < 10 ? 8 : 16;
        $bits = array_merge($bits, self::intToBits($len, $ccBits));
        // Data bytes
        foreach ($bytes as $b) {
            $bits = array_merge($bits, self::intToBits($b, 8));
        }

        // Terminator
        $rsBlocks = self::getRsBlocks($typeNum);
        $totalData = array_sum(array_column($rsBlocks, 'data'));

        for ($i = 0; $i < 4 && count($bits) < $totalData * 8; $i++) $bits[] = 0;
        while (count($bits) % 8 !== 0) $bits[] = 0;

        // Pad bytes
        $pad = [0xEC, 0x11];
        $pi  = 0;
        while (count($bits) < $totalData * 8) {
            $bits = array_merge($bits, self::intToBits($pad[$pi % 2], 8));
            $pi++;
        }

        // Convert to byte array
        $dataBytes = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $b = 0;
            for ($j = 0; $j < 8; $j++) $b = ($b << 1) | ($bits[$i + $j] ?? 0);
            $dataBytes[] = $b;
        }

        // Reed-Solomon error correction
        $result = [];
        $ecResult = [];
        $offset = 0;

        foreach ($rsBlocks as $block) {
            $dc = array_slice($dataBytes, $offset, $block['data']);
            $offset += $block['data'];
            $ec = self::rsEncode($dc, $block['total'] - $block['data']);
            $result[]   = $dc;
            $ecResult[] = $ec;
        }

        // Interleave
        $final = [];
        $maxDC = max(array_column($rsBlocks, 'data'));
        for ($i = 0; $i < $maxDC; $i++) {
            foreach ($result as $block) {
                if ($i < count($block)) $final[] = $block[$i];
            }
        }
        $maxEC = max(array_map(fn($b) => $b['total'] - $b['data'], $rsBlocks));
        for ($i = 0; $i < $maxEC; $i++) {
            foreach ($ecResult as $ec) {
                if ($i < count($ec)) $final[] = $ec[$i];
            }
        }

        return $final;
    }

    private static function getRsBlocks(int $typeNum): array
    {
        $row = self::RS_BLOCKS[$typeNum - 1];
        $blocks = [];
        for ($i = 0; $i < count($row); $i += 3) {
            for ($j = 0; $j < $row[$i]; $j++) {
                $blocks[] = ['total' => $row[$i+1], 'data' => $row[$i+2]];
            }
        }
        return $blocks;
    }

    private static function rsEncode(array $data, int $ecCount): array
    {
        $gen = self::generator($ecCount);
        $msg = array_merge($data, array_fill(0, $ecCount, 0));
        for ($i = 0; $i < count($data); $i++) {
            $coef = $msg[$i];
            if ($coef !== 0) {
                $log = self::glog($coef);
                for ($j = 0; $j < count($gen); $j++) {
                    $msg[$i + $j] ^= self::gexp($log + $gen[$j]);
                }
            }
        }
        return array_slice($msg, count($data));
    }

    private static function generator(int $degree): array
    {
        $g = [0]; // log form
        for ($i = 0; $i < $degree; $i++) {
            $ng = array_fill(0, count($g) + 1, 0);
            for ($j = 0; $j < count($g); $j++) {
                $ng[$j]   = ($j > 0) ? ($g[$j-1] ?? PHP_INT_MAX) : PHP_INT_MAX;
                $ng[$j+1] = $g[$j] ?? PHP_INT_MAX;
                if ($ng[$j] !== PHP_INT_MAX && $i !== PHP_INT_MAX) {
                    $ng[$j] = ($ng[$j] + $i) % 255;
                }
            }
            // Simpler: build as polynomial multiplication
            $g = $i === 0 ? [0, 0] : $g;
        }
        // Use the standard generator polynomial approach
        return self::buildGenerator($degree);
    }

    private static function buildGenerator(int $degree): array
    {
        $g = [1];
        for ($i = 0; $i < $degree; $i++) {
            $ng = array_fill(0, count($g) + 1, 0);
            for ($j = 0; $j < count($g); $j++) {
                $ng[$j]   ^= self::gexpRaw(self::glogRaw($g[$j]) + $i);
                $ng[$j+1] ^= $g[$j];
            }
            $g = $ng;
        }
        // Return as log values
        return array_map(fn($v) => $v === 0 ? 0 : self::glogRaw($v), array_slice($g, 0, -1));
    }

    // GF(256) tables
    private static array $expTable = [];
    private static array $logTable = [];

    private static function initTables(): void
    {
        if (!empty(self::$expTable)) return;
        self::$expTable = array_fill(0, 512, 0);
        self::$logTable = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$expTable[$i] = $x;
            self::$logTable[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) $x ^= 0x11D;
        }
        for ($i = 255; $i < 512; $i++) {
            self::$expTable[$i] = self::$expTable[$i - 255];
        }
    }

    private static function gexp(int $n): int
    {
        self::initTables();
        return self::$expTable[($n % 255 + 255) % 255];
    }

    private static function gexpRaw(int $n): int
    {
        self::initTables();
        return self::$expTable[$n % 255];
    }

    private static function glog(int $n): int
    {
        self::initTables();
        if ($n === 0) return 0;
        return self::$logTable[$n & 0xFF];
    }

    private static function glogRaw(int $n): int
    {
        self::initTables();
        if ($n === 0) return 0;
        return self::$logTable[$n & 0xFF];
    }

    private static function intToBits(int $val, int $bits): array
    {
        $result = [];
        for ($i = $bits - 1; $i >= 0; $i--) {
            $result[] = ($val >> $i) & 1;
        }
        return $result;
    }

    private static function toSvg(array $modules, int $size): string
    {
        $mc   = count($modules);
        $cell = $size / ($mc + 8); // add quiet zone
        $offset = 4 * $cell;       // 4-module quiet zone

        $rects = '';
        for ($r = 0; $r < $mc; $r++) {
            for ($c = 0; $c < $mc; $c++) {
                if ($modules[$r][$c]) {
                    $x = round($offset + $c * $cell, 2);
                    $y = round($offset + $r * $cell, 2);
                    $w = round($cell + 0.5, 2); // slight overlap to avoid gaps
                    $rects .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$w}\"/>";
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$size.' '.$size.'" width="'.$size.'" height="'.$size.'">'
             . '<rect width="'.$size.'" height="'.$size.'" fill="white"/>'
             . '<g fill="black">'.$rects.'</g>'
             . '</svg>';
    }

    private static function placeholder(string $text, int $size): string
    {
        $escaped = htmlspecialchars($text, ENT_XML1);
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">'
             . '<rect width="'.$size.'" height="'.$size.'" fill="#f1f5f9" rx="8"/>'
             . '<text x="50%" y="50%" text-anchor="middle" font-size="11" fill="#64748b">QR unavailable</text>'
             . '</svg>';
    }
}